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
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $site_id = $_GET['id'] ?? null;
    
    if ($site_id) {
        // Get specific site
        $stmt = $db->prepare("
            SELECT s.*, COUNT(b.id) as backlink_count 
            FROM sites s 
            LEFT JOIN backlinks b ON s.id = b.site_id 
            WHERE s.id = ? AND s.user_id = ? 
            GROUP BY s.id
        ");
        $stmt->bind_param("ii", $site_id, $user_id);
        $stmt->execute();
        $site = $stmt->get_result()->fetch_assoc();
        
        if (!$site) {
            http_response_code(404);
            echo json_encode(['error' => 'Site not found']);
            exit;
        }
        
        echo json_encode(['success' => true, 'site' => $site]);
    } else {
        // Get all user's sites
        $sites = $db->query("
            SELECT s.*, COUNT(b.id) as backlink_count 
            FROM sites s 
            LEFT JOIN backlinks b ON s.id = b.site_id 
            WHERE s.user_id = $user_id 
            GROUP BY s.id 
            ORDER BY s.created_at DESC
        ")->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'sites' => $sites]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['domain'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Domain required']);
        exit;
    }
    
    $domain = trim(strtolower($data['domain']));
    
    // Validate domain
    if (!$security->validateDomain($domain)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid domain']);
        exit;
    }
    
    // Check if domain already exists
    $stmt = $db->prepare("SELECT id FROM sites WHERE domain = ?");
    $stmt->bind_param("s", $domain);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Domain already exists']);
        exit;
    }
    
    // Add site
    $verification_code = bin2hex(random_bytes(16));
    $stmt = $db->prepare("
        INSERT INTO sites (user_id, domain, verification_code, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    
    $stmt->bind_param("iss", $user_id, $domain, $verification_code);
    
    if ($stmt->execute()) {
        $site_id = $db->insert_id;
        echo json_encode([
            'success' => true,
            'site_id' => $site_id,
            'verification_code' => $verification_code
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add site']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $site_id = $data['site_id'] ?? null;
    
    if (!$site_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Site ID required']);
        exit;
    }
    
    // Check ownership
    $stmt = $db->prepare("SELECT id FROM sites WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $site_id, $user_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        exit;
    }
    
    // Delete site and its backlinks
    $db->begin_transaction();
    
    try {
        $db->query("DELETE FROM backlinks WHERE site_id = $site_id");
        $db->query("DELETE FROM sites WHERE id = $site_id");
        
        $db->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $db->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete site']);
    }
}