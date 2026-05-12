<?php
/**
 * वंश वृक्ष (v3.0) — Proper Hierarchical Family Tree
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();
?>

<style>
    body {
        overflow: hidden;
    }

    .tree-page-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        flex-direction: column;
        background: var(--bg-main);
        overflow: hidden;
    }

    .tree-toolbar {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 16px;
        background: var(--bg-card);
        border-bottom: 0.5px solid rgba(255, 107, 53, 0.12);
        z-index: 100;
        gap: 8px;
    }

    .tree-toolbar-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tree-toolbar-actions {
        display: flex;
        gap: 6px;
        align-items: center;
    }

    .tree-btn {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 7px 12px;
        border-radius: 10px;
        border: 1px solid rgba(255, 107, 53, 0.2);
        background: var(--bg-secondary);
        color: var(--text-secondary);
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        transition: all 0.15s;
        white-space: nowrap;
    }

    .tree-btn:hover {
        background: rgba(255, 107, 53, 0.1);
        color: var(--rang-pramukh);
    }

    .tree-legend {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-shrink: 0;
    }

    .legend-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .legend-label {
        font-size: 10px;
        color: var(--text-muted);
        font-weight: 500;
    }

    #tree-canvas {
        flex: 1;
        overflow: hidden;
        cursor: grab;
        position: relative;
    }

    #tree-canvas:active {
        cursor: grabbing;
    }

    #tree-svg {
        width: 100%;
        height: 100%;
        display: block;
    }

    .tree-loading {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: var(--bg-main);
        z-index: 50;
        gap: 16px;
    }

    .tree-loading-spinner {
        width: 48px;
        height: 48px;
        border: 3px solid rgba(255, 107, 53, 0.15);
        border-top-color: #FF6B35;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .tree-loading p {
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 500;
    }

    .tree-empty {
        position: absolute;
        inset: 0;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .tree-empty i {
        font-size: 56px;
        color: var(--text-muted);
        opacity: 0.3;
    }

    .tree-empty p {
        font-size: 14px;
        color: var(--text-muted);
    }

    .tree-tooltip {
        position: fixed;
        background: #2C1810;
        color: white;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 12px;
        pointer-events: none;
        z-index: 9999;
        display: none;
        max-width: 200px;
        line-height: 1.6;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
    }

    [data-theme="dark"] #tree-canvas {
        background: #0F172A;
    }
</style>

<div class="tree-page-wrapper">
    <div class="tree-toolbar">
        <a href="/pages/dashboard.php" style="color:var(--text-main);font-size:20px;text-decoration:none;flex-shrink:0">
            <i class="ti ti-arrow-left"></i>
        </a>
        <div class="tree-toolbar-title">🌳 <span>वंश वृक्ष</span></div>
        <div class="tree-toolbar-actions">
            <div class="tree-legend" id="legend-bar" style="display:none">
                <span class="legend-dot" style="background:#228B22"></span><span class="legend-label">पुरुष</span>
                <span class="legend-dot" style="background:#C2185B"></span><span class="legend-label">महिला</span>
                <span class="legend-dot" style="background:#8B4513"></span><span class="legend-label">दिवंगत</span>
            </div>
            <button class="tree-btn" onclick="zoomIn()"><i class="ti ti-zoom-in"></i></button>
            <button class="tree-btn" onclick="zoomOut()"><i class="ti ti-zoom-out"></i></button>
            <button class="tree-btn" onclick="resetView()"><i class="ti ti-home"></i></button>
        </div>
    </div>
    <div id="tree-canvas">
        <div class="tree-loading" id="tree-loading">
            <div class="tree-loading-spinner"></div>
            <p>वंश वृक्ष लोड हो रहा है...</p>
        </div>
        <div class="tree-empty" id="tree-empty">
            <i class="ti ti-users-minus"></i>
            <p>अभी कोई सदस्य नहीं जोड़ा गया।</p>
            <a href="/pages/sadasy_banao.php" class="btn btn-primary"
                style="width:auto;padding:8px 16px;font-size:13px">+ सदस्य जोड़ें</a>
        </div>
        <svg id="tree-svg"></svg>
    </div>
</div>
<div class="tree-tooltip" id="tree-tooltip"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.8.5/d3.min.js"></script>
<script>
    const CARD_W = 130, CARD_H = 76, H_GAP = 40, V_GAP = 90, SPOUSE_GAP = 24;
    let svg, mainG, zoomBehavior, treeData = null;

    document.addEventListener('DOMContentLoaded', () => {
        if (window.innerWidth > 600) document.getElementById('legend-bar').style.display = 'flex';
        const pid = new URLSearchParams(window.location.search).get('parivar_id') || '';
        fetch(`../api/vyakti.php?action=tree_full&parivar_id=${pid}`)
            .then(r => r.json())
            .then(data => {
                document.getElementById('tree-loading').style.display = 'none';
                if (data.safalta && data.data?.nodes?.length > 0) {
                    treeData = data.data;
                    buildTree(treeData);
                } else {
                    document.getElementById('tree-empty').style.display = 'flex';
                }
            })
            .catch(() => {
                document.getElementById('tree-loading').innerHTML = '<p style="color:#E24B4A">डेटा लोड नहीं हुआ। पेज रिफ्रेश करें।</p>';
            });
    });

    function buildTree(data) {
        const nodes = data.nodes;
        const edges = data.edges;
        d3.select('#tree-svg').selectAll('*').remove();

        const canvasEl = document.getElementById('tree-canvas');
        const W = canvasEl.clientWidth, H = canvasEl.clientHeight;

        svg = d3.select('#tree-svg').attr('width', W).attr('height', H);
        const defs = svg.append('defs');

        // Branch gradient (brown trunk → green branch)
        const bg = defs.append('linearGradient').attr('id', 'branchGrad').attr('x1', '0%').attr('y1', '0%').attr('x2', '0%').attr('y2', '100%');
        bg.append('stop').attr('offset', '0%').attr('stop-color', '#8B5E3C').attr('stop-opacity', 0.8);
        bg.append('stop').attr('offset', '100%').attr('stop-color', '#5C3A1E').attr('stop-opacity', 0.9);

        // Spouse connector gradient
        const sg = defs.append('linearGradient').attr('id', 'spouseGrad').attr('x1', '0%').attr('y1', '0%').attr('x2', '100%').attr('y2', '0%');
        sg.append('stop').attr('offset', '0%').attr('stop-color', '#FF9F1C').attr('stop-opacity', 0.9);
        sg.append('stop').attr('offset', '100%').attr('stop-color', '#FF6B35').attr('stop-opacity', 0.9);

        // Card shadow filters
        const f1 = defs.append('filter').attr('id', 'cardShadow').attr('x', '-20%').attr('y', '-20%').attr('width', '140%').attr('height', '140%');
        f1.append('feDropShadow').attr('dx', 0).attr('dy', 3).attr('stdDeviation', 6).attr('flood-color', 'rgba(0,0,0,0.10)');
        const f2 = defs.append('filter').attr('id', 'cardShadowHover').attr('x', '-30%').attr('y', '-30%').attr('width', '160%').attr('height', '160%');
        f2.append('feDropShadow').attr('dx', 0).attr('dy', 6).attr('stdDeviation', 10).attr('flood-color', 'rgba(255,107,53,0.22)');

        mainG = svg.append('g').attr('id', 'mainG');

        const nodeById = new Map(nodes.map(n => [n.id, n]));
        const childrenOf = new Map(), parentsOf = new Map(), spousesOf = new Map();

        edges.forEach(e => {
            const a = e.vyakti_a_id, b = e.vyakti_b_id, t = e.sambandh_prakar;
            if (t === 'pita' || t === 'mata') {
                if (!childrenOf.has(a)) childrenOf.set(a, []);
                if (!childrenOf.get(a).includes(b)) childrenOf.get(a).push(b);
                if (!parentsOf.has(b)) parentsOf.set(b, []);
                if (!parentsOf.get(b).includes(a)) parentsOf.get(b).push(a);
            }
            if (t === 'putra' || t === 'putri') {
                if (!childrenOf.has(a)) childrenOf.set(a, []);
                if (!childrenOf.get(a).includes(b)) childrenOf.get(a).push(b);
                if (!parentsOf.has(b)) parentsOf.set(b, []);
                if (!parentsOf.get(b).includes(a)) parentsOf.get(b).push(a);
            }
            if (t === 'pati' || t === 'patni') { spousesOf.set(a, b); spousesOf.set(b, a); }
        });

        // BFS generation assignment
        const generation = new Map();
        const visited = new Set();
        const allChildren = new Set([...parentsOf.keys()]);
        let roots = nodes.filter(n => !allChildren.has(n.id));
        if (!roots.length) roots = [nodes[0]];

        const queue = roots.map(r => ({ id: r.id, gen: 0 }));
        while (queue.length) {
            const { id, gen } = queue.shift();
            if (visited.has(id)) continue;
            visited.add(id); generation.set(id, gen);
            const spouse = spousesOf.get(id);
            if (spouse && !visited.has(spouse)) {
                generation.set(spouse, gen); visited.add(spouse);
                (childrenOf.get(spouse) || []).forEach(cid => { if (!visited.has(cid)) queue.push({ id: cid, gen: gen + 1 }); });
            }
            (childrenOf.get(id) || []).forEach(cid => { if (!visited.has(cid)) queue.push({ id: cid, gen: gen + 1 }); });
        }
        nodes.forEach(n => { if (!generation.has(n.id)) generation.set(n.id, 0); });

        // Subtree width calculation
        const subtreeWidth = new Map();
        function calcW(id, vis2 = new Set()) {
            if (vis2.has(id)) return CARD_W; vis2.add(id);
            const children = childrenOf.get(id) || [];
            const spouse = spousesOf.get(id);
            if (!children.length) { const w = spouse ? CARD_W * 2 + SPOUSE_GAP : CARD_W; subtreeWidth.set(id, w); return w; }
            let tw = 0; children.forEach(c => tw += (calcW(c, vis2) + H_GAP)); tw -= H_GAP;
            const mw = spouse ? CARD_W * 2 + SPOUSE_GAP : CARD_W;
            const w = Math.max(mw, tw); subtreeWidth.set(id, w); return w;
        }
        roots.forEach(r => calcW(r.id));

        // Position assignment
        const posX = new Map(), posY = new Map();
        function assignPos(id, cx, genY, vis2 = new Set()) {
            if (vis2.has(id)) return; vis2.add(id);
            const spouse = spousesOf.get(id);
            const y = genY + generation.get(id) * (CARD_H + V_GAP);
            if (spouse && !posX.has(id) && !posX.has(spouse)) {
                const nd = nodeById.get(id), sp = nodeById.get(spouse);
                let L = id, R = spouse;
                if (nd?.ling === 'stri') { L = spouse; R = id; }
                posX.set(L, cx - CARD_W / 2 - SPOUSE_GAP / 2); posX.set(R, cx + CARD_W / 2 + SPOUSE_GAP / 2);
                posY.set(L, y); posY.set(R, y); vis2.add(spouse);
            } else if (!posX.has(id)) { posX.set(id, cx); posY.set(id, y); }
            const children = childrenOf.get(id) || [];
            if (!children.length) return;
            let tw = 0; children.forEach(c => tw += (subtreeWidth.get(c) || CARD_W) + H_GAP); tw -= H_GAP;
            let xc = cx - tw / 2;
            children.forEach(c => {
                const cw = subtreeWidth.get(c) || CARD_W;
                assignPos(c, xc + cw / 2, genY, vis2);
                xc += cw + H_GAP;
            });
        }
        let rc = 0;
        roots.forEach(r => { const rw = subtreeWidth.get(r.id) || CARD_W; assignPos(r.id, rc + rw / 2, 60); rc += rw + H_GAP * 3; });
        nodes.forEach(n => { if (!posX.has(n.id)) { posX.set(n.id, rc + CARD_W / 2); posY.set(n.id, 60 + generation.get(n.id) * (CARD_H + V_GAP)); rc += CARD_W + H_GAP; } });

        // Generation labels
        const maxGen = Math.max(...generation.values());
        const glG = mainG.append('g');
        const genLabels = ['पूर्वज', 'प्रथम पीढ़ी', 'द्वितीय पीढ़ी', 'तृतीय पीढ़ी', 'चतुर्थ पीढ़ी'];
        for (let g = 0; g <= maxGen; g++) {
            const y = 60 + g * (CARD_H + V_GAP) + CARD_H / 2;
            glG.append('line')
                .attr('x1', -200).attr('y1', y).attr('x2', rc + 200).attr('y2', y)
                .attr('stroke', 'rgba(255,107,53,0.04)').attr('stroke-width', CARD_H + V_GAP);
            glG.append('text').attr('x', -24).attr('y', y)
                .attr('text-anchor', 'end').attr('dominant-baseline', 'middle')
                .attr('font-size', '9px').attr('font-weight', '700')
                .attr('fill', '#9BA4B5').attr('letter-spacing', '0.5px')
                .text((genLabels[g] || g + 'वीं पीढ़ी').toUpperCase());
        }

        // Draw edges
        const edgeG = mainG.append('g');
        const drawnSpouseEdges = new Set();
        edges.forEach(e => {
            const t = e.sambandh_prakar;
            let pId = null, cId = null;
            if (t === 'pita' || t === 'mata') { pId = e.vyakti_a_id; cId = e.vyakti_b_id; }
            else if (t === 'putra' || t === 'putri') { pId = e.vyakti_a_id; cId = e.vyakti_b_id; }

            if (pId && cId && posX.has(pId) && posX.has(cId)) {
                const px = posX.get(pId), py = posY.get(pId) + CARD_H;
                const cx = posX.get(cId), cy = posY.get(cId);
                const midY = (py + cy) / 2;
                const thickness = Math.max(1.5, 5 - (generation.get(cId) || 0) * 0.7);
                edgeG.append('path')
                    .attr('d', `M${px},${py} C${px},${midY} ${cx},${midY} ${cx},${cy}`)
                    .attr('fill', 'none').attr('stroke', 'url(#branchGrad)')
                    .attr('stroke-width', thickness).attr('stroke-linecap', 'round').attr('opacity', 0.7);
            }

            if ((t === 'pati' || t === 'patni') && posX.has(e.vyakti_a_id) && posX.has(e.vyakti_b_id)) {
                const key = [e.vyakti_a_id, e.vyakti_b_id].sort().join('-');
                if (drawnSpouseEdges.has(key)) return;
                drawnSpouseEdges.add(key);
                const ax = posX.get(e.vyakti_a_id), ay = posY.get(e.vyakti_a_id) + CARD_H / 2;
                const bx = posX.get(e.vyakti_b_id);
                const lx = Math.min(ax, bx) + CARD_W / 2, rx = Math.max(ax, bx) - CARD_W / 2;
                const mx = (lx + rx) / 2, myOff = ay - 12;
                edgeG.append('path')
                    .attr('d', `M${lx},${ay} Q${mx},${myOff} ${rx},${ay}`)
                    .attr('fill', 'none').attr('stroke', 'url(#spouseGrad)')
                    .attr('stroke-width', 1.5).attr('stroke-dasharray', '5,3').attr('opacity', 0.65);
                edgeG.append('text').attr('x', mx).attr('y', myOff - 5)
                    .attr('text-anchor', 'middle').attr('font-size', '9px').attr('opacity', '0.5').text('♥');
            }
        });

        // Draw nodes
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const tooltip = document.getElementById('tree-tooltip');
        const nodeG = mainG.append('g');

        nodes.forEach(n => {
            if (!posX.has(n.id)) return;
            const x = posX.get(n.id) - CARD_W / 2, y = posY.get(n.id);
            const dead = !n.jeevit || n.jeevit == 0;
            const female = n.ling === 'stri';
            const accent = dead ? '#8B4513' : (female ? '#C2185B' : '#1A6B3C');
            const cardBg = dead ? (isDark ? '#2D2015' : '#FDF3EB') : (female ? (isDark ? '#1E1328' : '#FDF0F6') : (isDark ? '#0F2318' : '#F0F9F4'));
            const textC = isDark ? '#F1F5F9' : '#1E293B';
            const subC = isDark ? '#94A3B8' : '#64748B';

            const fg = nodeG.append('g')
                .attr('transform', `translate(${x},${y})`)
                .style('cursor', 'pointer')
                .on('click', () => window.location.href = `vyakti.php?id=${n.id}`)
                .on('mouseover', (ev) => {
                    d3.select(ev.currentTarget).select('rect').attr('filter', 'url(#cardShadowHover)');
                    const parts = [];
                    if (n.birth_year) parts.push('जन्म: ' + n.birth_year);
                    if (n.gotra) parts.push('गोत्र: ' + n.gotra);
                    if (dead) parts.push('✝ दिवंगत');
                    tooltip.innerHTML = `<strong>${n.name || ''} ${n.kul_naam || ''}</strong>` + (parts.length ? '<br>' + parts.join(' · ') : '');
                    tooltip.style.display = 'block';
                })
                .on('mousemove', (ev) => { tooltip.style.left = (ev.clientX + 14) + 'px'; tooltip.style.top = (ev.clientY - 10) + 'px'; })
                .on('mouseout', (ev) => { d3.select(ev.currentTarget).select('rect').attr('filter', 'url(#cardShadow)'); tooltip.style.display = 'none'; });

            // Card rect
            fg.append('rect').attr('width', CARD_W).attr('height', CARD_H).attr('rx', 12)
                .attr('fill', cardBg).attr('stroke', accent + '33').attr('stroke-width', 1.5)
                .attr('filter', 'url(#cardShadow)');

            // Top accent strip
            fg.append('rect').attr('width', CARD_W).attr('height', 3).attr('rx', 2).attr('fill', accent).attr('opacity', 0.9);

            // Avatar circle
            const ax = CARD_W / 2, ay = 22;
            fg.append('circle').attr('cx', ax).attr('cy', ay).attr('r', 16)
                .attr('fill', accent).attr('opacity', dead ? 0.15 : 0.12);

            if (n.photo_url) {
                const imgId = 'img' + n.id;
                defs.append('clipPath').attr('id', imgId + 'c').append('circle').attr('cx', ax).attr('cy', ay).attr('r', 15);
                defs.append('pattern').attr('id', imgId).attr('patternUnits', 'userSpaceOnUse')
                    .attr('x', ax - 15).attr('y', ay - 15).attr('width', 30).attr('height', 30)
                    .append('image').attr('href', '/' + n.photo_url).attr('width', 30).attr('height', 30);
                fg.append('circle').attr('cx', ax).attr('cy', ay).attr('r', 15)
                    .attr('fill', `url(#${imgId})`).attr('clip-path', `url(#${imgId}c)`);
            } else {
                fg.append('text').attr('x', ax).attr('y', ay).attr('text-anchor', 'middle')
                    .attr('dominant-baseline', 'middle').attr('font-size', '13px').attr('font-weight', '700')
                    .attr('fill', accent).text(n.name ? n.name.charAt(0) : '?');
            }

            // Gender/death icon
            fg.append('text').attr('x', ax + 13).attr('y', ay - 11).attr('font-size', '9px')
                .attr('fill', accent).attr('opacity', 0.75).text(dead ? '✝' : (female ? '♀' : '♂'));

            // Name
            const nm = n.name || '';
            const nm2 = nm.length > 9 ? nm.slice(0, 9) + '…' : nm;
            fg.append('text').attr('x', CARD_W / 2).attr('y', CARD_H - 26)
                .attr('text-anchor', 'middle').attr('font-size', '12px').attr('font-weight', '700')
                .attr('fill', textC).attr('font-family', 'Noto Sans Devanagari,Outfit,sans-serif').text(nm2);

            // Birth year sub-label
            const sub = n.birth_year ? String(n.birth_year) : (n.kul_naam || '');
            if (sub) fg.append('text').attr('x', CARD_W / 2).attr('y', CARD_H - 12)
                .attr('text-anchor', 'middle').attr('font-size', '9px').attr('fill', subC).text(sub);

            // Cross-family gold ring
            if (n.other_parivars?.length > 0)
                fg.append('circle').attr('cx', ax).attr('cy', ay).attr('r', 17)
                    .attr('fill', 'none').attr('stroke', '#FFD700').attr('stroke-width', 2)
                    .style('filter', 'drop-shadow(0 0 4px rgba(255,215,0,0.5))');
        });

        // Zoom setup + auto-fit
        zoomBehavior = d3.zoom().scaleExtent([0.08, 3]).on('zoom', (ev) => mainG.attr('transform', ev.transform));
        svg.call(zoomBehavior);

        const allX = [...posX.values()], allY = [...posY.values()];
        const minX = Math.min(...allX) - CARD_W, maxX = Math.max(...allX) + CARD_W;
        const minY = Math.min(...allY) - 40, maxY = Math.max(...allY) + CARD_H + 40;
        const tW = maxX - minX, tH = maxY - minY;
        const scale = Math.min(W / tW, H / tH, 1.2) * 0.88;
        const tx = (W - tW * scale) / 2 - minX * scale;
        const ty = (H - tH * scale) / 2 - minY * scale + 20;
        svg.call(zoomBehavior.transform, d3.zoomIdentity.translate(tx, ty).scale(scale));
    }

    function zoomIn() { svg.transition().duration(300).call(zoomBehavior.scaleBy, 1.4); }
    function zoomOut() { svg.transition().duration(300).call(zoomBehavior.scaleBy, 0.72); }
    function resetView() { if (treeData) buildTree(treeData); }
    window.addEventListener('resize', () => { if (treeData) buildTree(treeData); });
</script>

<?php include '../includes/nav.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>