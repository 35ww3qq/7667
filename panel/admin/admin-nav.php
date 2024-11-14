<?php
if (!defined('ADMIN_NAV_LOADED')) {
    define('ADMIN_NAV_LOADED', true);
    
    $current_page = $current_page ?? '';
    $username = get_username();
    ?>
    <nav class="bg-white shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <img class="h-8 w-auto" src="../../assets/logo.svg" alt="Logo">
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="../admin/dashboard.php" class="<?php echo $current_page === 'dashboard' ? 'border-blue-500' : 'border-transparent'; ?> text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>
                        <a href="../admin/users.php" class="<?php echo $current_page === 'users' ? 'border-blue-500' : 'border-transparent'; ?> text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Kullanıcılar
                        </a>
                        <a href="../admin/sites.php" class="<?php echo $current_page === 'sites' ? 'border-blue-500' : 'border-transparent'; ?> text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Siteler
                        </a>
                        <a href="../admin/settings.php" class="<?php echo $current_page === 'settings' ? 'border-blue-500' : 'border-transparent'; ?> text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Ayarlar
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-700"><?php echo htmlspecialchars($username); ?></span>
                            <a href="../../logout.php" class="text-gray-700 hover:text-gray-900">
                                <i class="fas fa-sign-out-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <?php
}
?>