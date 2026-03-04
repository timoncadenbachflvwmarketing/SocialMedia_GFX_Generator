<?php
// Simple Basic Auth Configuration
$expected_username = 'admin';
$expected_password = 'kumqab-noqguT-9qokga';

function check_auth() {
    global $expected_username, $expected_password;
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
        $_SERVER['PHP_AUTH_USER'] !== $expected_username || $_SERVER['PHP_AUTH_PW'] !== $expected_password) {
        header('WWW-Authenticate: Basic realm="Admin Access Required"');
        header('HTTP/1.0 401 Unauthorized');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$config_file = __DIR__ . '/config.json';

if ($action === 'config') {
    if ($method === 'GET') {
        if (file_exists($config_file)) {
            echo file_get_contents($config_file);
        } else {
            echo '{}';
        }
    } elseif ($method === 'POST') {
        check_auth();
        $input = file_get_contents('php://input');
        // Validate JSON
        if (json_decode($input) !== null) {
            file_put_contents($config_file, $input);
            echo json_encode(['status' => 'ok']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
        }
    }
    exit;
}

if ($action === 'upload' && $method === 'POST') {
    check_auth();
    if (!isset($_FILES['file']) || !isset($_POST['path'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing file or path']);
        exit;
    }

    $target_dir = __DIR__ . '/';
    $target_path = $target_dir . ltrim($_POST['path'], '/');
    
    // Security check: ensure path is within intended directories
    $real_target_dir = realpath($target_dir);
    
    // Create directory if it doesn't exist
    $dir = dirname($target_path);
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

    $real_dir = realpath($dir);
    if ($real_dir === false || strpos($real_dir, $real_target_dir) !== 0) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden path']);
        exit;
    }
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
        echo json_encode(['status' => 'uploaded']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Upload failed']);
    }
    exit;
}

if ($action === 'delete' && $method === 'POST') {
    check_auth();
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $path = $data['path'] ?? '';

    if (empty($path)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing path']);
        exit;
    }

    $target_dir = __DIR__ . '/';
    $target_path = $target_dir . ltrim($path, '/');
    
    // Security check
    $real_target_dir = realpath($target_dir);
    $real_path = realpath($target_path);
    
    if ($real_path === false || strpos($real_path, $real_target_dir) !== 0) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden path']);
        exit;
    }

    if (file_exists($real_path)) {
        if (is_dir($real_path)) {
            // Very simple non-recursive rmdir (assuming it's just the overlay folder)
            // Or leave to sysadmin if recursive needed, for now just @rmdir
            @rmdir($real_path);
        } else {
            unlink($real_path);
        }
        echo json_encode(['status' => 'deleted']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'File not found']);
    }
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Endpoint not found']);
