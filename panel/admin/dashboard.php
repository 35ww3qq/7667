<?php
require_once '../../includes/init.php';

if (!check_auth() || !is_admin()) {
    header('Location: ../../login.php');
    exit;
}

// İstatistikleri al
$stats = [];

$stats['total_users'] = $db->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$stats['total_sites'] = $db->query("SELECT COUNT(*) as count FROM sites")->fetch_assoc()['count'];
$stats['total_backlinks'] = $db->query("SELECT COUNT(*) as count FROM backlinks")->fetch_assoc()['count'];

// Son kayıt olan kullanıcılar
$recent_users = $db->query("
    SELECT username, email, created_at 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php require_once '../../includes/admin-nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Admin Dashboard</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- İstatistikler -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-gray-500 mb-2">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div class="text-2xl font-bold mb-1"><?php echo $stats['total_users']; ?></div>
                <div class="text-gray-600">Toplam Kullanıcı</div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-gray-500 mb-2">
                    <i class="fas fa-globe text-2xl"></i>
                </div>
                <div class="text-2xl font-bold mb-1"><?php echo $stats['total_sites']; ?></div>
                <div class="text-gray-600">Toplam Site</div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-gray-500 mb-2">
                    <i class="fas fa-link text-2xl"></i>
                </div>
                <div class="text-2xl font-bold mb-1"><?php echo $stats['total_backlinks']; ?></div>
                <div class="text-gray-600">Toplam Backlink</div>
            </div>
        </div>

        <!-- Son kayıt olan kullanıcılar -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Son Kayıt Olan Kullanıcılar</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Kullanıcı Adı</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Kayıt Tarihi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recent_users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($user['username']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../../assets/js/app.js"></script>
</body>
</html>