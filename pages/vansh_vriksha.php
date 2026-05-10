<?php
/**
 * वंश वृक्ष — SVG आधारित वंशावली दृश्य
 */
require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2>वंश वृक्ष (Family Tree)</h2>
        <div>
            <button onclick="exportTree()" class="btn-secondary" style="width: auto; padding: 0.5rem 1rem;">
                <i class="fa fa-download"></i> PNG में सेव करें
            </button>
        </div>
    </div>
    
    <div id="tree-container" style="border: 1px solid var(--rang-seemant); background: #fafafa; cursor: grab;">
        <!-- SVG will be rendered here via app.js -->
        <svg id="family-tree-svg" width="100%" height="100%">
            <g id="tree-g"></g>
        </svg>
    </div>
    
    <div style="margin-top: 1rem; font-size: 0.9rem; color: #888;">
        <p><i class="fa fa-info-circle"></i> माउस से खींचकर या ज़ूम करके देख सकते हैं। किसी व्यक्ति पर क्लिक करके उसकी पूरी जानकारी देखें।</p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fetch family data and render tree
        fetch('/api/vyakti.php?action=tree_data')
            .then(r => r.json())
            .then(res => {
                if (res.safalta) {
                    renderFamilyTree(res.data);
                }
            });
    });

    function exportTree() {
        // Now handled by app.js
        if (typeof window.exportTree === 'function') {
            window.exportTree();
        } else {
            alert('निर्यात सुविधा लोड हो रही है...');
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
