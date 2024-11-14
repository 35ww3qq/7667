<?php
require_once '../../includes/init.php';

if (!check_auth()) {
    header('Location: ../../login.php');
    exit;
}

$site_id = $_GET['site_id'] ?? null;

if ($site_id) {
    // Site bilgilerini al
    $stmt = $db->prepare("SELECT * FROM sites WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $site_id, $_SESSION['user_id']);
    $stmt->execute();
    $site = $stmt->get_result()->fetch_assoc();

    if (!$site) {
        header('Location: sites.php');
        exit;
    }

    // Backlink'leri al
    $stmt = $db->prepare("SELECT * FROM backlinks WHERE site_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $site_id);
    $stmt->execute();
    $backlinks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Tüm siteleri al (dropdown için)
$stmt = $db->prepare("SELECT id, domain FROM sites WHERE user_id = ? ORDER BY domain");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$sites = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backlinkler - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php require_once '../../includes/customer-nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Backlinkler</h1>
            <div class="flex items-center space-x-4">
                <select onchange="changeSite(this.value)" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">Site Seçin</option>
                    <?php foreach ($sites as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo $site_id == $s['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['domain']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <?php if ($site_id): ?>
                    <button onclick="addBacklink(<?php echo $site_id; ?>)" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        <i class="fas fa-plus mr-2"></i> Yeni Backlink
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($site_id): ?>
            <?php if (empty($backlinks)): ?>
                <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                    <p class="text-gray-500">Bu site için henüz backlink eklenmemiş.</p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Hedef URL</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Anchor Text</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($backlinks as $backlink): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="<?php echo htmlspecialchars($backlink['target_url']); ?>" target="_blank" class="text-blue-600 hover:text-blue-900">
                                            <?php echo htmlspecialchars($backlink['target_url']); ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($backlink['anchor_text']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($backlink['status'] === 'active'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Aktif
                                            </span>
                                        <?php elseif ($backlink['status'] === 'pending'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Beklemede
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Pasif
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('d.m.Y H:i', strtotime($backlink['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="app.backlinks.edit(<?php echo $backlink['id']; ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="app.backlinks.delete(<?php echo $backlink['id']; ?>)" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <div class="text-gray-500 mb-4">
                    <i class="fas fa-link text-4xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Site Seçin</h3>
                <p class="text-gray-600">
                    Backlinkleri görüntülemek için yukarıdan bir site seçin veya
                    <a href="sites.php" class="text-blue-600 hover:text-blue-500">yeni bir site ekleyin</a>.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <script src="../../assets/js/app.js"></script>
</body>
</html>