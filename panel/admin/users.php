<?php
require_once '../../includes/init.php';

if (!check_auth() || !is_admin()) {
    header('Location: ../../login.php');
    exit;
}

// Sayfalama
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Toplam kullanıcı sayısı
    $total_users = $db->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
    $total_pages = ceil($total_users / $limit);

    // Kullanıcıları al
    $stmt = $db->prepare("
        SELECT u.*, 
               COUNT(DISTINCT s.id) as site_count,
               COUNT(DISTINCT b.id) as backlink_count
        FROM users u
        LEFT JOIN sites s ON u.id = s.user_id
        LEFT JOIN backlinks b ON s.id = b.site_id
        GROUP BY u.id
        ORDER BY u.created_at DESC
        LIMIT ?, ?
    ");
    $stmt->bind_param("ii", $offset, $limit);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log("Users page error: " . $e->getMessage());
    die("Bir hata oluştu. Lütfen daha sonra tekrar deneyin.");
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Yönetimi - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            max-width: 500px;
            position: relative;
        }
        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            cursor: pointer;
        }
        .table-container {
            overflow-x: auto;
            margin-top: 1rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: #f3f4f6;
            padding: 12px;
            text-align: left;
            font-size: 0.875rem;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:hover {
            background-color: #f9fafb;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }
        .btn-primary:hover {
            background-color: #2563eb;
        }
        .btn-danger {
            background-color: #ef4444;
            color: white;
        }
        .btn-danger:hover {
            background-color: #dc2626;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .badge-admin {
            background-color: #818cf8;
            color: white;
        }
        .badge-user {
            background-color: #34d399;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php require_once '../../includes/admin-nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kullanıcı Yönetimi</h1>
                <p class="mt-1 text-sm text-gray-500">Toplam <?php echo $total_users; ?> kullanıcı</p>
            </div>
            <button onclick="addUser()" class="btn btn-primary">
                <i class="fas fa-user-plus mr-2"></i> Yeni Kullanıcı
            </button>
        </div>

        <div class="bg-white rounded-lg shadow-sm">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Kullanıcı</th>
                            <th>Rol</th>
                            <th>Site</th>
                            <th>Backlink</th>
                            <th>Kredi</th>
                            <th>Kayıt Tarihi</th>
                            <th class="text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($user['email']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($user['is_admin']): ?>
                                        <span class="badge badge-admin">Admin</span>
                                    <?php else: ?>
                                        <span class="badge badge-user">Müşteri</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $user['site_count']; ?></td>
                                <td><?php echo $user['backlink_count']; ?></td>
                                <td><?php echo number_format($user['credits']); ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                                <td class="text-right">
                                    <button onclick="editUser(<?php echo $user['id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-900 mr-3"
                                            title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button onclick="deleteUser(<?php echo $user['id']; ?>)" 
                                                class="text-red-600 hover:text-red-900"
                                                title="Sil">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="mt-4 flex justify-center">
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" 
                           class="<?php echo $page == $i ? 'bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            </div>
        <?php endif; ?>
    </div>

    <!-- Düzenleme Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Kullanıcı Düzenle</h3>
                <button onclick="document.getElementById('editModal').style.display='none'" class="close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="editForm" onsubmit="return false;">
                <input type="hidden" id="editUserId">
                
                <div class="mb-4">
                    <label for="editUsername" class="block text-sm font-medium text-gray-700">Kullanıcı Adı</label>
                    <input type="text" id="editUsername" name="username" required autocomplete="username"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="editEmail" class="block text-sm font-medium text-gray-700">E-posta</label>
                    <input type="email" id="editEmail" name="email" required autocomplete="email"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="editPassword" class="block text-sm font-medium text-gray-700">Şifre (Boş bırakılırsa değişmez)</label>
                    <input type="password" id="editPassword" name="password" autocomplete="new-password"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="editCredits" class="block text-sm font-medium text-gray-700">Krediler</label>
                    <input type="number" id="editCredits" name="credits" required autocomplete="off"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="editIsAdmin" name="is_admin" 
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-600">Admin Yetkisi</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('editModal').style.display='none'"
                            class="btn bg-gray-100 text-gray-700 hover:bg-gray-200">
                        İptal
                    </button>
                    <button type="submit" onclick="app.users.submitEdit()"
                            class="btn btn-primary">
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/app.js"></script>
</body>
</html>