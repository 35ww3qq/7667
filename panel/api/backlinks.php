<?php
require_once '../../includes/init.php';

if (!check_auth()) {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

if (!$security->validateRequest()) {
    http_response_code(403);
    exit(json_encode(['error' => 'Invalid request']));
}

$response = ['success' => false];

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $site_id = $_GET['site_id'] ?? null;
        $backlink_id = $_GET['id'] ?? null;
        
        if ($backlink_id) {
            // Tek backlink detayı
            $stmt = $db->prepare("SELECT * FROM backlinks WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $backlink_id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $response['data'] = $result->fetch_assoc();
        } else {
            // Site'a ait tüm backlinkler
            $stmt = $db->prepare("SELECT * FROM backlinks WHERE site_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $site_id, $_SESSION['user_id']);
            $stmt->execute();
            $response['data'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        $response['success'] = true;
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$security->validateUrl($data['target_url'])) {
            $response['error'] = 'Geçersiz URL formatı';
            break;
        }
        
        $stmt = $db->prepare("INSERT INTO backlinks (site_id, user_id, target_url, anchor_text) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", 
            $data['site_id'], 
            $_SESSION['user_id'],
            $data['target_url'],
            $data['anchor_text']
        );
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['id'] = $stmt->insert_id;
        } else {
            $response['error'] = 'Backlink eklenirken bir hata oluştu';
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$security->validateUrl($data['target_url'])) {
            $response['error'] = 'Geçersiz URL formatı';
            break;
        }
        
        $stmt = $db->prepare("UPDATE backlinks SET target_url = ?, anchor_text = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii",
            $data['target_url'],
            $data['anchor_text'],
            $data['id'],
            $_SESSION['user_id']
        );
        
        $response['success'] = $stmt->execute();
        if (!$response['success']) {
            $response['error'] = 'Backlink güncellenirken bir hata oluştu';
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $db->prepare("DELETE FROM backlinks WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $data['id'], $_SESSION['user_id']);
        
        $response['success'] = $stmt->execute();
        if (!$response['success']) {
            $response['error'] = 'Backlink silinirken bir hata oluştu';
        }
        break;
}

header('Content-Type: application/json');
echo json_encode($response);