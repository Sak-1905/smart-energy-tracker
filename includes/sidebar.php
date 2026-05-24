<?php
$activePage = $activePage ?? '';
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-mark">⚡</div>
        <div>
            <p>Smart Energy</p>
            <strong>Tracker</strong>
        </div>
    </div>
    <ul>
        <li><a href="dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
        <li><a href="add_usage.php" class="<?= $activePage === 'add' ? 'active' : '' ?>">Add Data</a></li>
        <li><a href="reports.php" class="<?= $activePage === 'reports' ? 'active' : '' ?>">Reports</a></li>
        <li><a href="tips.php" class="<?= $activePage === 'tips' ? 'active' : '' ?>">Tips</a></li>
    </ul>
</aside>
