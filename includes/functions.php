<?php
/**
 * Common functions used throughout the application
 */

// Clean input data to prevent XSS
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Generate a random string
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Format date to readable format
function formatDate($date) {
    return date('F j, Y, g:i a', strtotime($date));
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_ADMIN;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect to a specific page
function redirect($page) {
    header("Location: $page");
    exit;
}

// Display error message
function displayError($message) {
    return '<div class="alert alert-danger" role="alert">' . $message . '</div>';
}

// Display success message
function displaySuccess($message) {
    return '<div class="alert alert-success" role="alert">' . $message . '</div>';
}

// Get all subjects
function getAllSubjects() {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT * FROM subjects ORDER BY subject_name");
    return $stmt->fetchAll();
}

// Get all questions with pagination
function getQuestions($offset = 0, $limit = 10, $subject_id = null, $search = null) {
    $pdo = getDbConnection();
    
    $params = [];
    $sql = "SELECT q.*, s.subject_name 
            FROM questions q
            JOIN subjects s ON q.subject_id = s.subject_id";
    
    // Add where clause if subject_id is provided
    if ($subject_id) {
        $sql .= " WHERE q.subject_id = :subject_id";
        $params[':subject_id'] = $subject_id;
    }
    
    // Add search condition if search term is provided
    if ($search) {
        if ($subject_id) {
            $sql .= " AND (q.question_text LIKE :search OR q.option_a LIKE :search OR q.option_b LIKE :search OR q.option_c LIKE :search OR q.option_d LIKE :search)";
        } else {
            $sql .= " WHERE (q.question_text LIKE :search OR q.option_a LIKE :search OR q.option_b LIKE :search OR q.option_c LIKE :search OR q.option_d LIKE :search)";
        }
        $params[':search'] = "%$search%";
    }
    
    $sql .= " ORDER BY q.question_id DESC LIMIT :limit OFFSET :offset";
    $params[':limit'] = $limit;
    $params[':offset'] = $offset;
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    
    return $stmt->fetchAll();
}

// Count total questions
function countQuestions($subject_id = null, $search = null) {
    $pdo = getDbConnection();
    
    $params = [];
    $sql = "SELECT COUNT(*) FROM questions q";
    
    // Add where clause if subject_id is provided
    if ($subject_id) {
        $sql .= " WHERE q.subject_id = :subject_id";
        $params[':subject_id'] = $subject_id;
    }
    
    // Add search condition if search term is provided
    if ($search) {
        if ($subject_id) {
            $sql .= " AND (q.question_text LIKE :search OR q.option_a LIKE :search OR q.option_b LIKE :search OR q.option_c LIKE :search OR q.option_d LIKE :search)";
        } else {
            $sql .= " WHERE (q.question_text LIKE :search OR q.option_a LIKE :search OR q.option_b LIKE :search OR q.option_c LIKE :search OR q.option_d LIKE :search)";
        }
        $params[':search'] = "%$search%";
    }
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    
    return $stmt->fetchColumn();
}

// Get a single question by ID
function getQuestionById($id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE question_id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

// Get user by ID
function getUserById($id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

// Get all users (for admin)
function getAllUsers() {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT * FROM users ORDER BY user_id DESC");
    return $stmt->fetchAll();
}

// Get user statistics
function getUserStats($userId) {
    $pdo = getDbConnection();
    
    // Get total quizzes taken
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_results WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $totalQuizzes = $stmt->fetchColumn();
    
    // Get average score
    $stmt = $pdo->prepare("SELECT AVG(score) FROM quiz_results WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $averageScore = $stmt->fetchColumn();
    
    // Get highest score
    $stmt = $pdo->prepare("SELECT MAX(score) FROM quiz_results WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $highestScore = $stmt->fetchColumn();
    
    return [
        'total_quizzes' => $totalQuizzes,
        'average_score' => $averageScore ? round($averageScore, 2) : 0,
        'highest_score' => $highestScore ?: 0
    ];
}

// Get recent quiz results for a user
function getRecentQuizResults($userId, $limit = 5) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        SELECT qr.*, s.subject_name 
        FROM quiz_results qr
        JOIN subjects s ON qr.subject_id = s.subject_id
        WHERE qr.user_id = :user_id
        ORDER BY qr.date_taken DESC
        LIMIT :limit
    ");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Save quiz result
function saveQuizResult($userId, $subjectId, $score, $totalQuestions) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        INSERT INTO quiz_results (user_id, subject_id, score, total_questions, date_taken)
        VALUES (:user_id, :subject_id, :score, :total_questions, NOW())
    ");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':subject_id', $subjectId, PDO::PARAM_INT);
    $stmt->bindParam(':score', $score, PDO::PARAM_INT);
    $stmt->bindParam(':total_questions', $totalQuestions, PDO::PARAM_INT);
    return $stmt->execute();
}
?>
