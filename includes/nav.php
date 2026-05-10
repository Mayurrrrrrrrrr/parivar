<?php
/**
 * Navigation Bar — सभी pages में include करें
 */
requireLogin();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="bottom-nav">
    <a href="/parivar/pages/dashboard.php" class="nav-item <?= $currentPage==='dashboard'?'active':'' ?>">
        <i class="ti ti-home"></i><span>मुख्य</span>
    </a>
    <a href="/parivar/pages/vansh_vriksha.php" class="nav-item <?= $currentPage==='vansh_vriksha'?'active':'' ?>">
        <i class="ti ti-tree"></i><span>वंश</span>
    </a>
    <a href="/parivar/pages/karyakram.php" class="nav-item <?= $currentPage==='karyakram'?'active':'' ?>">
        <i class="ti ti-calendar-event"></i><span>कार्यक्रम</span>
    </a>
    <a href="/parivar/pages/parivar_feed.php" class="nav-item <?= $currentPage==='parivar_feed'?'active':'' ?>">
        <i class="ti ti-speakerphone"></i><span>फ़ीड</span>
    </a>
    <a href="/parivar/pages/settings.php" class="nav-item <?= $currentPage==='settings'?'active':'' ?>">
        <i class="ti ti-settings"></i><span>सेटिंग्स</span>
    </a>
</nav>
