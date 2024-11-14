<?php
require_once 'includes/init.php';
require_once 'includes/security.php';

if (check_auth()) {
    header('Location: ' . (is_admin() ? 'panel/admin/dashboard.php' : 'panel/customer/dashboard.php'));
    exit;
}

$security = new Security();
$db = Database::getInstance()->getConnection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($security->validateCSRF()) {
        $email = $_POST['email'] ?? '';
        
        $stmt = $db->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user['id'], $token, $expires);
            
            if ($stmt->execute()) {
                $reset_link = SITE_URL . "/reset-password.php?token=" . $token;
                
                $subject = "Şifre Sıfırlama - Backlink Management System";
                $message = "Merhaba " . $user['username'] . ",\n\n";
                $message .= "Şifrenizi sıfırlamak için aşağıdaki bağlantıya tıklayın:\n\n";
                $message .= $reset_link . "\n\n";
                $message .= "Bu bağlantı 1 saat sonra geçersiz olacaktır.\n\n";
                $message .= "Eğer şifre sıfırlama talebinde bulunmadıysanız, bu emaili görmezden gelebilirsiniz.\n\n";
                $message .= "Saygılarımızla,\nBacklink Management System";
                
                if (send_email($email, $subject, $message)) {
                    $success = 'Şifre sıfırlama bağlantısı email adresinize gönderildi';
                } else {
                    $error = 'Email gönderimi sırasında bir hata oluştu';
                }
            } else {
                $error = 'Sistem hatası oluştu';
            }
        } else {
            $success = 'Şifre sıfırlama bağlantısı email adresinize gönderildi';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifremi Unuttum - Backlink Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Şifremi Unuttum</h1>
            <p class="mt-2 text-gray-600">Şifre sıfırlama bağlantısı için email adresinizi girin</p>
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
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Adresi</label>
                        <input type="email" id="email" name="email" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-paper-plane mr-2"></i> Sıfırlama Bağlantısı Gönder
                    </button>
                </form>
            <?php endif; ?>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">
                        <i class="fas fa-arrow-left mr-1"></i> Giriş sayfasına dön
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>