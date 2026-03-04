import http.server
import socketserver
import json
import os
import shutil
import cgi
import base64
from urllib.parse import urlparse, parse_qs

PORT = 8000
CONFIG_FILE = 'config.json'
ASSETS_DIR = 'assets'
OVERLAYS_DIR = 'overlays'

# Format is 'username:password' -> Base64
# admin:kumqab-noqguT-9qokga
EXPECTED_AUTH = "Basic " + base64.b64encode(b"admin:kumqab-noqguT-9qokga").decode("utf-8")

class RequestHandler(http.server.SimpleHTTPRequestHandler):
    def check_auth(self):
        auth_header = self.headers.get('Authorization')
        if auth_header != EXPECTED_AUTH:
            self.send_response(401)
            self.send_header('WWW-Authenticate', 'Basic realm="Admin Access Required"')
            self.end_headers()
            self.wfile.write(b"Unauthorized")
            return False
        return True

    def do_GET(self):
        print(f"DEBUG: Original path request: {self.path}")
        parsed_path = urlparse(self.path)
        self.path = parsed_path.path
        print(f"DEBUG: Processed path: {self.path}")
        
        # Protect the admin frontend
        if self.path.startswith('/admin.html') or self.path.startswith('/admin.js'):
            if not self.check_auth():
                return
        
        if self.path == '/api/config':
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.end_headers()
            try:
                with open(CONFIG_FILE, 'r', encoding='utf-8') as f:
                    self.wfile.write(f.read().encode('utf-8'))
            except FileNotFoundError:
                self.wfile.write(b'{}')
        else:
            super().do_GET()

    def do_POST(self):
        # Protect all API write routes
        if not self.check_auth():
            return

        parsed_path = urlparse(self.path)
        path = parsed_path.path

        if path == '/api/config':
            length = int(self.headers['Content-Length'])
            data = self.rfile.read(length)
            try:
                # Validate JSON before saving
                json.loads(data)
                with open(CONFIG_FILE, 'wb') as f:
                    f.write(data)
                self.send_response(200)
                self.end_headers()
                self.wfile.write(b'{"status": "ok"}')
            except Exception as e:
                self.send_error(500, str(e))

        elif path == '/api/upload':
            form = cgi.FieldStorage(
                fp=self.rfile,
                headers=self.headers,
                environ={'REQUEST_METHOD': 'POST'}
            )

            if 'file' not in form:
                self.send_error(400, "No file field")
                return

            fileitem = form['file']
            target_path = form.getvalue('path') # relative path, e.g. "assets/logo.png" or "overlays/Theme/Post.png"

            if not target_path:
                 self.send_error(400, "No path specified")
                 return
            
            # Security check: prevent breakout
            abs_target = os.path.abspath(target_path)
            abs_cwd = os.path.abspath(os.getcwd())
            if not abs_target.startswith(abs_cwd):
                 self.send_error(403, "Forbidden path")
                 return

            # Ensure directory exists
            os.makedirs(os.path.dirname(abs_target), exist_ok=True)

            with open(abs_target, 'wb') as f:
                f.write(fileitem.file.read())

            self.send_response(200)
            self.end_headers()
            self.wfile.write(b'{"status": "uploaded"}')
        
        elif path == '/api/delete':
            length = int(self.headers['Content-Length'])
            data = json.loads(self.rfile.read(length))
            target_path = data.get('path')

            if not target_path:
                 self.send_error(400, "No path specified")
                 return
            
             # Security check: prevent breakout
            abs_target = os.path.abspath(target_path)
            abs_cwd = os.path.abspath(os.getcwd())
            if not abs_target.startswith(abs_cwd):
                 self.send_error(403, "Forbidden path")
                 return

            if os.path.exists(abs_target):
                if os.path.isdir(abs_target):
                     shutil.rmtree(abs_target)
                else:
                    os.remove(abs_target)
                self.send_response(200)
                self.end_headers()
                self.wfile.write(b'{"status": "deleted"}')
            else:
                self.send_error(404, "File not found")

        else:
            self.send_error(404, "Endpoint not found")

print(f"Serving at http://localhost:{PORT}")
with socketserver.TCPServer(("", PORT), RequestHandler) as httpd:
    httpd.serve_forever()
