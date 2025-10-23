<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
?>
<div class="sidebar">
    <div class="sidebar-brand">
        <h2>OUTSINC</h2>
        <p>Outreach System</p>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="/modules/dashboard/index.php" class="<?php echo $currentDir === 'dashboard' ? 'active' : ''; ?>">
                <span class="icon">📊</span>
                <span>Dashboard</span>
            </a>
        </li>
        
        <li>
            <a href="/modules/clients/list.php" class="<?php echo $currentDir === 'clients' ? 'active' : ''; ?>">
                <span class="icon">👥</span>
                <span>Clients</span>
            </a>
        </li>
        
        <li>
            <a href="/modules/assessment/list.php" class="<?php echo $currentDir === 'assessment' ? 'active' : ''; ?>">
                <span class="icon">📋</span>
                <span>Assessments</span>
            </a>
        </li>
        
        <li>
            <a href="/modules/resources/list.php" class="<?php echo $currentDir === 'resources' ? 'active' : ''; ?>">
                <span class="icon">🏢</span>
                <span>Resources</span>
            </a>
        </li>
        
        <?php if (hasRole('admin')): ?>
        <li>
            <a href="/admin/index.php">
                <span class="icon">⚙️</span>
                <span>Admin Panel</span>
            </a>
        </li>
        <?php endif; ?>
        
        <li style="margin-top: 30px;">
            <a href="/modules/auth/logout.php">
                <span class="icon">🚪</span>
                <span>Logout</span>
            </a>
        </li>
    </ul>
    
    <div style="padding: 20px 30px; border-top: 1px solid rgba(255,255,255,0.1); margin-top: 30px;">
        <p style="font-size: 12px; opacity: 0.7;">Logged in as:</p>
        <p style="font-size: 14px; font-weight: 600;"><?php echo htmlspecialchars(getCurrentUserName()); ?></p>
        <p style="font-size: 12px; opacity: 0.7;"><?php echo getRoleBadge(getCurrentUserRole()); ?></p>
    </div>
</div>
