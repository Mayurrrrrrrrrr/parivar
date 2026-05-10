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
        fetch('/parivar/api/vyakti.php?action=tree')
            .then(r => r.json())
            .then(data => {
                if (data.safalta) {
                    initTree(data.data);
                }
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

        // Force simulation for a flexible tree layout
        const simulation = d3.forceSimulation(nodes)
            .force("link", d3.forceLink(links).id(d => d.id).distance(120))
            .force("charge", d3.forceManyBody().strength(-400))
            .force("center", d3.forceCenter(width / 2, height / 2));

        const link = g.append("g")
            .attr("stroke", "var(--seemant-strong)")
            .attr("stroke-width", 1.5)
            .selectAll("line")
            .data(links)
            .join("line");

        const node = g.append("g")
            .selectAll("g")
            .data(nodes)
            .join("g")
            .style("cursor", "pointer")
            .on("click", (event, d) => {
                window.location.href = `vyakti.php?id=${d.id}`;
            });

        node.append("circle")
            .attr("r", 22)
            .attr("fill", d => d.jeevit ? "var(--rang-pramukh)" : "#888")
            .attr("stroke", "white")
            .attr("stroke-width", 2);

        node.append("text")
            .attr("text-anchor", "middle")
            .attr("dy", "38px")
            .attr("fill", "var(--text-primary)")
            .attr("font-size", "12px")
            .attr("font-weight", "500")
            .text(d => d.name);

        simulation.on("tick", () => {
            link
                .attr("x1", d => d.source.x)
                .attr("y1", d => d.source.y)
                .attr("x2", d => d.target.x)
                .attr("y2", d => d.target.y);

            node
                .attr("transform", d => `translate(${d.x},${d.y})`);
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
    #tree-canvas { background: var(--bg-page); height: calc(100vh - 160px); }
</style>

<?php include '../includes/nav.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
