let config = null;

// Definition used for auto-detection
const FORMAT_SPECS = [
    { key: 'facebook_post_4x5', w: 1080, h: 1350, label: 'FB/Insta Post (4:5)', filename: 'flvw_post_1080x1350' },
    { key: 'instagram_post_4x5', w: 1080, h: 1350, label: 'FB/Insta Post (4:5)', filename: 'flvw_post_1080x1350' }, // Duplicate resolution handling needed
    { key: 'facebook_story', w: 1080, h: 1920, label: 'FB/Insta Story (9:16)', filename: 'flvw_story_1080x1920' },
    { key: 'instagram_story', w: 1080, h: 1920, label: 'FB/Insta Story (9:16)', filename: 'flvw_story_1080x1920' },
    { key: 'flvw_homepage', w: 1920, h: 1080, label: 'Homepage (16:9)', filename: 'flvw_homepage_1920x1080' },
    { key: 'flvw_werbung', w: 500, h: 500, label: 'Werbung (1:1)', filename: 'flvw_werbung_500x500' }
];

document.addEventListener('DOMContentLoaded', init);

async function init() {
    await loadConfig();
    setupEventListeners();
}

async function loadConfig() {
    try {
        const res = await fetch('/api/config?v=' + Date.now());
        config = await res.json();
        renderTextConfig();
        renderThemes();
    } catch (err) {
        showToast('Fehler beim Laden der Konfiguration', 'error');
        console.error(err);
    }
}

function renderTextConfig() {
    if (!config.text) return;
    document.getElementById('text_heroTitle').value = config.text.heroTitle || '';
    document.getElementById('text_heroSub').value = config.text.heroSub || '';
    document.getElementById('text_step1').value = config.text.step1 || '';
    document.getElementById('text_step2').value = config.text.step2 || '';
    document.getElementById('text_step3').value = config.text.step3 || '';
    document.getElementById('text_stepExtra').value = config.text.stepExtra || '';
    document.getElementById('text_footer').value = config.text.footer || '';
}

function renderThemes() {
    const list = document.getElementById('themesList');
    list.innerHTML = '';
    const tmpl = document.getElementById('themeTemplate');

    (config.themes || []).forEach((theme, index) => {
        const clone = tmpl.content.cloneNode(true);

        clone.querySelector('.theme-name').textContent = theme.name;
        clone.querySelector('.theme-path').textContent = `/${theme.path}`;
        clone.querySelector('.delete-theme-btn').addEventListener('click', () => deleteTheme(index));

        // Smart Upload
        const uploadInput = clone.querySelector('.smart-upload-input');
        uploadInput.addEventListener('change', (e) => handleSmartUpload(e, index));

        // Render Files
        const fileGrid = clone.querySelector('.file-grid');
        const files = theme.files || [];

        if (files.length === 0) {
            fileGrid.innerHTML = '<div style="color:#999; text-align:center; padding:20px; grid-column:1/-1;">Noch keine Overlays vorhanden.</div>';
        } else {
            files.forEach(file => {
                const card = document.createElement('div');
                card.className = 'file-card';
                card.innerHTML = `
                    <div class="delete-overlay" onclick="window.deleteOverlay(${index}, '${file.key}')">&times;</div>
                    <img src="overlays/${theme.path}${file.overlay}?v=${Date.now()}" class="file-preview">
                    <div class="file-info">
                        <strong>${file.label}</strong><br>
                        <span style="color:#666">${file.width}x${file.height}</span><br>
                        <span class="file-tag">${file.overlay}</span>
                    </div>
                `;
                fileGrid.appendChild(card);
            });
        }

        list.appendChild(clone);
    });
}

