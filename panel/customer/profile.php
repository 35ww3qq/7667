<?php
require_once '../../includes/init.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
require_once '../../includes/security.php';


if (!check_auth()) {
    header('Location: ../../login.php');
    exit;
}

$security = new Security();
$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($security->validateCSRF()) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $new_password_confirm = $_POST['new_password_confirm'] ?? '';
        
        if (!password_verify($current_password, $user['password'])) {
            $error = 'Mevcut şifre yanlış';
        } elseif ($new_password !== $new_password_confirm) {
            $error = 'Yeni şifreler eşleşmiyor';
        } elseif (strlen($new_password) < 8) {
            $error = 'Yeni şifre en az 8 karakter olmalıdır';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success = 'Şifreniz başarıyla güncellendi';
            } else {
                $error = 'Şifre güncellenirken bir hata oluştu';
            }
        }
    }
}

$current_page = 'profile';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Backlink System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../includes/customer-nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <!-- Profile Info -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <h2 class="text-xl font-semibold mb-6">Profil Bilgileri</h2>
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kullanıcı Adı</label>
                        <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kayıt Tarihi</label>
                        <p class="mt-1 text-gray-900"><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Son Giriş</label>
                        <p class="mt-1 text-gray-900"><?php echo date('d.m.Y H:i', strtotime($user['last_login'])); ?></p>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-6">Şifre Değiştir</h2>
                
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo $security->getCSRFToken(); ?>">
                    
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Mevcut Şifre</label>
                        <input type="password" id="current_password" name="current_password" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700">Yeni Şifre</label>
                        <input type="password" id="new_password" name="new_password" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="new_password_confirm" class="block text-sm font-medium text-gray-700">Yeni Şifre Tekrar</label>
                        <input type="password" id="new_password_confirm" name="new_password_confirm" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Şifreyi Güncelle
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>