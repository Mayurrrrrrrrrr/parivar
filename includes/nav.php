<?php
/**
 * Navigation Bar — सभी pages में include करें
 */
requireLogin();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="bottom-nav">
    <a href="/pages/dashboard.php?v=<?php echo time(); ?>" class="nav-item <?= $currentPage==='dashboard'?'active':'' ?>">
        <i class="ti ti-smart-home"></i><span>मुख्य</span>
    </a>
    <a href="/pages/vansh_vriksha.php" class="nav-item <?= $currentPage==='vansh_vriksha'?'active':'' ?>">
        <i class="ti ti-hierarchy-2"></i><span>वंश</span>
    </a>
    <a href="/pages/karyakram.php" class="nav-item <?= $currentPage==='karyakram'?'active':'' ?>">
        <i class="ti ti-calendar-event"></i><span>कार्यक्रम</span>
    </a>
    <a href="/pages/parivar_feed.php" class="nav-item <?= $currentPage==='parivar_feed'?'active':'' ?>">
        <i class="ti ti-message-2-share"></i><span>फ़ीड</span>
    </a>
    <a href="/pages/settings.php" class="nav-item <?= $currentPage==='settings'?'active':'' ?>">
        <i class="ti ti-settings"></i><span>सेटिंग्स</span>
    </a>
</nav>
