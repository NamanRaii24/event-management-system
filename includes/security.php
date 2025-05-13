<?php
require_once __DIR__ . '/../config.php';

class Security {
    public static function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 1);
        
        // Set session lifetime
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
        session_set_cookie_params(SESSION_LIFETIME);
    }

    public static function validatePassword($password) {
        $errors = [];
        
        if (strlen($password) < MIN_PASSWORD_LENGTH) {
            $errors[] = "Password must be at least " . MIN_PASSWORD_LENGTH . " characters long";
        }
        
        if (REQUIRE_SPECIAL_CHARS && !preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        if (REQUIRE_NUMBERS && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        if (REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        return $errors;
    }

    public static function checkLoginAttempts($email) {
        global $conn;
        
        $stmt = $conn->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $attempt = $result->fetch_assoc();
            
            // Check if timeout has passed
            if (time() - strtotime($attempt['last_attempt']) > LOGIN_TIMEOUT) {
                // Reset attempts
                $stmt = $conn->prepare("UPDATE login_attempts SET attempts = 0 WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                return true;
            }
            
            // Check if max attempts reached
            if ($attempt['attempts'] >= MAX_LOGIN_ATTEMPTS) {
                return false;
            }
        }
        
        return true;
    }

    public static function recordLoginAttempt($email, $success) {
        global $conn;
        
        if ($success) {
            // Clear attempts on successful login
            $stmt = $conn->prepare("DELETE FROM login_attempts WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
        } else {
            // Increment attempts
            $stmt = $conn->prepare("INSERT INTO login_attempts (email, attempts, last_attempt) 
                                  VALUES (?, 1, NOW()) 
                                  ON DUPLICATE KEY UPDATE 
                                  attempts = attempts + 1, 
                                  last_attempt = NOW()");
            $stmt->bind_param("s", $email);
            $stmt->execute();
        }
    }

    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            throw new Exception("CSRF token validation failed");
        }
        return true;
    }

    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    public static function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }
    }

    public static function requireRole($role) {
        self::requireLogin();
        if ($_SESSION['role'] !== $role) {
            header("Location: index.php");
            exit();
        }
    }
}
?> 