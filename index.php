<?php
require_once 'config/database.php';

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
    
    if ($path === 'admin') {
        include 'public/admin.html';
        exit;
    }
    
    // API Routes
    if (strpos($path, 'api/') === 0) {
        $apiPath = substr($path, 4); // Remove 'api/' prefix
        
        // GET /api/tests - Get all tests
        if ($apiPath === 'tests' && $method === 'GET') {
            $stmt = executeQuery($pdo, 'SELECT * FROM tests ORDER BY created_at DESC');
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
            $input = json_decode(file_get_contents('php://input'), true);
            $testId = $input['test_id'] ?? '';
            $question = $input['question'] ?? '';
            $correctAnswer = $input['correct_answer'] ?? '';
            $explanation = $input['explanation'] ?? '';
            $questionOrder = $input['question_order'] ?? 1;
            
            $stmt = executeQuery($pdo, 
                'INSERT INTO questions (test_id, question, correct_answer, explanation, question_order) VALUES (?, ?, ?, ?, ?)', 
                [$testId, $question, $correctAnswer, $explanation, $questionOrder]
            );
            
            $questionId = $pdo->lastInsertId();
            sendJsonResponse(['id' => $questionId, 'message' => 'Question added successfully']);
        }
        
        // PUT /api/tests/:id - Update test
        if (preg_match('/^tests\/(\d+)$/', $apiPath, $matches) && $method === 'PUT') {
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
            $testId = $matches[1];
            executeQuery($pdo, 'DELETE FROM tests WHERE id = ?', [$testId]);
            sendJsonResponse(['message' => 'Test deleted successfully']);
        }
        
        // PUT /api/questions/:id - Update question
        if (preg_match('/^questions\/(\d+)$/', $apiPath, $matches) && $method === 'PUT') {
            $questionId = $matches[1];
            $input = json_decode(file_get_contents('php://input'), true);
            $question = $input['question'] ?? '';
            $correctAnswer = $input['correct_answer'] ?? '';
            $explanation = $input['explanation'] ?? '';
            $questionOrder = $input['question_order'] ?? 1;
            
            $stmt = executeQuery($pdo, 
                'UPDATE questions SET question = ?, correct_answer = ?, explanation = ?, question_order = ? WHERE id = ?', 
                [$question, $correctAnswer, $explanation, $questionOrder, $questionId]
            );
            
            sendJsonResponse(['message' => 'Question updated successfully']);
        }
        
        // DELETE /api/questions/:id - Delete question
        if (preg_match('/^questions\/(\d+)$/', $apiPath, $matches) && $method === 'DELETE') {
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
    error_log("Error: " . $e->getMessage());
    sendJsonResponse(['error' => $e->getMessage()], 500);
}
?>