async function handleSmartUpload(event, themeIndex) {
    const files = Array.from(event.target.files);
    if (!files.length) return;

    let uploadedCount = 0;
    let errors = [];

    const theme = config.themes[themeIndex];
    if (!theme.files) theme.files = [];

    // Show loading state could be nice here
    showToast(`Verarbeite ${files.length} Dateien...`, 'success');

    for (const file of files) {
        try {
            // 1. Get Dimensions
            const dims = await getImageDimensions(file);
            console.log(`Analyzed ${file.name}: ${dims.w}x${dims.h}`);

            // 2. Find Matches
            const matches = FORMAT_SPECS.filter(spec => spec.w === dims.w && spec.h === dims.h);

            if (matches.length === 0) {
                errors.push(`${file.name}: Keine passende Auflösung (${dims.w}x${dims.h})`);
                continue;
            }

            // 3. Upload File
            // We upload ONE physical file, but we might map it to multiple keys (e.g. FB & Insta share 4:5 resolution)
            // Or we check if matches.length > 1 and apply to all suitable keys.
            // User requirement: "Name etc. automatisch ausgelesen". 
            // Implementation: If 1080x1350 is found, we map it to both facebook_post_4x5 AND instagram_post_4x5

            const targetPath = `overlays/${theme.path}${file.name}`;
            await uploadFile(file, targetPath);

            // 4. Update Config for ALL matches
            matches.forEach(match => {
                // Find existing entry or create new
                const existingIdx = theme.files.findIndex(f => f.key === match.key);
                const entry = {
                    key: match.key,
                    label: match.label,
                    width: match.w,
                    height: match.h,
                    overlay: file.name,
                    filename: match.filename
                };

                if (existingIdx >= 0) {
                    theme.files[existingIdx] = entry;
                } else {
                    theme.files.push(entry);
                }
            });

            uploadedCount++;

        } catch (err) {
            console.error(err);
            errors.push(`${file.name}: Fehler beim Lesen/Upload`);
        }
    }

    if (uploadedCount > 0) {
        await saveConfig(true);
        renderThemes();
        showToast(`${uploadedCount} Dateien erfolgreich verarbeitet!`, 'success');
    }

    if (errors.length > 0) {
        setTimeout(() => alert("Einige Dateien wurden übersprungen:\n" + errors.join("\n")), 500);
    }
}

function getImageDimensions(file) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve({ w: img.width, h: img.height });
        img.onerror = reject;
        img.src = URL.createObjectURL(file);
    });
}

// Make globally available for inline onclick
window.deleteOverlay = async function (themeIndex, key) {
    if (!confirm('Overlay wirklich löschen?')) return;

    // Deleting the logical entry. 
    // Ideally we also delete the physical file IF no other key uses it, but that's complex logic.
    // For now, we just remove the config entry as requested "Motive verwalten".

    const theme = config.themes[themeIndex];
    const idx = theme.files.findIndex(f => f.key === key);
    if (idx >= 0) {
        theme.files.splice(idx, 1);
        await saveConfig(true);
        renderThemes();
    }
};

async function deleteTheme(index) {
    if (!confirm('Motiv wirklich löschen?')) return;

    const theme = config.themes[index];
    // Optional: Delete folder on server? 
    // Keeping it simple: remove from config.
    // If user wants to delete folder, we can add that logic later.

    config.themes.splice(index, 1);
    await saveConfig(true);
    renderThemes();
}

function setupEventListeners() {
    document.getElementById('saveConfigBtn').addEventListener('click', () => {
        updateConfigFromUI();
        saveConfig();
    });

    document.getElementById('addThemeBtn').addEventListener('click', () => {
        const name = document.getElementById('newThemeName').value.trim();
        if (!name) return;
        const safeName = name.replace(/[^a-zA-Z0-9_-]/g, '_');
        config.themes.push({ name: name, path: `${safeName}/`, files: [] });
        document.getElementById('newThemeName').value = '';
        saveConfig(true);
        renderThemes();
    });

    document.getElementById('logoUpload').addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (file) {
            try {
                await uploadFile(file, 'assets/logo.png');
                document.getElementById('currentLogo').src = 'assets/logo.png?v=' + Date.now();
                showToast('Logo erfolgreich aktualisiert!');
            } catch (err) {
                showToast('Logo Upload fehlgeschlagen', 'error');
            }
        }
    });
}

function updateConfigFromUI() {
    config.text = {
        heroTitle: document.getElementById('text_heroTitle').value,
        heroSub: document.getElementById('text_heroSub').value,
        step1: document.getElementById('text_step1').value,
        step2: document.getElementById('text_step2').value,
        step3: document.getElementById('text_step3').value,
        stepExtra: document.getElementById('text_stepExtra').value,
        footer: document.getElementById('text_footer').value
    };
}

async function uploadFile(file, path) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('path', path);
    const res = await fetch('/api/upload', { method: 'POST', body: formData });
    if (!res.ok) throw new Error('Upload failed');
}

async function saveConfig(silent = false) {
    const res = await fetch('/api/config', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(config)
    });
    if (res.ok) {
        if (!silent) showToast('Konfiguration gespeichert!');
    } else {
        showToast('Fehler beim Speichern', 'error');
    }
}

function showToast(msg, type = 'success') {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = `toast ${type} show`;
    setTimeout(() => el.classList.remove('show'), 3000);
}
