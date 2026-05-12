<?php
/**
 * Header Partial - Dashboard Top Bar
 */
?>

<!-- Top Header -->
<header class="top-header">
    <div class="d-flex align-items-center">
        <button class="btn btn-link text-dark sidebar-toggle d-lg-none me-3" onclick="toggleSidebar()">
            <i class="bi bi-list fs-4"></i>
        </button>
        <h1 class="header-title"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
    </div>

    <div class="header-actions">
        <button class="btn btn-link text-dark" onclick="toggleDarkMode()" title="Toggle Dark Mode">
            <i class="bi bi-moon-stars fs-5"></i>
        </button>

        <div class="dropdown user-dropdown">
            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
                </div>
                <span class="d-none d-md-inline"><?php echo $_SESSION['user_name'] ?? 'User'; ?></span>
                <i class="bi bi-chevron-down small"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout.php">
                    <i class="bi bi-box-arrow-left me-2"></i>Logout</a>
                </li>
            </ul>
        </div>
    </div>
</header>
