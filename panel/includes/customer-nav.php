<?php
// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    header('Location: ../../login.php');
    exit;
}

// Check for authenticated user
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

// Get user credits
$user_credits = get_user_credits($_SESSION['user_id']);

// Set active page if not set
$current_page = $current_page ?? '';

// Start nav output
?>
<nav class="bg-white shadow-sm">
    <div class="container mx-auto px-4">
        <div class="flex justify-between h-16">
            <!-- Logo and Main Navigation -->
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="dashboard.php">
                        <img class="h-8 w-auto" src="../../assets/logo.svg" alt="Logo">
                    </a>
                </div>
                <!-- Desktop Navigation -->
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <a href="dashboard.php" 
                       class="<?php echo $current_page === 'dashboard' ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> 
                              inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        Dashboard
                    </a>
                    <a href="sites.php" 
                       class="<?php echo $current_page === 'sites' ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> 
                              inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        Siteler
                    </a>
                    <a href="backlinks.php" 
                       class="<?php echo $current_page === 'backlinks' ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> 
                              inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        Backlinkler
                    </a>
                    <a href="credits.php" 
                       class="<?php echo $current_page === 'credits' ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> 
                              inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        Krediler
                    </a>
                    <a href="profile.php" 
                       class="<?php echo $current_page === 'profile' ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> 
                              inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        Profil
                    </a>
                </div>
            </div>

            <!-- Right side - User info and logout -->
            <div class="flex items-center">
                <div class="ml-3 relative">
                    <div class="flex items-center space-x-4">
                        <!-- Credits Display -->
                        <span class="text-gray-700">
                            <i class="fas fa-coins text-yellow-500 mr-1"></i>
                            Kredi: <span class="font-medium"><?php echo number_format($user_credits); ?></span>
                        </span>
                        <!-- Logout Button -->
                        <a href="../../logout.php" 
                           class="text-gray-700 hover:text-gray-900 flex items-center"
                           title="Çıkış Yap">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div class="sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
                <a href="dashboard.php" 
                   class="<?php echo $current_page === 'dashboard' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-800'; ?> 
                          block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Dashboard
                </a>
                <a href="sites.php" 
                   class="<?php echo $current_page === 'sites' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-800'; ?> 
                          block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Siteler
                </a>
                <a href="backlinks.php" 
                   class="<?php echo $current_page === 'backlinks' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-800'; ?> 
                          block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Backlinkler
                </a>
                <a href="credits.php" 
                   class="<?php echo $current_page === 'credits' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-800'; ?> 
                          block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Krediler
                </a>
                <a href="profile.php" 
                   class="<?php echo $current_page === 'profile' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-800'; ?> 
                          block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Profil
                </a>
            </div>
        </div>
    </div>
</nav>
<?php
// Add mobile menu toggle script
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenu = document.querySelector('.sm\\:hidden');
    const mobileMenuButton = document.createElement('button');
    mobileMenuButton.className = 'md:hidden px-4 py-2 text-gray-500 hover:text-gray-900';
    mobileMenuButton.innerHTML = '<i class="fas fa-bars"></i>';
    mobileMenuButton.addEventListener('click', function() {
        mobileMenu.classList.toggle('hidden');
    });
    document.querySelector('.flex-shrink-0').appendChild(mobileMenuButton);
    mobileMenu.classList.add('hidden');
});
</script>