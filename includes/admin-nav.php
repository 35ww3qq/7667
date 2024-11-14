<?php
if (!defined('ADMIN_NAV_LOADED')) {
    define('ADMIN_NAV_LOADED', true);
    
    if (!function_exists('get_username')) {
        require_once __DIR__ . '/auth.php';
    }
    
    $current_page = $current_page ?? '';
    $username = get_username();
    ?>
    <nav class="bg-white shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="dashboard.php" class="text-lg font-bold text-gray-800">
                            <?php echo SITE_NAME; ?> - Admin
                        </a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="dashboard.php" class="border-b-2 border-blue-500 text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium">
                            Dashboard
                        </a>
                        <a href="users.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Kullanıcılar
                        </a>
                        <a href="sites.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Siteler
                        </a>
                        <a href="settings.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Ayarlar
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="ml-3">
                        <a href="../../logout.php" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-sign-out-alt text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <?php
}
?>