<?php
class BacklinkChecker {
    public function checkBacklink($backlink_id) {
        global $db;
        
        $stmt = $db->prepare("SELECT b.*, s.domain FROM backlinks b JOIN sites s ON b.site_id = s.id WHERE b.id = ?");
        $stmt->bind_param("i", $backlink_id);
        $stmt->execute();
        $backlink = $stmt->get_result()->fetch_assoc();
        
        if (!$backlink) {
            return false;
        }
        
        $ch = curl_init("https://" . $backlink['domain']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $start_time = microtime(true);
        $content = curl_exec($ch);
        $loading_time = microtime(true) - $start_time;
        
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $link_exists = strpos($content, $backlink['target_url']) !== false && 
                      strpos($content, $backlink['anchor_text']) !== false;
        
        $stmt = $db->prepare("INSERT INTO backlink_reports (backlink_id, status_code, loading_time) VALUES (?, ?, ?)");
        $stmt->bind_param("iid", $backlink_id, $status_code, $loading_time);
        $stmt->execute();
        
        $new_status = $link_exists && $status_code == 200 ? 'active' : 'removed';
        $db->query("UPDATE backlinks SET status = '$new_status' WHERE id = $backlink_id");
        
        return [
            'status_code' => $status_code,
            'loading_time' => $loading_time,
            'link_exists' => $link_exists
        ];
    }
    
    public function checkAllBacklinks() {
        global $db;
        $backlinks = $db->query("SELECT id FROM backlinks WHERE status != 'removed'")->fetch_all(MYSQLI_ASSOC);
        
        foreach ($backlinks as $backlink) {
            $this->checkBacklink($backlink['id']);
            usleep(500000);
        }
    }
}