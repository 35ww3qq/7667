<?php
function check_auth() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    error_log("Checking admin status: " . print_r($_SESSION, true));
    return isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function get_username() {
    return $_SESSION['username'] ?? null;
}

function login($username, $password) {
    global $db;
    
    try {
        $stmt = $db->prepare("SELECT id, password, is_admin FROM users WHERE username = ?");
        if (!$stmt) {
            error_log("Login prepare failed: " . $db->getConnection()->error);
            return false;
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $username;
                $_SESSION['is_admin'] = (bool)$user['is_admin'];
                return true;
            } else {
                error_log("Invalid password for user: $username");
            }
        } else {
            error_log("User not found: $username");
        }
        return false;
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

function logout() {
    session_destroy();
    header('Location: /login.php');
    exit;
} 