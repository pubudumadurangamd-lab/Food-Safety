<?php
/**
 * Sidebar Partial - Dashboard Navigation
 */

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$role = $_SESSION['user_role'] ?? '';

$menuItems = [];

// Super Admin Menu
if ($role === 'super_admin') {
    $menuItems = [
        ['dashboard', 'bi-speedometer2', 'Dashboard'],
        ['users', 'bi-people', 'Manage Users'],
        ['premises', 'bi-shop', 'All Premises'],
        ['inspections', 'bi-clipboard-check', 'Inspections'],
        ['complaints', 'bi-exclamation-triangle', 'Complaints'],
        ['workers', 'bi-person-workspace', 'Workers'],
        ['medical-certificates', 'bi-file-medical', 'Medical Certificates'],
        ['reports', 'bi-graph-up', 'Reports'],
        ['activity-logs', 'bi-clock-history', 'Activity Logs'],
        ['settings', 'bi-gear', 'Settings']
    ];
}
// Inspector Menu
elseif ($role === 'inspector') {
    $menuItems = [
        ['dashboard', 'bi-speedometer2', 'Dashboard'],
        ['premises', 'bi-shop', 'My Premises'],
        ['new-inspection', 'bi-clipboard-plus', 'New Inspection'],
        ['inspections', 'bi-clipboard-check', 'Inspection History'],
        ['complaints', 'bi-exclamation-triangle', 'Complaints'],
        ['workers', 'bi-person-workspace', 'Workers'],
        ['medical-certificates', 'bi-file-medical', 'Medical Certificates'],
        ['reports', 'bi-graph-up', 'My Reports']
    ];
}
// Business Owner Menu
elseif ($role === 'business_owner') {
    $menuItems = [
        ['dashboard', 'bi-speedometer2', 'Dashboard'],
        ['my-premises', 'bi-shop', 'My Premises'],
        ['inspections', 'bi-clipboard-check', 'Inspections'],
        ['complaints', 'bi-exclamation-triangle', 'Complaints'],
        ['workers', 'bi-person-workspace', 'My Workers'],
        ['profile', 'bi-person', 'Profile']
    ];
}
// Worker Menu
elseif ($role === 'worker') {
    $menuItems = [
        ['dashboard', 'bi-speedometer2', 'Dashboard'],
        ['my-certificates', 'bi-file-medical', 'My Certificates'],
        ['profile', 'bi-person', 'Profile']
    ];
}
?>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo BASE_URL; ?>" class="sidebar-brand">
            <i class="bi bi-shield-check fs-4"></i>
            <span><?php echo SITE_SHORT; ?></span>
        </a>
    </div>

    <div class="sidebar-menu">
        <nav class="nav flex-column">
            <?php foreach ($menuItems as $item): ?>
                <a href="<?php echo $item[0]; ?>.php" 
                   class="nav-link <?php echo $currentPage === $item[0] ? 'active' : ''; ?>">
                    <i class="bi <?php echo $item[1]; ?>"></i>
                    <span><?php echo $item[2]; ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <div class="sidebar-footer">
        <a href="<?php echo BASE_URL; ?>logout.php" class="nav-link text-danger">
            <i class="bi bi-box-arrow-left"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>
