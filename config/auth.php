<?php
// Authentication configuration and functions

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database-based authentication - no hardcoded credentials!

// Session timeout (30 minutes)
const SESSION_TIMEOUT = 1800;

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
        return false;
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        logout();
        return false;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Authenticate user with username and password
 */
function authenticate($username, $password) {
    require_once __DIR__ . '/database.php';
    $pdo = $GLOBALS['pdo'];
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, password_hash, full_name, is_active FROM admin_users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $updateStmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            $_SESSION['authenticated'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['last_activity'] = time();
            $_SESSION['login_time'] = time();
            return true;
        }
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
    }
    
    return false;
}

/**
 * Logout user
 */
function logout() {
    // Clear all session data
    $_SESSION = array();
    
    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Require authentication - redirect to login if not authenticated
 */
function requireAuth() {
    if (!isAuthenticated()) {
        // If this is an API request, return JSON error
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Authentication required']);
            exit;
        }
        
        // Otherwise redirect to login page
        header('Location: /login');
        exit;
    }
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize input to prevent XSS
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Rate limiting - prevent brute force attacks
 */
function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
    $key = 'rate_limit_' . md5($identifier);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => time()];
    }
    
    $rateLimit = $_SESSION[$key];
    
    // Reset if time window has passed
    if (time() - $rateLimit['first_attempt'] > $timeWindow) {
        $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => time()];
        return true;
    }
    
    // Check if max attempts exceeded
    if ($rateLimit['attempts'] >= $maxAttempts) {
        return false;
    }
    
    return true;
}

/**
 * Record failed login attempt
 */
function recordFailedAttempt($identifier) {
    $key = 'rate_limit_' . md5($identifier);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => time()];
    }
    
    $_SESSION[$key]['attempts']++;
}

/**
 * Get remaining time until rate limit resets
 */
function getRateLimitRemainingTime($identifier, $timeWindow = 300) {
    $key = 'rate_limit_' . md5($identifier);
    
    if (!isset($_SESSION[$key])) {
        return 0;
    }
    
    $rateLimit = $_SESSION[$key];
    $elapsed = time() - $rateLimit['first_attempt'];
    $remaining = $timeWindow - $elapsed;
    
    return max(0, $remaining);
}

/**
 * Generate a secure password hash
 * Use this function to create new password hashes
 */
function generatePasswordHash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify if a password matches a hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Create a new admin user
 */
function createAdminUser($username, $password, $email = null, $fullName = null) {
    require_once __DIR__ . '/database.php';
    $pdo = $GLOBALS['pdo'];
    
    try {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password_hash, email, full_name) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $passwordHash, $email, $fullName]);
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("Error creating admin user: " . $e->getMessage());
        return false;
    }
}

/**
 * Update admin user password
 */
function updateAdminUserPassword($userId, $newPassword) {
    require_once __DIR__ . '/database.php';
    $pdo = $GLOBALS['pdo'];
    
    try {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$passwordHash, $userId]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        error_log("Error updating admin user password: " . $e->getMessage());
        return false;
    }
}

/**
 * Update admin user details
 */
function updateAdminUser($userId, $username = null, $email = null, $fullName = null, $isActive = null) {
    require_once __DIR__ . '/database.php';
    $pdo = $GLOBALS['pdo'];
    
    try {
        $fields = [];
        $values = [];
        
        if ($username !== null) {
            $fields[] = "username = ?";
            $values[] = $username;
        }
        if ($email !== null) {
            $fields[] = "email = ?";
            $values[] = $email;
        }
        if ($fullName !== null) {
            $fields[] = "full_name = ?";
            $values[] = $fullName;
        }
        if ($isActive !== null) {
            $fields[] = "is_active = ?";
            $values[] = $isActive ? 1 : 0;
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = "updated_at = NOW()";
        $values[] = $userId;
        
        $sql = "UPDATE admin_users SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        error_log("Error updating admin user: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all admin users
 */
function getAllAdminUsers() {
    require_once __DIR__ . '/database.php';
    $pdo = $GLOBALS['pdo'];
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, full_name, is_active, last_login, created_at FROM admin_users ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting admin users: " . $e->getMessage());
        return [];
    }
}

/**
 * Get admin user by ID
 */
function getAdminUserById($userId) {
    require_once __DIR__ . '/database.php';
    $pdo = $GLOBALS['pdo'];
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, full_name, is_active, last_login, created_at FROM admin_users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting admin user: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete admin user
 */
function deleteAdminUser($userId) {
    require_once __DIR__ . '/database.php';
    $pdo = $GLOBALS['pdo'];
    
    try {
        // Don't allow deleting the last admin user
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE is_active = 1");
        $countStmt->execute();
        $activeCount = $countStmt->fetchColumn();
        
        if ($activeCount <= 1) {
            return false; // Cannot delete the last active admin
        }
        
        $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        error_log("Error deleting admin user: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if username exists
 */
function usernameExists($username, $excludeUserId = null) {
    require_once __DIR__ . '/database.php';
    $pdo = $GLOBALS['pdo'];
    
    try {
        $sql = "SELECT COUNT(*) FROM admin_users WHERE username = ?";
        $params = [$username];
        
        if ($excludeUserId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeUserId;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Error checking username: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if any admin users exist
 */
function hasAdminUsers() {
    require_once __DIR__ . '/database.php';
    $pdo = $GLOBALS['pdo'];
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users");
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Error checking admin users: " . $e->getMessage());
        return false;
    }
}
?>
