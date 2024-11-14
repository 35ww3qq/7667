<?php
require_once 'includes/init.php';
require_once 'includes/security.php';

// Zaten giriş yapmışsa yönlendir
if (check_auth()) {
    header('Location: ' . (is_admin() ? 'panel/admin/dashboard.php' : 'panel/customer/dashboard.php'));
    exit;
}

$security = new Security();
$db = Database::getInstance()->getConnection();  // Database bağlantısını ekledik

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($security->validateCSRF()) {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if ($password !== $password_confirm) {
            $error = 'Şifreler eşleşmiyor';
        } elseif (strlen($password) < 8) {
            $error = 'Şifre en az 8 karakter olmalıdır';
        } else {
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'Bu kullanıcı adı veya email zaten kullanılıyor';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (username, email, password, credits) VALUES (?, ?, ?, 10)");
                $stmt->bind_param("sss", $username, $email, $hashed_password);
                
                if ($stmt->execute()) {
                    $success = 'Hesabınız başarıyla oluşturuldu. Şimdi giriş yapabilirsiniz.';
                } else {
                    $error = 'Kayıt sırasında bir hata oluştu';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Backlink Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Backlink Management System</h1>
            <p class="mt-2 text-gray-600">Yeni hesap oluştur</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-8">
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <?php echo htmlspecialchars($success); ?>
                    <div class="mt-2">
                        <a href="login.php" class="text-green-700 font-semibold hover:text-green-900">Giriş Yap →</a>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST" action="" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo $security->getCSRFToken(); ?>">
                    
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Kullanıcı Adı</label>
                        <input type="text" id="username" name="username" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Şifre</label>
                        <input type="password" id="password" name="password" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-gray-700">Şifre Tekrar</label>
                        <input type="password" id="password_confirm" name="password_confirm" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-user-plus mr-2"></i> Kayıt Ol
                    </button>
                </form>
            <?php endif; ?>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Zaten hesabınız var mı?
                    <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">
                        Giriş Yap
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>