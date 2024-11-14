<?php
if (!defined('STATS_LOADED')) {
    define('STATS_LOADED', true);

    if (!function_exists('get_user_stats')) {
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
    }
    
    if (!function_exists('has_enough_credits')) {
        function has_enough_credits($user_id, $required_credits) {
            return get_user_credits($user_id) >= $required_credits;
        }
    }
}
?>