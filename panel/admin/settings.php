<?php
require_once '../../includes/init.php';
require_once '../../includes/auth.php';
require_once '../../includes/security.php';

if (!check_auth() || !is_admin()) {
    header('Location: ../../login.php');
    exit;
}

$security = new Security();
$current_page = 'settings';

$success = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $security->validateCSRF()) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_smtp':
            $smtp_host = $_POST['smtp_host'] ?? '';
            $smtp_port = $_POST['smtp_port'] ?? '';
            $smtp_user = $_POST['smtp_user'] ?? '';
            $smtp_pass = $_POST['smtp_pass'] ?? '';
            
            if (empty($smtp_host) || empty($smtp_port) || empty($smtp_user)) {
                $error = 'Tüm SMTP bilgilerini doldurun';
            } else {
                // Update SMTP settings in config file
                $config_file = '../../includes/config.php';
                $config = file_get_contents($config_file);
                
                $config = preg_replace(
                    "/define\('SMTP_HOST',\s*'.*?'\);/",
                    "define('SMTP_HOST', '$smtp_host');",
                    $config
                );
                
                $config = preg_replace(
                    "/define\('SMTP_PORT',\s*\d+\);/",
                    "define('SMTP_PORT', $smtp_port);",
                    $config
                );
                
                $config = preg_replace(
                    "/define\('SMTP_USER',\s*'.*?'\);/",
                    "define('SMTP_USER', '$smtp_user');",
                    $config
                );
                
                if (!empty($smtp_pass)) {
                    $config = preg_replace(
                        "/define\('SMTP_PASS',\s*'.*?'\);/",
                        "define('SMTP_PASS', '$smtp_pass');",
                        $config
                    );
                }
                
                if (file_put_contents($config_file, $config)) {
                    $success = 'SMTP ayarları güncellendi';
                } else {
                    $error = 'SMTP ayarları güncellenirken bir hata oluştu';
                }
            }
            break;
            
        case 'update_security':
            $max_login = (int)$_POST['max_login'] ?? 5;
            $lockout_time = (int)$_POST['lockout_time'] ?? 1800;
            
            $config_file = '../../includes/config.php';
            $config = file_get_contents($config_file);
            
            $config = preg_replace(
                "/define\('MAX_LOGIN_ATTEMPTS',\s*\d+\);/",
                "define('MAX_LOGIN_ATTEMPTS', $max_login);",
                $config
            );
            
            $config = preg_replace(
                "/define\('LOCKOUT_TIME',\s*\d+\);/",
                "define('LOCKOUT_TIME', $lockout_time);",
                $config
            );
            
            if (file_put_contents($config_file, $config)) {
                $success = 'Güvenlik ayarları güncellendi';
            } else {
                $error = 'Güvenlik ayarları güncellenirken bir hata oluştu';
            }
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Ayarları - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <img class="h-8 w-auto" src="../../assets/logo.svg" alt="Logo">
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="dashboard.php" class="border-transparent text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>
                        <a href="users.php" class="border-transparent text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Kullanıcılar
                        </a>
                        <a href="sites.php" class="border-transparent text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Siteler
                        </a>
                        <a href="settings.php" class="border-blue-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Ayarlar
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-700"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></span>
                            <a href="../../logout.php" class="text-gray-700 hover:text-gray-900">
                                <i class="fas fa-sign-out-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Sistem Ayarları</h1>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- SMTP Settings -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">SMTP Ayarları</h2>
                
                <form method="POST" action="" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $security->getCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update_smtp">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">SMTP Host</label>
                        <input type="text" name="smtp_host" value="<?php echo SMTP_HOST; ?>" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">SMTP Port</label>
                        <input type="number" name="smtp_port" value="<?php echo SMTP_PORT; ?>" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">SMTP Kullanıcı</label>
                        <input type="text" name="smtp_user" value="<?php echo SMTP_USER; ?>" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">SMTP Şifre</label>
                        <input type="password" name="smtp_pass" placeholder="Değiştirmek için doldurun" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        SMTP Ayarlarını Güncelle
                    </button>
                </form>
            </div>

            <!-- Security Settings -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Güvenlik Ayarları</h2>
                
                <form method="POST" action="" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $security->getCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update_security">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Maksimum Giriş Denemesi</label>
                        <input type="number" name="max_login" value="<?php echo MAX_LOGIN_ATTEMPTS; ?>" min="1" max="10"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hesap Kilitleme Süresi (saniye)</label>
                        <input type="number" name="lockout_time" value="<?php echo LOCKOUT_TIME; ?>" min="300" step="300"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        Güvenlik Ayarlarını Güncelle
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>