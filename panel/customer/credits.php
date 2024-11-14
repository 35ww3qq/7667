<?php
require_once '../../includes/init.php';

if (!check_auth()) {
    header('Location: ../../login.php');
    exit;
}

// Kullanıcı bilgilerini al
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT credits FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$credits = $stmt->get_result()->fetch_assoc()['credits'];

// Kredi paketleri
$packages = [
    ['id' => 1, 'credits' => 100, 'price' => 50, 'name' => 'Başlangıç Paketi'],
    ['id' => 2, 'credits' => 250, 'price' => 100, 'name' => 'Profesyonel Paket'],
    ['id' => 3, 'credits' => 500, 'price' => 175, 'name' => 'İşletme Paketi'],
    ['id' => 4, 'credits' => 1000, 'price' => 300, 'name' => 'Kurumsal Paket']
];

// Kredi geçmişi
$stmt = $db->prepare("
    SELECT * FROM credit_logs 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Krediler - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php require_once '../../includes/customer-nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Krediler</h1>
            <div class="text-lg font-semibold">
                Mevcut Krediniz: <span class="text-blue-600"><?php echo $credits; ?></span>
            </div>
        </div>

        <!-- Kredi Paketleri -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <?php foreach ($packages as $package): ?>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="text-xl font-bold mb-2"><?php echo $package['name']; ?></div>
                    <div class="text-3xl font-bold text-blue-600 mb-4"><?php echo $package['credits']; ?> Kredi</div>
                    <div class="text-gray-600 mb-4"><?php echo $package['price']; ?> TL</div>
                    <button onclick="app.credits.buy(<?php echo $package['id']; ?>)"
                            class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Satın Al
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Kredi Geçmişi -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Kredi Geçmişi</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">İşlem</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Miktar</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Açıklama</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($history as $log): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo date('d.m.Y H:i', strtotime($log['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($log['type'] === 'added'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Eklendi
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Kullanıldı
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $log['amount']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($log['description']); ?>
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