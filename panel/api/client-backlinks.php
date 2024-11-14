<?php
require_once '../../includes/init.php';

header('Content-Type: text/html; charset=utf-8');

// Get domain and clean it
$domain = $_GET['site'] ?? '';
$domain = preg_replace('/^www\./', '', strtolower(trim($domain)));

if (empty($domain)) {
    exit('<!-- No domain provided -->');
}

try {
    // Add site if not exists
    $stmt = $db->prepare("SELECT id, is_verified FROM sites WHERE domain = ?");
    $stmt->bind_param("s", $domain);
    $stmt->execute();
    $site = $stmt->get_result()->fetch_assoc();

    if (!$site) {
        // Auto-add site with pending verification
        $verification_code = bin2hex(random_bytes(16));
        $stmt = $db->prepare("INSERT INTO sites (domain, user_id, verification_code) VALUES (?, 1, ?)");
        $stmt->bind_param("ss", $domain, $verification_code);
        $stmt->execute();
        $site_id = $db->insert_id;
        
        exit('<!-- Site added, pending verification -->');
    }

    if (!$site['is_verified']) {
        exit('<!-- Site not verified -->');
    }

    // Get active backlinks with timeout
    $db->query("SET SESSION wait_timeout=5");
    $links = $db->query("
        SELECT target_url, anchor_text 
        FROM backlinks 
        WHERE site_id = {$site['id']} 
        AND status = 'active' 
        ORDER BY RAND() 
        LIMIT 5
    ")->fetch_all(MYSQLI_ASSOC);

    // Output backlinks
    foreach ($links as $link) {
        echo '<a href="' . htmlspecialchars($link['target_url']) . '" rel="nofollow" target="_blank">' . 
             htmlspecialchars($link['anchor_text']) . '</a> ';
    }

} catch (Exception $e) {
    error_log("Backlink API error: " . $e->getMessage());
    exit('<!-- Error processing request -->');
}
?>