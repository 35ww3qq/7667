<?php
class Security {
    private $max_login_attempts = 5;
    private $lockout_time = 1800;
    private $rate_limit_requests = 100;
    private $rate_limit_window = 3600;
    private $session_lifetime = 3600;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1);
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.gc_maxlifetime', $this->session_lifetime);
            ini_set('session.use_strict_mode', 1);
            
            session_start();
        }
    }
    
    public function validateLoginAttempt() {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['lockout_until'] = 0;
        }
        
        if (time() < $_SESSION['lockout_until']) {
            return false;
        }
        
        if ($_SESSION['login_attempts'] >= $this->max_login_attempts) {
            $_SESSION['lockout_until'] = time() + $this->lockout_time;
            return false;
        }
        
        return true;
    }
    
    public function incrementLoginAttempts() {
        $_SESSION['login_attempts']++;
    }
    
    public function resetLoginAttempts() {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['lockout_until'] = 0;
    }
    
    public function validateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                return false;
            }
        }
        
        return true;
    }
    
    public function getCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public function validateDomain($domain) {
        return filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
    }
    
    public function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL);
    }
    
    public function validateRequest() {
        return $this->validateCSRF() && 
               $this->checkRateLimit() && 
               $this->validateOrigin();
    }
    
    private function checkRateLimit() {
        $ip = $_SERVER['REMOTE_ADDR'];
        $now = time();
        
        if (!isset($_SESSION['rate_limit'][$ip])) {
            $_SESSION['rate_limit'][$ip] = [
                'requests' => 0,
                'window_start' => $now
            ];
        }
        
        if ($now - $_SESSION['rate_limit'][$ip]['window_start'] > $this->rate_limit_window) {
            $_SESSION['rate_limit'][$ip] = [
                'requests' => 0,
                'window_start' => $now
            ];
        }
        
        $_SESSION['rate_limit'][$ip]['requests']++;
        
        return $_SESSION['rate_limit'][$ip]['requests'] <= $this->rate_limit_requests;
    }
    
    private function validateOrigin() {
        if (!isset($_SERVER['HTTP_ORIGIN'])) {
            return true;
        }
        
        $allowed_origins = [
            'http://localhost',
            'https://yourdomain.com'
        ];
        
        return in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins);
    }
    
    // XSS koruma
    public function sanitize($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitize($value);
            }
        } else {
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }
}