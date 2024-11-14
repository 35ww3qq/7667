<?php
// Activity logging function
function log_activity($user_id, $action, $details = '') {
    global $db;
    $ip = filter_var($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', FILTER_VALIDATE_IP) ?: '0.0.0.0';
    $ua = filter_var($_SERVER['HTTP_USER_AGENT'] ?? '', FILTER_SANITIZE_STRING);
    
    $stmt = $db->prepare("
        INSERT INTO activity_log (user_id, action, details, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $user_id, $action, $details, $ip, $ua);
    return $stmt->execute();
}

// Get user's sites
function get_user_sites($user_id) {
    global $db;
    $user_id = (int)$user_id;
    
    $query = "
        SELECT s.*, 
               COUNT(DISTINCT b.id) as backlink_count
        FROM sites s 
        LEFT JOIN backlinks b ON s.id = b.site_id 
        WHERE s.user_id = ? 
        GROUP BY s.id
    ";
    
    $stmt = $db->prepare($query);
    if ($stmt === false) {
        error_log("MySQL Error: " . $db->error);
        return [];
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get user credits
function get_user_credits($user_id) {
    global $db;
    $user_id = (int)$user_id;
    
    $stmt = $db->prepare("SELECT credits FROM users WHERE id = ?");
    if ($stmt === false) {
        error_log("MySQL Error: " . $db->error);
        return 0;
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0] ?? 0;
}

// Get user stats
function get_user_stats($user_id) {
    global $db;
    $user_id = (int)$user_id;
    
    try {
        $stats = [
            'total_sites' => 0,
            'active_backlinks' => 0,
            'credits' => 0,
            'verified_sites' => 0
        ];

        // Get total sites
        $stmt = $db->prepare("SELECT COUNT(*) FROM sites WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stats['total_sites'] = $stmt->get_result()->fetch_row()[0] ?? 0;

        // Get active backlinks
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM backlinks b 
            JOIN sites s ON b.site_id = s.id 
            WHERE s.user_id = ? AND b.status = 'active'
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stats['active_backlinks'] = $stmt->get_result()->fetch_row()[0] ?? 0;

        // Get credits
        $stats['credits'] = get_user_credits($user_id);

        // Get verified sites
        $stmt = $db->prepare("SELECT COUNT(*) FROM sites WHERE user_id = ? AND is_verified = 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stats['verified_sites'] = $stmt->get_result()->fetch_row()[0] ?? 0;

        return $stats;
    } catch (Exception $e) {
        error_log("Error in get_user_stats: " . $e->getMessage());
        return [
            'total_sites' => 0,
            'active_backlinks' => 0,
            'credits' => 0,
            'verified_sites' => 0
        ];
    }
}

// Format datetime
function format_datetime($datetime) {
    return date('d.m.Y H:i', strtotime($datetime));
}

// Verify site ownership
function verify_site($site_id) {
    global $db;
    $site_id = (int)$site_id;
    
    $stmt = $db->prepare("UPDATE sites SET is_verified = 1 WHERE id = ?");
    if ($stmt === false) {
        error_log("MySQL Error: " . $db->error);
        return false;
    }
    
    $stmt->bind_param("i", $site_id);
    return $stmt->execute();
}

// Delete site and its backlinks safely
function delete_site($site_id) {
    global $db;
    $site_id = (int)$site_id;
    
    $db->begin_transaction();
    try {
        $stmt = $db->prepare("DELETE FROM backlinks WHERE site_id = ?");
        $stmt->bind_param("i", $site_id);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM sites WHERE id = ?");
        $stmt->bind_param("i", $site_id);
        $stmt->execute();
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollback();
        error_log("Error deleting site: " . $e->getMessage());
        return false;
    }
}

// Update credits safely
function update_user_credits($user_id, $amount) {
    global $db;
    $user_id = (int)$user_id;
    $amount = (int)$amount;
    
    $db->begin_transaction();
    try {
        $stmt = $db->prepare("
            UPDATE users 
            SET credits = credits + ? 
            WHERE id = ? AND (credits + ?) >= 0
        ");
        
        if ($stmt === false) {
            throw new Exception("MySQL Error: " . $db->error);
        }
        
        $stmt->bind_param("iii", $amount, $user_id, $amount);
        $success = $stmt->execute();
        
        if ($success) {
            $action = $amount >= 0 ? 'credit_added' : 'credit_used';
            log_activity($user_id, $action, "Amount: $amount");
            $db->commit();
            return true;
        }
        
        $db->rollback();
        return false;
    } catch (Exception $e) {
        $db->rollback();
        error_log("Error updating credits: " . $e->getMessage());
        return false;
    }
}
?>