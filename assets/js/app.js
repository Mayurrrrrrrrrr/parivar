/**
 * परिवार (Parivar) — Utility Functions
 */

// Panchang AJAX conversion (forms में use)
function convertToVS(dateInput, outputField, displayField) {
    const date = dateInput.value;
    if (!date) return;
    fetch(`/api/panchang.php?action=convert&gregorian=${date}`)
        .then(r => r.json())
        .then(data => {
            if (data.safalta) {
                if (outputField) outputField.value = data.data.formatted;
                if (displayField) displayField.textContent = '📅 ' + data.data.formatted;
            }
        })
        .catch(() => {});
}

// Copy to clipboard
function copyText(text) {
    navigator.clipboard.writeText(text)
        .then(() => showToast('कॉपी हो गया! ✓'))
        .catch(() => {});
}

// Toast notification
function showToast(msg, duration = 2500) {
    const t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:#2C1810;color:white;padding:10px 20px;border-radius:20px;font-size:13px;z-index:9999;animation:fadeIn .2s ease';
    document.body.appendChild(t);
    setTimeout(() => t.remove(), duration);
}

// Date formatter (Gregorian → Hindi display)
function formatDateHindi(dateStr) {
    if (!dateStr) return '';
    const months = ['जनवरी','फरवरी','मार्च','अप्रैल','मई','जून',
                    'जुलाई','अगस्त','सितंबर','अक्टूबर','नवंबर','दिसंबर'];
    const d = new Date(dateStr);
    return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
}
