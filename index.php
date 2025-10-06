<?php
require_once 'config/database.php';
require_once 'config/auth.php';

// Get the request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Remove leading slash and get path segments
$path = ltrim($path, '/');
$segments = explode('/', $path);

// Set content type for API responses
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Handle CORS for API requests
if ($method === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit;
}

header('Access-Control-Allow-Origin: *');

// Route handling
try {
    // Serve static HTML pages
    if ($path === '' || $path === 'index.php') {
        include 'public/index.html';
        exit;
    }
    
    if ($path === 'test') {
        include 'public/test.html';
        exit;
    }
    
    if ($path === 'results') {
        include 'public/results.html';
        exit;
    }
    
    if ($path === 'login') {
        include 'public/login.html';
        exit;
    }
    
    if ($path === 'install') {
        include 'install.php';
        exit;
    }
    
    if ($path === 'setup') {
        // Check if any admin users exist
        if (hasAdminUsers()) {
            // If admin users exist, redirect to login
            header('Location: /login');
            exit;
        }
        include 'public/setup.html';
        exit;
    }
    
    if ($path === 'admin') {
        // Check if any admin users exist
        if (!hasAdminUsers()) {
            // If no admin users exist, redirect to setup
            header('Location: /setup');
            exit;
        }
        requireAuth(); // Require authentication for admin panel
        include 'public/admin.html';
        exit;
    }
    
    // API Routes
    if (strpos($path, 'api/') === 0) {
        $apiPath = substr($path, 4); // Remove 'api/' prefix
        
        // Authentication routes
        if ($apiPath === 'auth/login' && $method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $username = sanitizeInput($input['username'] ?? '');
            $password = $input['password'] ?? '';
            
            // Check rate limiting
            $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            if (!checkRateLimit($clientIP)) {
                $remainingTime = getRateLimitRemainingTime($clientIP);
                sendJsonResponse(['error' => "Te veel inlogpogingen. Probeer het over {$remainingTime} seconden opnieuw."], 429);
            }
            
            if (authenticate($username, $password)) {
                sendJsonResponse(['success' => true, 'message' => 'Succesvol ingelogd']);
            } else {
                recordFailedAttempt($clientIP);
                sendJsonResponse(['error' => 'Ongeldige gebruikersnaam of wachtwoord'], 401);
            }
        }
        
        if ($apiPath === 'auth/logout' && $method === 'POST') {
            logout();
            sendJsonResponse(['success' => true, 'message' => 'Succesvol uitgelogd']);
        }
        
        if ($apiPath === 'auth/check' && $method === 'GET') {
            if (isAuthenticated()) {
                sendJsonResponse(['authenticated' => true, 'username' => $_SESSION['username']]);
            } else {
                sendJsonResponse(['authenticated' => false], 401);
            }
        }
        
        // Setup route for creating first admin user
        if ($apiPath === 'setup/create-admin' && $method === 'POST') {
            // Only allow if no admin users exist
            if (hasAdminUsers()) {
                sendJsonResponse(['error' => 'Admin users already exist'], 403);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $username = sanitizeInput($input['username'] ?? '');
            $password = $input['password'] ?? '';
            $email = sanitizeInput($input['email'] ?? '');
            $fullName = sanitizeInput($input['fullName'] ?? '');
            
            // Validate input
            if (empty($username) || empty($password)) {
                sendJsonResponse(['error' => 'Gebruikersnaam en wachtwoord zijn verplicht'], 400);
            }
            
            if (strlen($password) < 6) {
                sendJsonResponse(['error' => 'Wachtwoord moet minimaal 6 karakters lang zijn'], 400);
            }
            
            // Create the first admin user
            $userId = createAdminUser($username, $password, $email, $fullName);
            
            if ($userId) {
                // Automatically log in the new user
                authenticate($username, $password);
                sendJsonResponse(['success' => true, 'message' => 'Admin gebruiker succesvol aangemaakt']);
            } else {
                sendJsonResponse(['error' => 'Fout bij het aanmaken van admin gebruiker'], 500);
            }
        }
        
        // Helper function to check admin authentication for API routes
        function requireAdminAuth() {
            if (!isAuthenticated()) {
                sendJsonResponse(['error' => 'Admin authentication required'], 401);
            }
        }
        
        // GET /api/tests - Get all tests
        if ($apiPath === 'tests' && $method === 'GET') {
            $stmt = executeQuery($pdo, 'SELECT * FROM tests ORDER BY created_at DESC');
            $tests = $stmt->fetchAll();
            sendJsonResponse($tests);
        }
        
        // GET /api/tests/stats - Get test statistics
        if ($apiPath === 'tests/stats' && $method === 'GET') {
            $stmt = executeQuery($pdo, '
                SELECT 
                    t.id,
                    t.title,
                    t.description,
                    t.created_at,
                    COUNT(q.id) as question_count
                FROM tests t
                LEFT JOIN questions q ON t.id = q.test_id
                GROUP BY t.id, t.title, t.description, t.created_at
                ORDER BY t.created_at DESC
            ');
            $tests = $stmt->fetchAll();
            sendJsonResponse($tests);
        }
        
        // GET /api/tests/:id - Get single test
        if (preg_match('/^tests\/(\d+)$/', $apiPath, $matches) && $method === 'GET') {
            $testId = $matches[1];
            $stmt = executeQuery($pdo, 'SELECT * FROM tests WHERE id = ?', [$testId]);
            $test = $stmt->fetch();
            
            if (!$test) {
                sendJsonResponse(['error' => 'Test not found'], 404);
            }
            
            sendJsonResponse($test);
        }
        
        // GET /api/tests/:id/questions - Get questions for a test
        if (preg_match('/^tests\/(\d+)\/questions$/', $apiPath, $matches) && $method === 'GET') {
            $testId = $matches[1];
            $stmt = executeQuery($pdo, 
                'SELECT * FROM questions WHERE test_id = ? ORDER BY question_order', 
                [$testId]
            );
            $questions = $stmt->fetchAll();
            sendJsonResponse($questions);
        }
        
        // POST /api/tests - Create new test
        if ($apiPath === 'tests' && $method === 'POST') {
            requireAdminAuth();
            $input = json_decode(file_get_contents('php://input'), true);
            $title = $input['title'] ?? '';
            $description = $input['description'] ?? '';
            
            $stmt = executeQuery($pdo, 
                'INSERT INTO tests (title, description) VALUES (?, ?)', 
                [$title, $description]
            );
            
            $testId = $pdo->lastInsertId();
            sendJsonResponse(['id' => $testId, 'message' => 'Test created successfully']);
        }
        
        // POST /api/questions - Create new question
        if ($apiPath === 'questions' && $method === 'POST') {
            requireAdminAuth();
            $input = json_decode(file_get_contents('php://input'), true);
            $testId = $input['test_id'] ?? '';
            $question = $input['question'] ?? '';
            $correctAnswer = $input['correct_answer'] ?? '';
            $explanation = $input['explanation'] ?? '';
            $questionOrder = max(1, intval($input['question_order'] ?? 1));
            
            $stmt = executeQuery($pdo, 
                'INSERT INTO questions (test_id, question, correct_answer, explanation, question_order) VALUES (?, ?, ?, ?, ?)', 
                [$testId, $question, $correctAnswer, $explanation, $questionOrder]
            );
            
            $questionId = $pdo->lastInsertId();
            sendJsonResponse(['id' => $questionId, 'message' => 'Question added successfully']);
        }
        
        // PUT /api/tests/:id - Update test
        if (preg_match('/^tests\/(\d+)$/', $apiPath, $matches) && $method === 'PUT') {
            requireAdminAuth();
            $testId = $matches[1];
            $input = json_decode(file_get_contents('php://input'), true);
            $title = $input['title'] ?? '';
            $description = $input['description'] ?? '';
            
            $stmt = executeQuery($pdo, 
                'UPDATE tests SET title = ?, description = ? WHERE id = ?', 
                [$title, $description, $testId]
            );
            
            sendJsonResponse(['message' => 'Test updated successfully']);
        }
        
        // DELETE /api/tests/:id - Delete test
        if (preg_match('/^tests\/(\d+)$/', $apiPath, $matches) && $method === 'DELETE') {
            requireAdminAuth();
            $testId = $matches[1];
            executeQuery($pdo, 'DELETE FROM tests WHERE id = ?', [$testId]);
            sendJsonResponse(['message' => 'Test deleted successfully']);
        }
        
        // PUT /api/questions/:id - Update question
        if (preg_match('/^questions\/(\d+)$/', $apiPath, $matches) && $method === 'PUT') {
            requireAdminAuth();
            $questionId = $matches[1];
            $input = json_decode(file_get_contents('php://input'), true);
            $question = $input['question'] ?? '';
            $correctAnswer = $input['correct_answer'] ?? '';
            $explanation = $input['explanation'] ?? '';
            $questionOrder = max(1, intval($input['question_order'] ?? 1));
            
            $stmt = executeQuery($pdo, 
                'UPDATE questions SET question = ?, correct_answer = ?, explanation = ?, question_order = ? WHERE id = ?', 
                [$question, $correctAnswer, $explanation, $questionOrder, $questionId]
            );
            
            sendJsonResponse(['message' => 'Question updated successfully']);
        }
        
        // DELETE /api/questions/:id - Delete question
        if (preg_match('/^questions\/(\d+)$/', $apiPath, $matches) && $method === 'DELETE') {
            requireAdminAuth();
            $questionId = $matches[1];
            executeQuery($pdo, 'DELETE FROM questions WHERE id = ?', [$questionId]);
            sendJsonResponse(['message' => 'Question deleted successfully']);
        }
        
        // If no API route matches
        sendJsonResponse(['error' => 'API endpoint not found'], 404);
    }
    
    // If no route matches, show 404
    http_response_code(404);
    echo "Page not found";
    
} catch (Exception $e) {
    error_log("Error in " . __FILE__ . " at line " . __LINE__ . ": " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Don't expose internal errors in production
    $errorMessage = 'Er is een onverwachte fout opgetreden. Probeer het later opnieuw.';
    if (defined('DEBUG') && DEBUG) {
        $errorMessage = $e->getMessage();
    }
    
    sendJsonResponse(['error' => $errorMessage], 500);
}
?>
