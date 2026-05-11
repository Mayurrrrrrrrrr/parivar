<?php
/**
 * वंश वृक्ष (v2.0) — D3.js Tree
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();
?>

<header class="app-header">
    <h1>🌳 वंश वृक्ष</h1>
</header>

<div class="page-content" style="max-width: 100%; padding: 0;">
    <div class="tree-container" id="tree-canvas">
        <!-- SVG rendering area -->
    </div>
    
    <div class="tree-controls" style="padding: 0 16px;">
        <button class="tree-btn active" onclick="resetZoom()">केंद्र में लाएं</button>
        <button class="tree-btn" onclick="saveImage()">पिक्चर सेव करें</button>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.8.5/d3.min.js"></script>
<script>
    let svg, g, zoom;

    document.addEventListener('DOMContentLoaded', function() {
        fetch('../api/vyakti.php?action=tree')
            .then(r => r.json())
            .then(data => {
                if (data.safalta && data.data && data.data.nodes && data.data.nodes.length > 0) {
                    initTree(data.data);
                } else {
                    document.getElementById('tree-canvas').innerHTML = '<p style="padding: 20px; text-align: center;">वंश वृक्ष में अभी कोई सदस्य नहीं है।</p>';
                }
            })
            .catch(e => {
                console.error('Error fetching tree data:', e);
                document.getElementById('tree-canvas').innerHTML = '<p style="padding: 20px; text-align: center; color: red;">डेटा लोड करने में समस्या आई।</p>';
            });
    });

    function initTree(data) {
        const width = window.innerWidth;
        const height = window.innerHeight - 150;

        svg = d3.select("#tree-canvas").append("svg")
            .attr("width", width)
            .attr("height", height);

        g = svg.append("g");

        zoom = d3.zoom()
            .scaleExtent([0.1, 3])
            .on("zoom", (event) => {
                g.attr("transform", event.transform);
            });

        svg.call(zoom);

        const nodes = data.nodes;
        const links = data.edges;

        const nodeById = new Map(nodes.map(d => [d.id, d]));
        links.forEach(l => {
            l.source = nodeById.get(l.vyakti_a_id);
            l.target = nodeById.get(l.vyakti_b_id);
        });
        const validLinks = links.filter(l => l.source && l.target);

        // 1. Calculate Generations
        nodes.forEach(d => d.generation = null);
        
        let normalizedEdges = [];
        validLinks.forEach(l => {
            if (l.sambandh_prakar === 'pita' || l.sambandh_prakar === 'mata') {
                normalizedEdges.push({parent: l.source.id, child: l.target.id});
            } else if (l.sambandh_prakar === 'putra' || l.sambandh_prakar === 'putri') {
                normalizedEdges.push({parent: l.target.id, child: l.source.id});
            }
        });

        let hasParent = new Set(normalizedEdges.map(e => e.child));
        let roots = nodes.filter(n => !hasParent.has(n.id));
        if (roots.length === 0 && nodes.length > 0) roots = [nodes[0]];

        let queue = roots.map(r => { r.generation = 0; return r; });
        while(queue.length > 0) {
            let curr = queue.shift();
            
            // Children
            let childrenIds = normalizedEdges.filter(e => e.parent === curr.id).map(e => e.child);
            childrenIds.forEach(cid => {
                let child = nodeById.get(cid);
                if (child && child.generation === null) {
                    child.generation = curr.generation + 1;
                    queue.push(child);
                }
            });
            
            // Spouses
            let spouseLinks = validLinks.filter(l => 
                (l.source.id === curr.id || l.target.id === curr.id) && 
                (l.sambandh_prakar === 'pati' || l.sambandh_prakar === 'patni')
            );
            spouseLinks.forEach(l => {
                let spouseId = l.source.id === curr.id ? l.target.id : l.source.id;
                let spouse = nodeById.get(spouseId);
                if (spouse && spouse.generation === null) {
                    spouse.generation = curr.generation;
                    queue.push(spouse);
                }
            });
        }
        
        nodes.forEach(d => { if (d.generation === null) d.generation = 0; });

        // 2. Force simulation with Y constraint based on generation
        const maxGen = d3.max(nodes, d => d.generation) || 0;
        
        const simulation = d3.forceSimulation(nodes)
            .force("link", d3.forceLink(validLinks).id(d => d.id).distance(100))
            .force("charge", d3.forceManyBody().strength(-800))
            .force("x", d3.forceX(width / 2).strength(0.05))
            .force("y", d3.forceY(d => height - 100 - (d.generation * 160)).strength(1)); // Base at bottom, grows UP

        // Map relation types to colors/styles
        function getLinkStyle(type) {
            if (type === 'pati' || type === 'patni') return { stroke: '#FF9F1C', dash: '3,3', width: 2.5, type: 'vine' };
            return { stroke: 'url(#branchGradient)', dash: '0', width: 4, type: 'branch' };
        }

        // Add SVG definitions for gradients
        const defs = svg.append("defs");
        const gradient = defs.append("linearGradient")
            .attr("id", "branchGradient")
            .attr("x1", "0%").attr("y1", "100%") // Bottom (Root)
            .attr("x2", "0%").attr("y2", "0%");  // Top (Leaves)
        gradient.append("stop").attr("offset", "0%").attr("stop-color", "#8B5A2B"); // Brown Trunk
        gradient.append("stop").attr("offset", "100%").attr("stop-color", "#4CAF50"); // Green Branches

        const link = g.append("g")
            .selectAll("path")
            .data(validLinks)
            .join("path")
            .attr("fill", "none")
            .attr("stroke", d => getLinkStyle(d.sambandh_prakar).stroke)
            .attr("stroke-width", d => getLinkStyle(d.sambandh_prakar).width)
            .attr("stroke-dasharray", d => getLinkStyle(d.sambandh_prakar).dash)
            .style("opacity", 0.8);

        const node = g.append("g")
            .selectAll("g")
            .data(nodes)
            .join("g")
            .style("cursor", "pointer")
            .on("click", (event, d) => {
                window.location.href = `vyakti.php?id=${d.id}`;
            });

        node.append("circle")
            .attr("r", 24)
            .attr("fill", d => {
                if (!d.jeevit) return "#888";
                return d.ling === 'stri' ? "#e83e8c" : "var(--rang-pramukh)";
            })
            .attr("stroke", "white")
            .attr("stroke-width", 2)
            .style("filter", "drop-shadow(0 4px 6px rgba(0,0,0,0.1))");

        node.append("text")
            .attr("text-anchor", "middle")
            .attr("dy", "42px")
            .attr("fill", "var(--text-primary)")
            .attr("font-size", "13px")
            .attr("font-weight", "600")
            .text(d => d.name);

        simulation.on("tick", () => {
            link.attr("d", d => {
                const sourceX = d.source ? d.source.x : 0;
                const sourceY = d.source ? d.source.y : 0;
                const targetX = d.target ? d.target.x : 0;
                const targetY = d.target ? d.target.y : 0;

                // Mycelium vine for spouses
                if (d.sambandh_prakar === 'pati' || d.sambandh_prakar === 'patni') {
                    return `M${sourceX},${sourceY} Q${(sourceX+targetX)/2},${sourceY-40} ${targetX},${targetY}`;
                }

                // Vertical organic branches for parent/child
                return `M${sourceX},${sourceY} C${sourceX},${(sourceY+targetY)/2} ${targetX},${(sourceY+targetY)/2} ${targetX},${targetY}`;
            });

            node.attr("transform", d => `translate(${d.x || 0},${d.y || 0})`);
        });
    }

    function resetZoom() {
        svg.transition().duration(750).call(zoom.transform, d3.zoomIdentity);
    }

    function saveImage() {
        alert('इमेज सेविंग फीचर मोबाइल ब्राउज़र में सीमित हो सकता है।');
    }
</script>

<style>
    #tree-canvas { 
        background: radial-gradient(circle at bottom, #F9FBE7 0%, var(--bg-page) 70%);
        height: calc(100vh - 160px); 
    }
    [data-theme="dark"] #tree-canvas {
        background: radial-gradient(circle at bottom, #1B2E1E 0%, var(--bg-page) 70%);
    }
</style>

<?php include '../includes/nav.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
