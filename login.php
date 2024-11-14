<?php
require_once 'includes/init.php';

// Zaten giriş yapmışsa yönlendir
if (check_auth()) {
    header('Location: ' . (is_admin() ? 'panel/admin/dashboard.php' : 'panel/customer/dashboard.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($security->validateCSRF() && $security->validateLoginAttempt()) {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                $error = 'Kullanıcı adı ve şifre gereklidir';
            } else {
                if (login($username, $password)) {
                    $security->resetLoginAttempts();
                    $redirect = is_admin() ? 'panel/admin/dashboard.php' : 'panel/customer/dashboard.php';
                    error_log("Redirecting to: " . $redirect);
                    header('Location: ' . $redirect);
                    exit;
                } else {
                    $security->incrementLoginAttempts();
                    $error = 'Geçersiz kullanıcı adı veya şifre';
                }
            }
        } else {
            $error = 'Çok fazla başarısız giriş denemesi. Lütfen daha sonra tekrar deneyin.';
        }
    } catch (Exception $e) {
        error_log("Login Error: " . $e->getMessage());
        $error = 'Bir sistem hatası oluştu. Lütfen daha sonra tekrar deneyin.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900"><?php echo SITE_NAME; ?></h1>
            <p class="mt-2 text-gray-600">Güvenli backlink yönetim sistemi</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-8">
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $security->getCSRFToken(); ?>">
                
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Kullanıcı Adı veya Email</label>
                    <input type="text" id="username" name="username" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Şifre</label>
                    <input type="password" id="password" name="password" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Beni Hatırla
                        </label>
                    </div>

                    <a href="forgot-password.php" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                        Şifremi Unuttum
                    </a>
                </div>

                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-sign-in-alt mr-2"></i> Giriş Yap
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Hesabınız yok mu?
                    <a href="register.php" class="font-medium text-blue-600 hover:text-blue-500">
                        Kayıt Ol
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
    // Form submit edildiğinde butonu devre dışı bırak
    document.querySelector('form').addEventListener('submit', function(e) {
        const button = this.querySelector('button[type="submit"]');
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Giriş Yapılıyor...';
    });
    </script>
</body>
</html>