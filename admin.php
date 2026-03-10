<?php
$expected_username = 'admin';
$expected_password = 'kumqab-noqguT-9qokga';
if (
    !isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== $expected_username || $_SERVER['PHP_AUTH_PW'] !== $expected_password
) {
    header('WWW-Authenticate: Basic realm="Admin Access Required"');
    header('HTTP/1.0 401 Unauthorized');
    echo "Unauthorized";
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FLVW GFX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0"
        rel="stylesheet" />
    <style>
        .material-symbols-rounded {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            vertical-align: middle;
        }

        :root {
            /* Legacy/Config mapping (will be updated via JS) */
            --primary: #009640;

            /* MD3 System Tokens */
            --md-sys-color-primary: var(--primary);
            --md-sys-color-on-primary: #ffffff;
            --md-sys-color-primary-container: color-mix(in srgb, var(--primary) 15%, transparent);
            --md-sys-color-on-primary-container: color-mix(in srgb, var(--primary) 90%, black);

            --md-sys-color-surface: #fdfdfd;
            --md-sys-color-surface-container-low: #f8f9fa;
            --md-sys-color-surface-container: #f1f3f4;
            --md-sys-color-on-surface: #1f1f1f;
            --md-sys-color-on-surface-variant: #444746;

            --md-sys-color-outline: #747775;
            --md-sys-color-outline-variant: #c4c7c5;

            --md-sys-color-error: #b3261e;
            --md-sys-color-error-container: #f9dedc;
            --md-sys-color-on-error-container: #410e0b;

            /* MD3 Shape Tokens */
            --md-sys-shape-corner-none: 0px;
            --md-sys-shape-corner-extra-small: 4px;
            --md-sys-shape-corner-small: 8px;
            --md-sys-shape-corner-medium: 12px;
            --md-sys-shape-corner-large: 16px;
            --md-sys-shape-corner-full: 100px;

            /* MD3 Elevation Tokens */
            --md-sys-elevation-level1: 0 1px 2px 0 rgba(0, 0, 0, 0.3), 0 1px 3px 1px rgba(0, 0, 0, 0.15);
            --md-sys-elevation-level2: 0 1px 2px 0 rgba(0, 0, 0, 0.3), 0 2px 6px 2px rgba(0, 0, 0, 0.15);
            --md-sys-elevation-level3: 0 1px 3px 0 rgba(0, 0, 0, 0.3), 0 4px 8px 3px rgba(0, 0, 0, 0.15);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--md-sys-color-surface-container-low);
            color: var(--md-sys-color-on-surface);
            margin: 0;
            padding: 0;
            line-height: 1.5;
            letter-spacing: 0.25px;
        }

        .layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
            transition: grid-template-columns 0.3s ease;
        }

        .layout.sidebar-collapsed {
            grid-template-columns: 80px 1fr;
        }

        .layout.sidebar-hidden {
            grid-template-columns: 0 1fr;
        }

        /* Sidebar */
        .sidebar {
            background: var(--md-sys-color-surface);
            border-right: 1px solid var(--md-sys-color-outline-variant);
            padding: 24px 12px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            transition: all 0.3s cubic-bezier(0.2, 0, 0, 1);
        }

        .layout.sidebar-collapsed .sidebar {
            padding: 24px 12px;
        }

        .layout.sidebar-hidden .sidebar {
            padding: 0;
            border-right: none;
            opacity: 0;
            pointer-events: none;
        }

        .brand {
            font-size: 1.375rem;
            font-weight: 500;
            color: var(--md-sys-color-primary);
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-left: 12px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            color: var(--md-sys-color-on-surface-variant);
            text-decoration: none;
            border-radius: var(--md-sys-shape-corner-full);
            margin-bottom: 4px;
            font-weight: 500;
            transition: background-color 0.2s cubic-bezier(0.2, 0, 0, 1), color 0.2s;
            white-space: nowrap;
        }

        .layout.sidebar-collapsed .nav-link {
            justify-content: center;
            padding: 12px;
        }

        .nav-text {
            transition: opacity 0.2s;
        }

        .layout.sidebar-collapsed .nav-text {
            display: none;
        }

        .layout.sidebar-collapsed #sidebarLogo {
            display: none !important;
        }

        .nav-link .material-symbols-rounded {
            font-size: 24px;
            width: 24px;
            text-align: center;
        }

        .nav-link:hover {
            background-color: var(--md-sys-color-surface-container);
            color: var(--md-sys-color-on-surface);
        }

        .nav-link.active {
            background-color: var(--md-sys-color-primary-container);
            color: var(--md-sys-color-on-primary-container);
        }

        /* Main Content */
        .main-content {
            padding: 40px;
            max-width: 1000px;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: -40px -40px 32px -40px;
            padding: 16px 40px;
            background-color: var(--md-sys-color-surface);
            border-bottom: 1px solid var(--md-sys-color-outline-variant);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .header-actions-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .sidebar-toggle-btn {
            background: transparent;
            border: none;
            border-radius: var(--md-sys-shape-corner-full);
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--md-sys-color-on-surface);
            transition: background-color 0.2s;
            font-size: 1.25rem;
        }

        .sidebar-toggle-btn:hover {
            background-color: var(--md-sys-color-surface-container);
        }

        h1 {
            font-size: 1.75rem;
            /* Headline Small */
            font-weight: 400;
            margin: 0;
            line-height: 2.25rem;
            letter-spacing: 0;
        }

        h2 {
            font-size: 1.375rem;
            /* Title Large */
            font-weight: 400;
            margin: 0 0 16px 0;
            line-height: 1.75rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 24px;
            height: 40px;
            border-radius: var(--md-sys-shape-corner-full);
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.2s cubic-bezier(0.2, 0, 0, 1);
            font-size: 0.875rem;
            /* Label Large */
            letter-spacing: 0.1px;
            font-family: inherit;
            position: relative;
            overflow: hidden;
            box-shadow: none;
        }

        .btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background-color: currentColor;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .btn:hover::before {
            opacity: 0.08;
        }

        .btn:active::before {
            opacity: 0.12;
        }

        .btn-primary {
            background: var(--md-sys-color-primary);
            color: var(--md-sys-color-on-primary);
        }

        .btn-primary:hover {
            box-shadow: var(--md-sys-elevation-level1);
        }

        .btn-ghost {
            background: transparent;
            color: var(--md-sys-color-primary);
            border: 1px solid var(--md-sys-color-outline);
        }

        .btn-ghost:hover {
            border-color: var(--md-sys-color-primary);
        }

        .btn-danger {
            background: var(--md-sys-color-error-container);
            color: var(--md-sys-color-on-error-container);
        }

        .btn-danger:hover {
            /* State layer handles the visual change */
        }

        /* Cards */
        .card {
            background: var(--md-sys-color-surface);
            border-radius: var(--md-sys-shape-corner-medium);
            box-shadow: var(--md-sys-elevation-level1);
            padding: 24px;
            margin-bottom: 24px;
            border: none;
            transition: box-shadow 0.2s cubic-bezier(0.2, 0, 0, 1);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group.full {
            grid-column: span 2;
        }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--md-sys-color-on-surface);
            margin-bottom: 8px;
            letter-spacing: 0.1px;
        }

        input[type="text"],
        input[type="password"],
        select,
        textarea {
            width: 100%;
            padding: 16px;
            border: 1px solid var(--md-sys-color-outline);
            border-radius: var(--md-sys-shape-corner-extra-small);
            font-size: 1rem;
            transition: border-color 0.2s, background-color 0.2s;
            font-family: inherit;
            background-color: var(--md-sys-color-surface);
            color: var(--md-sys-color-on-surface);
            letter-spacing: 0.5px;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--md-sys-color-primary);
            box-shadow: inset 0 0 0 1px var(--md-sys-color-primary);
            background-color: var(--md-sys-color-surface);
        }

        /* Themes */
        .theme-card {
            border: 1px solid var(--md-sys-color-outline-variant);
            border-radius: var(--md-sys-shape-corner-medium);
            padding: 24px;
            margin-bottom: 24px;
            background: var(--md-sys-color-surface-container-low);
        }

        .theme-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            border-bottom: 1px solid var(--md-sys-color-outline-variant);
            padding-bottom: 16px;
        }

        .smart-upload-area {
            border: 2px dashed var(--md-sys-color-outline);
            border-radius: var(--md-sys-shape-corner-medium);
            padding: 48px 24px;
            text-align: center;
            background: var(--md-sys-color-surface);
            transition: background-color 0.2s, border-color 0.2s;
            cursor: pointer;
            position: relative;
        }

        .smart-upload-area:hover {
            border-color: var(--md-sys-color-primary);
            background: var(--md-sys-color-primary-container);
            color: var(--md-sys-color-on-primary-container);
        }

        .smart-upload-area input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .file-card {
            background: var(--md-sys-color-surface);
            border: 1px solid var(--md-sys-color-outline-variant);
            border-radius: var(--md-sys-shape-corner-medium);
            overflow: hidden;
            position: relative;
            box-shadow: var(--md-sys-elevation-level1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .file-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--md-sys-elevation-level2);
        }

        .file-preview {
            height: 140px;
            width: 100%;
            object-fit: contain;
            background: var(--md-sys-color-surface-container);
            padding: 10px;
        }

        .file-info {
            padding: 10px;
            font-size: 0.8rem;
        }

        .file-tag {
            display: inline-block;
            background: #e5e7eb;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.7rem;
            margin-top: 4px;
            font-weight: 600;
        }

        .file-label-input {
            width: 100%;
            padding: 4px;
            margin-bottom: 4px;
            border: 1px solid transparent;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.9rem;
            background: transparent;
            color: inherit;
            transition: all 0.2s;
            font-family: inherit;
        }

        .file-label-input:hover {
            border-color: #ddd;
            background: #fff;
        }

        .file-label-input:focus {
            outline: none;
            border: 2px solid var(--md-sys-color-primary);
            padding: 3px;
        }

        .delete-overlay {
            position: absolute;
            top: 8px;
            right: 8px;
            background: var(--md-sys-color-error-container);
            border-radius: var(--md-sys-shape-corner-full);
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--md-sys-color-on-error-container);
            border: none;
            transition: background-color 0.2s;
        }

        .delete-overlay:hover {
            background: var(--md-sys-color-error);
            color: var(--md-sys-color-error-container);
        }

        /* Notification */
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 16px 24px;
            border-radius: var(--md-sys-shape-corner-extra-small);
            color: var(--md-sys-color-on-surface);
            background: var(--md-sys-color-surface-container-high);
            font-weight: 500;
            box-shadow: var(--md-sys-elevation-level3);
            transform: translateY(150%);
            transition: transform 0.3s cubic-bezier(0.2, 0, 0, 1);
            z-index: 50;
        }

        .toast.show {
            transform: translateY(0);
        }

        .toast.success {
            background: var(--md-sys-color-primary);
            color: var(--md-sys-color-on-primary);
        }

        .toast.error {
            background: var(--md-sys-color-error);
            color: var(--md-sys-color-on-error);
        }

        /* Responsive Layout */
        @media (max-width: 768px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: relative;
                height: auto;
                border-right: none;
                border-bottom: 1px solid var(--md-sys-color-outline-variant);
                padding: 16px;
                display: flex;
                flex-direction: column;
            }

            .main-content {
                padding: 24px 16px;
            }

            .header-actions {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: span 1;
            }

            .theme-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }
    </style>
    <script src="admin.js?v=8" defer></script>
