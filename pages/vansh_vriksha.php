<?php
/**
 * वंश वृक्ष — SVG Tree Visualization using D3.js
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2>वंश वृक्ष</h2>
        <div>
            <button onclick="exportTree()" class="btn-secondary" style="width: auto;">पिक्चर सेव करें</button>
        </div>
    </div>
    
    <div id="tree-container">
        <!-- D3.js Tree goes here -->
    </div>
</div>

<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('/api/vyakti.php?action=tree')
            .then(res => res.json())
            .then(data => {
                if (data.safalta) {
                    renderTree(data.data);
                } else {
                    alert('डेटा लोड करने में विफल: ' + data.sandesh);
                }
            });
    });

    function renderTree(data) {
        const width = document.getElementById('tree-container').clientWidth;
        const height = 600;

        // Transform data for D3 (simple horizontal layout)
        // Here we'd typically use d3.stratify if we had a parent_id
        // For this demo, let's assume nodes and links structure
        
        const svg = d3.select("#tree-container").append("svg")
            .attr("width", width)
            .attr("height", height)
            .append("g")
            .attr("transform", "translate(50, 50)");

        const simulation = d3.forceSimulation(data.nodes)
            .force("link", d3.forceLink(data.edges).id(d => d.id).distance(150))
            .force("charge", d3.forceManyBody().strength(-300))
            .force("center", d3.forceCenter(width / 2, height / 2));

        const link = svg.append("g")
            .attr("stroke", "#999")
            .attr("stroke-opacity", 0.6)
            .selectAll("line")
            .data(data.edges)
            .join("line")
            .attr("stroke-width", d => d.sambandh_prakar === 'pati' || d.sambandh_prakar === 'patni' ? 3 : 1);

        const node = svg.append("g")
            .attr("stroke", "#fff")
            .attr("stroke-width", 1.5)
            .selectAll("g")
            .data(data.nodes)
            .join("g")
            .call(d3.drag()
                .on("start", dragstarted)
                .on("drag", dragged)
                .on("end", dragended))
            .on("click", (event, d) => {
                window.location.href = `/pages/vyakti.php?id=${d.id}`;
            });

        node.append("circle")
            .attr("r", 30)
            .attr("fill", d => d.jeevit ? "var(--rang-pramukh)" : "#888");

        node.append("text")
            .attr("text-anchor", "middle")
            .attr("dy", ".35em")
            .attr("fill", "white")
            .attr("font-size", "10px")
            .attr("stroke", "none")
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

        function dragstarted(event) {
            if (!event.active) simulation.alphaTarget(0.3).restart();
            event.subject.fx = event.subject.x;
            event.subject.fy = event.subject.y;
        }

        function dragged(event) {
            event.subject.fx = event.x;
            event.subject.fy = event.y;
        }

        function dragended(event) {
            if (!event.active) simulation.alphaTarget(0);
            event.subject.fx = null;
            event.subject.fy = null;
        }
    }

    function exportTree() {
        alert('निर्यात सुविधा अगले अपडेट में आएगी।');
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
