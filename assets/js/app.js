/**
 * परिवार (Parivar) — मुख्य जावास्क्रिप्ट
 */

/**
 * वंश वृक्ष (Family Tree) रेंडरिंग
 */
function renderFamilyTree(data) {
    const svg = document.getElementById('family-tree-svg');
    const g = document.getElementById('tree-g');
    if (!svg || !g) return;

    const nodes = data.nodes;
    const links = data.links;

    // Simple Grid Layout for Phase 1
    const nodeWidth = 120;
    const nodeHeight = 60;
    const gapX = 60;
    const gapY = 100;

    let html = '';

    // Calculate positions (Placeholder simple algorithm)
    // In a real app, this would use a tree layout algorithm
    nodes.forEach((node, i) => {
        const x = 100 + (i % 4) * (nodeWidth + gapX);
        const y = 100 + Math.floor(i / 4) * (nodeHeight + gapY);
        node.x = x;
        node.y = y;

        const color = node.jeevit == 1 ? '#B5470B' : '#888888';
        
        html += `
            <g class="node" onclick="window.location.href='vyakti.php?id=${node.id}'" style="cursor: pointer;">
                <rect x="${x}" y="${y}" width="${nodeWidth}" height="${nodeHeight}" rx="10" fill="white" stroke="${color}" stroke-width="2" />
                <text x="${x + nodeWidth/2}" y="${y + 25}" text-anchor="middle" font-size="14" font-weight="bold" fill="${color}">${node.name}</text>
                <text x="${x + nodeWidth/2}" y="${y + 45}" text-anchor="middle" font-size="11" fill="#666">${node.kul_naam || ''}</text>
            </g>
        `;
    });

    // Draw links
    links.forEach(link => {
        const source = nodes.find(n => n.id == link.vyakti_a_id);
        const target = nodes.find(n => n.id == link.vyakti_b_id);
        if (source && target) {
            html = `<line x1="${source.x + nodeWidth/2}" y1="${source.y + nodeHeight}" x2="${target.x + nodeWidth/2}" y2="${target.y}" stroke="#E8D5C4" stroke-width="2" />` + html;
        }
    });

    g.innerHTML = html;

    // Basic Pan/Zoom placeholder logic
    let isDragging = false;
    let startX, startY, translateX = 0, translateY = 0;

    svg.addEventListener('mousedown', e => {
        isDragging = true;
        startX = e.clientX - translateX;
        startY = e.clientY - translateY;
        svg.style.cursor = 'grabbing';
    });

    window.addEventListener('mousemove', e => {
        if (!isDragging) return;
        translateX = e.clientX - startX;
        translateY = e.clientY - startY;
        g.setAttribute('transform', `translate(${translateX}, ${translateY})`);
    });

    window.addEventListener('mouseup', () => {
        isDragging = false;
        svg.style.cursor = 'grab';
    });
}

/**
 * SVG को PNG के रूप में सेव करें
 */
function exportTree() {
    const svg = document.getElementById('family-tree-svg');
    if (!svg) return;

    const svgData = new XMLSerializer().serializeToString(svg);
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = new Image();

    // Get dimensions
    const bbox = svg.getBBox();
    canvas.width = bbox.width + 100;
    canvas.height = bbox.height + 100;

    img.onload = function() {
        ctx.fillStyle = '#FFF8F0'; // Background color
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 50, 50);
        
        const pngUrl = canvas.toDataURL('image/png');
        const downloadLink = document.createElement('a');
        downloadLink.href = pngUrl;
        downloadLink.download = 'vansh_vriksha.png';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    };

    img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
}

/**
 * Form Helper: AJAX Date Conversion
 */
function setupDateConversion(gInputId, vsInputId) {
    const gInput = document.getElementById(gInputId);
    const vsInput = document.getElementById(vsInputId);
    if (!gInput || !vsInput) return;

    gInput.addEventListener('change', function() {
        const date = this.value;
        if (!date) return;
        fetch(`/api/panchang.php?action=convert&gregorian=${date}`)
            .then(r => r.json())
            .then(res => {
                if (res.safalta) vsInput.value = res.data.formatted;
            });
    });
}