</head>

<body>

    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2 style="display: flex; align-items: center; gap: 8px; overflow: hidden; white-space: nowrap;">
                    <img src="assets/logo.png?v=<?= file_exists('assets/logo.png') ? filemtime('assets/logo.png') : '1' ?>"
                        id="sidebarLogo" style="height: 40px; display: block; flex-shrink: 0;" alt="SMG Admin">
                </h2>
            </div>
            <nav>
                <a href="#texts" class="nav-link active"><span class="material-symbols-rounded">text_fields</span> <span
                        class="nav-text">Texte & Inhalte</span></a>
                <a href="#thema" class="nav-link"><span class="material-symbols-rounded">palette</span> <span
                        class="nav-text">Design & Farben</span></a>
                <a href="#themes" class="nav-link"><span class="material-symbols-rounded">imagesmode</span> <span
                        class="nav-text">Motive & Design</span></a>
                <a href="#anleitung" class="nav-link"><span class="material-symbols-rounded">menu_book</span> <span
                        class="nav-text">Anleitung</span></a>
                <a href="#about" class="nav-link"><span class="material-symbols-rounded">info</span> <span
                        class="nav-text">Über SMG Admin</span></a>
                <a href="index.html" class="nav-link" target="_blank"
                    style="margin-top: 40px; color: var(--md-sys-color-primary);"><span
                        class="material-symbols-rounded">open_in_new</span> <span class="nav-text">Zum
                        Generator</span></a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="header-actions">
                <div class="header-actions-left">
                    <button id="toggleSidebarBtn" class="sidebar-toggle-btn" title="Menü umschalten">
                        <span class="material-symbols-rounded">menu</span>
                    </button>
                    <h1>Einstellungen</h1>
                </div>
                <button id="saveConfigBtn" class="btn btn-primary">
                    <span class="material-symbols-rounded">save</span> Änderungen speichern
                </button>
            </div>

            <section id="texts" class="card">
                <h2>Texte bearbeiten</h2>
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Haupt-Überschrift</label>
                        <input type="text" id="text_heroTitle">
                    </div>
                    <div class="form-group full">
                        <label>Untertitel</label>
                        <textarea id="text_heroSub" rows="2"></textarea>
                    </div>
                    <!-- Steps -->
                    <div class="form-group">
                        <label>Schritt 1</label>
                        <input type="text" id="text_step1">
                    </div>
                    <div class="form-group">
                        <label>Schritt 2</label>
                        <input type="text" id="text_step2">
                    </div>
                    <div class="form-group">
                        <label>Schritt 3</label>
                        <input type="text" id="text_step3">
                    </div>
                    <div class="form-group">
                        <label>Extra Info</label>
                        <input type="text" id="text_stepExtra">
                    </div>
                    <div class="form-group full">
                        <label>Footer Text</label>
                        <input type="text" id="text_footer">
                    </div>
                </div>
            </section>

            <section id="logo-section" class="card">
                <h2>Logo</h2>
                <div style="display: flex; gap: 20px; align-items: center;">
                    <div style="padding: 10px; background: #eee; border-radius: 8px;">
                        <img src="assets/logo.png?v=<?= file_exists('assets/logo.png') ? filemtime('assets/logo.png') : '1' ?>"
                            id="currentLogo" style="height: 40px; display: block;">
                    </div>
                    <div style="flex: 1;">
                        <label>Neues Logo hochladen</label>
                        <input type="file" id="logoUpload" accept="image/png">
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px;">Empfohlen: PNG mit
                            Transparenz, max. 80px Höhe</div>
                    </div>
                </div>
            </section>

            <section id="themes">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2>Motive (Themen)</h2>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="newThemeName" placeholder="Neuer Motiv-Name"
                            style="width: 200px; padding: 8px;">
                        <button id="addThemeBtn" class="btn btn-ghost">Motiv hinzufügen</button>
                    </div>
                </div>

                <div id="themesList"></div>
            </section>
            <section id="thema" class="card hidden">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2>Design & Farben</h2>
                </div>
                <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 20px;">Lege hier die primären
                    Farben fest. Alle anderen Nuancen für Buttons und Leuchteffekte werden automatisch berechnet.</p>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Hauptakzent (Normal)</label>
                        <input type="color" id="theme_accent"
                            style="width: 100%; height: 40px; cursor: pointer; padding: 2px;">
                    </div>
                    <div class="form-group">
                        <label>Hellerer Akzent (z.B. Hover/Boxen)</label>
                        <input type="color" id="theme_accentLight"
                            style="width: 100%; height: 40px; cursor: pointer; padding: 2px;">
                    </div>
                    <div class="form-group">
                        <label>Dunklerer Akzent (z.B. Focus)</label>
                        <input type="color" id="theme_accentDark"
                            style="width: 100%; height: 40px; cursor: pointer; padding: 2px;">
                    </div>
                    <div class="form-group">
                        <label>Button Verlauf (Start)</label>
                        <input type="color" id="theme_gradientStart"
                            style="width: 100%; height: 40px; cursor: pointer; padding: 2px;">
                    </div>
                    <div class="form-group">
                        <label>Button Verlauf (Ende)</label>
                        <input type="color" id="theme_gradientEnd"
                            style="width: 100%; height: 40px; cursor: pointer; padding: 2px;">
                    </div>
                    <div class="form-group"
                        style="grid-column: 1 / -1; margin-top: 10px; border-top: 1px solid var(--border); padding-top: 15px;">
                        <label>Admin-Panel Akzentfarbe</label>
                        <input type="color" id="theme_adminAccent"
                            style="width: 100%; height: 40px; cursor: pointer; padding: 2px;">
                    </div>
                </div>
            </section>

            <section id="anleitung" class="card hidden">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2>Anleitung (FAQ) bearbeiten</h2>
                    <div style="display: flex; gap: 10px;">
                        <button id="resetGuideBtn" class="btn btn-secondary"
                            style="background: var(--ui-surface-06); color: var(--text);">FAQ zurücksetzen</button>
                        <button id="addGuideBtn" class="btn btn-ghost">Abschnitt hinzufügen</button>
                    </div>
                </div>
                <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 20px;">Hier kannst du die Inhalte
                    der Anleitungs-Seite anpassen. Verwende simple HTML-Tags für Formatierungen, z.B.
                    <code>&lt;strong&gt;fett&lt;/strong&gt;</code> oder <code>&lt;br&gt;</code> für Zeilenumbrüche.
                </p>
                <div id="guideList"></div>
            </section>

            <section id="about" class="card hidden">
                <h2>Über SMG Admin</h2>
                <div
                    style="background: var(--surface); padding: 24px; border-radius: var(--radius); border: 1px solid var(--border); line-height: 1.6;">
                    <h3 style="margin-top: 0; color: var(--primary);">Social Media Grafik (SMG) Generator</h3>
                    <p><strong>Version:</strong> 1.4.0</p>
                    <p><strong>Agentur/Entwickler:</strong> FLVW Marketing GmbH</p>
                    <p><strong>Technologien:</strong> HTML5, CSS3, Vanilla JavaScript, Python 3 (Backend)</p>
                    <hr style="border: 0; border-top: 1px solid var(--border); margin: 20px 0;">
                    <p>
                        Diese Software wurde entwickelt, um Vereinen und Verbänden die automatische Erstellung
                        und Formatierung von Social-Media-Grafiken so einfach wie möglich zu machen.
                        Über dieses Admin-Panel können Texte, Farben, Overlays (Motive) und die visuelle
                        Erscheinung (Glass-Effekte, Akzentfarben) direkt und ohne Programmierkenntnisse
                        angepasst werden.
                    </p>
                    <p>
                        Bei Fragen oder Problemen wende dich bitte an deinen zuständigen Administrator
                        oder den Support der FLVW Marketing GmbH.
                    </p>
                </div>
            </section>
        </main>
    </div>

    <div id="toast" class="toast">Nachricht</div>

    <!-- Template for Guide Section -->
    <template id="guideTemplate">
        <div class="card theme-card guide-item" draggable="true"
            style="margin-bottom: 16px; padding: 16px; cursor: move; transition: transform 0.2s, box-shadow 0.2s;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                <div class="drag-handle"
                    style="padding-right: 12px; color: var(--text-muted); font-size: 1.2rem; cursor: grab; align-self: center;"
                    title="Ziehen zum Sortieren"><span class="material-symbols-rounded">drag_indicator</span></div>
                <div
                    style="flex: 1; display: grid; grid-template-columns: 80px 1fr 1fr; gap: 12px; align-items: center;">
                    <div>
                        <label style="font-size: 0.75rem;">Icon</label>
                        <input type="text" class="guide-icon" placeholder="z.B. ✦"
                            style="padding: 6px; text-align: center;">
                    </div>
                    <div>
                        <label style="font-size: 0.75rem;">Menü-Titel (Kurz)</label>
                        <input type="text" class="guide-menu-title" placeholder="z.B. Wofür gedacht?">
                    </div>
                    <div>
                        <label style="font-size: 0.75rem;">Haupt-Titel (H3)</label>
                        <input type="text" class="guide-title" placeholder="z.B. Wofür ist der Generator gedacht?">
                    </div>
                </div>
                <button class="btn btn-danger btn-ghost delete-guide-btn"
                    style="padding: 6px 10px; font-size: 0.8rem; margin-left: 16px;">Löschen</button>
            </div>
            <details class="guide-content-details" style="margin-top: 10px; cursor: pointer;">
                <summary style="font-size: 0.85rem; font-weight: 600; color: var(--primary); outline: none;">Inhalt
                    aufklappen / bearbeiten</summary>
                <div style="margin-top: 10px;">
                    <label style="font-size: 0.75rem;">Inhalt (HTML erlaubt)</label>
                    <textarea class="guide-content-html" rows="4"
                        style="font-family: monospace; font-size: 0.85rem; line-height: 1.4; resize: vertical; min-height: 120px;"></textarea>
                </div>
            </details>
        </div>
    </template>

    <!-- Template for Theme Item -->
    <template id="themeTemplate">
        <div class="card theme-card">
            <div class="theme-header">
                <div style="flex: 1; margin-right: 20px;">
                    <input class="theme-name-input" type="text" title="Motiv-Name bearbeiten"
                        style="width: 100%; font-size: 1.1rem; font-weight: bold; margin: 0; padding: 4px 8px; border: 1px solid transparent; background: transparent; border-radius: 4px; transition: all 0.2s; color: var(--text-main); font-family: inherit;" />
                    <code class="theme-path"
                        style="font-size: 0.8rem; color: var(--text-muted); display: block; padding-left: 8px; margin-top: 4px;"></code>
                </div>
                <button class="btn btn-danger btn-ghost delete-theme-btn"
                    style="padding: 6px 12px; font-size: 0.8rem;">Löschen</button>
            </div>

            <div class="smart-upload-area">
                <input type="file" class="smart-upload-input" accept="image/png,image/jpeg" multiple>
                <div style="margin-bottom: 10px; color: var(--text-muted);"><span class="material-symbols-rounded"
                        style="font-size: 40px;">folder_open</span></div>
                <strong>Bilder hier ablegen oder klicken</strong>
                <p style="margin: 5px 0 0 0; font-size: 0.85rem; color: var(--text-muted);">
                    Lade mehrere PNGs hoch. Das System erkennt automatisch das Format anhand der Größe (z.B. 1080x1350).
                </p>
            </div>

            <div class="file-grid">
                <!-- File cards go here -->
            </div>
        </div>
    </template>

</body>

</html>