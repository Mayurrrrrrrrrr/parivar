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
        const urlParams = new URLSearchParams(window.location.search);
        const pid = urlParams.get('parivar_id') || '';
        fetch(`../api/vyakti.php?action=tree&parivar_id=${pid}`)
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
            .scaleExtent([0.1, 4])
            .on("zoom", (event) => {
                g.attr("transform", event.transform);
            });

        svg.call(zoom);
        
        // Initial zoom for mobile readability
        const initialScale = window.innerWidth < 600 ? 1.2 : 1;
        svg.call(zoom.transform, d3.zoomIdentity.translate(0, 0).scale(initialScale));

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

        // 2. Botanical Physics Engine
        const maxGen = d3.max(nodes, d => d.generation) || 0;
        
        // Custom forces for a tree-like shape
        const simulation = d3.forceSimulation(nodes)
            .force("link", d3.forceLink(validLinks).id(d => d.id).distance(80))
            .force("charge", d3.forceManyBody().strength(-400)) // Repel nodes
            .force("collide", d3.forceCollide().radius(25).iterations(2)) // Prevent overlap
            .force("x", d3.forceX(width / 2).strength(0.02)) // Very gentle pull to center
            .force("y", d3.forceY(d => {
                // Root is at the bottom, higher generations grow upwards
                return height - 100 - (d.generation * 130);
            }).strength(1)); // Strict Y anchoring

        // Map relation types to colors/styles
        function getLinkStyle(d) {
            const type = d.sambandh_prakar;
            if (type === 'pati' || type === 'patni') {
                return { stroke: '#FF9F1C', dash: '4,4', width: 2, type: 'vine' };
            }
            
            // Branch thickness based on generation (older = thicker)
            const minGen = Math.min(d.source.generation || 0, d.target.generation || 0);
            const thickness = Math.max(2, 16 - (minGen * 2.5));
            return { stroke: 'url(#branchGradient)', dash: '0', width: thickness, type: 'branch' };
        }

        // Add SVG definitions for gradients & leaf paths
        const defs = svg.append("defs");
        const gradient = defs.append("linearGradient")
            .attr("id", "branchGradient")
            .attr("x1", "0%").attr("y1", "100%") // Bottom (Root)
            .attr("x2", "0%").attr("y2", "0%");  // Top (Leaves)
        gradient.append("stop").attr("offset", "0%").attr("stop-color", "#5C4033"); // Dark Wood
        gradient.append("stop").attr("offset", "100%").attr("stop-color", "#6B8E23"); // Olive Green

        const link = g.append("g")
            .selectAll("path")
            .data(validLinks)
            .join("path")
            .attr("fill", "none")
            .attr("stroke", d => getLinkStyle(d).stroke)
            .attr("stroke-width", d => getLinkStyle(d).width)
            .attr("stroke-dasharray", d => getLinkStyle(d).dash)
            .style("opacity", 0.85);

        const node = g.append("g")
            .selectAll("g")
            .data(nodes)
            .join("g")
            .style("cursor", "pointer")
            .on("click", (event, d) => {
                if (d.other_parivars && d.other_parivars.length > 0) {
                    if (confirm(`यह सदस्य "${d.other_parivars[0].naam}" से भी जुड़ा है।\nक्या आप वह परिवार वृक्ष देखना चाहते हैं?\n(Cancel दबाने पर प्रोफ़ाइल खुलेगी)`)) {
                        window.location.href = `vansh_vriksha.php?parivar_id=${d.other_parivars[0].id}`;
                        return;
                    }
                }
                window.location.href = `vyakti.php?id=${d.id}`;
            });

        // Draw leaf paths instead of circles
        node.append("path")
            .attr("d", d => {
                // If trunk (generation 0), draw a root base
                if (d.generation === 0) {
                    return "M-25,25 Q0,-25 25,25 Z"; 
                }
                // Standard leaf: stem at bottom, tip at top
                return "M0,20 C-25,5 -20,-20 0,-35 C20,-20 25,5 0,20 Z";
            })
            .attr("fill", d => {
                if (!d.jeevit) return "#D2691E"; // Autumn Orange for deceased
                return d.ling === 'stri' ? "#9ACD32" : "#228B22"; // Yellow-Green for female, Forest Green for male
            })
            .attr("stroke", d => (d.other_parivars && d.other_parivars.length > 0) ? "#FFD700" : "#ffffff")
            .attr("stroke-width", d => (d.other_parivars && d.other_parivars.length > 0) ? 3 : 1.5)
            .style("filter", d => (d.other_parivars && d.other_parivars.length > 0) ? "drop-shadow(0 0 8px rgba(255,215,0,0.8))" : "drop-shadow(0 2px 4px rgba(0,0,0,0.2))");

        node.append("text")
            .attr("text-anchor", "middle")
            .attr("dy", "38px")
            .attr("fill", "var(--text-primary)")
            .attr("font-size", "14px")
            .attr("font-weight", "800")
            .style("text-shadow", "0 1px 3px rgba(255,255,255,1), 0 0 5px rgba(255,255,255,0.8)")
            .text(d => d.name);

        simulation.on("tick", () => {
            // Anchor roots to exactly horizontal center bottom
            nodes.forEach(d => {
                if (d.generation === 0) {
                    d.fy = height - 50;
                }
            });

            link.attr("d", d => {
                const sourceX = d.source ? d.source.x : 0;
                const sourceY = d.source ? d.source.y : 0;
                const targetX = d.target ? d.target.x : 0;
                const targetY = d.target ? d.target.y : 0;

                // Mycelium vine for spouses (horizontal intertwining)
                if (d.sambandh_prakar === 'pati' || d.sambandh_prakar === 'patni') {
                    return `M${sourceX},${sourceY} Q${(sourceX+targetX)/2},${sourceY-30} ${targetX},${targetY}`;
                }

                // Botanical branch (curved upward)
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
