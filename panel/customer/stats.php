<?php
// File: /includes/stats.php
require_once '../../includes/init.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
require_once '../../includes/security.php';


if (!defined('STATS_LOADED')) {
    define('STATS_LOADED', true);
    
    /**
     * Get user's current credit balance
     */
    function get_user_credits($user_id) {
        global $db;
        $stmt = $db->prepare("SELECT credits FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_row()[0] ?? 0;
    }

    /**
     * Get user's sites with stats
     */
    function get_user_sites($user_id) {
        global $db;
        $user_id = (int)$user_id;
        
        $result = $db->query("
            SELECT s.*,
                   COUNT(DISTINCT b.id) as backlink_count,
                   COUNT(DISTINCT CASE WHEN b.status = 'active' THEN b.id END) as active_backlinks
            FROM sites s
            LEFT JOIN backlinks b ON s.id = b.site_id
            WHERE s.user_id = $user_id
            GROUP BY s.id, s.domain, s.user_id, s.is_verified, s.created_at
            ORDER BY s.created_at DESC
        ");

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Get site stats
     */
    function get_site_stats($site_id) {
        global $db;
        
        $stmt = $db->prepare("
            SELECT 
                COUNT(DISTINCT b.id) as total_backlinks,
                COUNT(DISTINCT CASE WHEN b.status = 'active' THEN b.id END) as active_backlinks,
                COUNT(DISTINCT CASE WHEN b.status = 'pending' THEN b.id END) as pending_backlinks,
                COUNT(DISTINCT CASE WHEN b.status = 'removed' THEN b.id END) as removed_backlinks,
                MAX(b.last_checked) as last_check_date
            FROM sites s
            LEFT JOIN backlinks b ON s.id = b.site_id
            WHERE s.id = ?
            GROUP BY s.id
        ");
        
        $stmt->bind_param("i", $site_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : [];
    }

    /**
     * Get user stats for dashboard
     */
    function get_user_stats($user_id) {
        global $db;
        $user_id = (int)$user_id;
        
        return [
            'total_sites' => $db->query("SELECT COUNT(*) FROM sites WHERE user_id = $user_id")->fetch_row()[0] ?? 0,
            'active_backlinks' => $db->query("
                SELECT COUNT(*) 
                FROM backlinks b 
                JOIN sites s ON b.site_id = s.id 
                WHERE s.user_id = $user_id AND b.status = 'active'
            ")->fetch_row()[0] ?? 0,
            'credits' => get_user_credits($user_id),
            'verified_sites' => $db->query("
                SELECT COUNT(*) 
                FROM sites 
                WHERE user_id = $user_id AND is_verified = 1
            ")->fetch_row()[0] ?? 0
        ];
    }

    /**
     * Log credit usage
     */
    function log_credit_usage($user_id, $credits_used, $action, $details = '') {
        global $db;
        
        $stmt = $db->prepare("
            INSERT INTO credit_logs (user_id, credits_used, action, details, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->bind_param("iiss", $user_id, $credits_used, $action, $details);
        return $stmt->execute();
    }

    /**
     * Update user credits
     */
    function update_user_credits($user_id, $credits_change) {
        global $db;
        
        $db->begin_transaction();
        
        try {
            $stmt = $db->prepare("
                UPDATE users 
                SET credits = credits + ? 
                WHERE id = ? AND (credits + ?) >= 0
            ");
            
            $stmt->bind_param("iii", $credits_change, $user_id, $credits_change);
            $result = $stmt->execute();
            
            if ($result && $stmt->affected_rows > 0) {
                if ($credits_change < 0) {
                    log_credit_usage($user_id, abs($credits_change), 'used', 'Credit usage');
                } else {
                    log_credit_usage($user_id, $credits_change, 'added', 'Credit addition');
                }
                $db->commit();
                return true;
            }
            
            $db->rollback();
            return false;
            
        } catch (Exception $e) {
            $db->rollback();
            error_log("Credit update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user has enough credits
     */
    function has_enough_credits($user_id, $required_credits) {
        return get_user_credits($user_id) >= $required_credits;
    }
}
?>