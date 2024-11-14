<?php
require_once '../../includes/auth.php';
require_once '../../includes/security.php';

header('Content-Type: application/json');

if (!check_auth()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$security = new Security();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $site_id = $data['site_id'] ?? null;
    
    if (!$site_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Site ID required']);
        exit;
    }
    
    $stmt = $db->prepare("SELECT * FROM sites WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $site_id, $_SESSION['user_id']);
    $stmt->execute();
    $site = $stmt->get_result()->fetch_assoc();
    
    if (!$site) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        exit;
    }
    
    $verification_url = "https://" . $site['domain'] . "/backlink-verify.txt";
    $ch = curl_init($verification_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $content = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($status_code === 200 && trim($content) === $site['verification_code']) {
        $db->query("UPDATE sites SET is_verified = TRUE WHERE id = $site_id");
        echo json_encode(['success' => true, 'message' => 'Site verified successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Verification failed']);
    }
}