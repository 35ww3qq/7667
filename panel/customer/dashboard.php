<?php
require_once '../../includes/init.php';

if (!check_auth()) {
    header('Location: ../../login.php');
    exit;
}

if (is_admin()) {
    header('Location: ../admin/dashboard.php');
    exit;
}

// Kullanıcı bilgilerini al
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// İstatistikleri al
$stats = [
    'total_sites' => 0,
    'active_backlinks' => 0,
    'credits' => $user['credits'] ?? 0
];

$stmt = $db->prepare("SELECT COUNT(*) as count FROM sites WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stats['total_sites'] = $result['count'];

$stmt = $db->prepare("
    SELECT COUNT(*) as count 
    FROM backlinks b 
    JOIN sites s ON b.site_id = s.id 
    WHERE s.user_id = ? AND b.status = 'active'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stats['active_backlinks'] = $result['count'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php require_once '../../includes/customer-nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Dashboard</h1>
            <div class="text-sm text-gray-600">
                Hoş geldiniz, <?php echo htmlspecialchars($user['username']); ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- İstatistikler -->
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
                <div class="text-2xl font-bold mb-1"><?php echo $stats['active_backlinks']; ?></div>
                <div class="text-gray-600">Aktif Backlink</div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-gray-500 mb-2">
                    <i class="fas fa-coins text-2xl"></i>
                </div>
                <div class="text-2xl font-bold mb-1"><?php echo $stats['credits']; ?></div>
                <div class="text-gray-600">Kalan Kredi</div>
            </div>
        </div>

        <!-- Son aktiviteler veya diğer içerikler buraya eklenebilir -->
    </div>

    <script src="../../assets/js/app.js"></script>
</body>
</html>