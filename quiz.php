<?php
$pageTitle = "Take Quiz";
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Ensure user is logged in
requireLogin();

// Process quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $subject_id = (int)$_POST['subject_id'];
    $user_answers = isset($_POST['answers']) ? $_POST['answers'] : [];
    $question_ids = $_POST['question_ids'];
    
    $score = 0;
    $total_questions = count($question_ids);
    
    // Calculate score
    foreach ($question_ids as $question_id) {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT correct_answer FROM questions WHERE question_id = :question_id");
        $stmt->bindParam(':question_id', $question_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $question = $stmt->fetch();
        $correct_answer = $question['correct_answer'];
        
        if (isset($user_answers[$question_id]) && $user_answers[$question_id] === $correct_answer) {
            $score++;
        }
    }
    
    // Save quiz result
    if (saveQuizResult($_SESSION['user_id'], $subject_id, $score, $total_questions)) {
        // Redirect to result page
        $_SESSION['quiz_result'] = [
            'score' => $score,
            'total' => $total_questions,
            'percentage' => ($score / $total_questions) * 100,
            'subject_id' => $subject_id
        ];
        
        redirect('result.php?new=1');
    }
}

// Get subject and number of questions from GET parameters
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$num_questions = isset($_GET['num_questions']) ? (int)$_GET['num_questions'] : 10;

// Validate parameters
if ($subject_id <= 0) {
    redirect('dashboard.php');
}

// Get subject information
$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT * FROM subjects WHERE subject_id = :subject_id");
$stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    redirect('dashboard.php');
}

$subject = $stmt->fetch();

// Get random questions for the quiz
$randomFunction = (DB_TYPE === 'pgsql') ? 'RANDOM()' : 'RAND()';
$stmt = $pdo->prepare("
    SELECT * FROM questions 
    WHERE subject_id = :subject_id
    ORDER BY $randomFunction
    LIMIT :limit
");
$stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
$stmt->bindParam(':limit', $num_questions, PDO::PARAM_INT);
$stmt->execute();

$questions = $stmt->fetchAll();

// If not enough questions available, adjust the total
$actual_num_questions = count($questions);

if ($actual_num_questions === 0) {
    $_SESSION['error_message'] = "No questions available for this subject.";
    redirect('dashboard.php');
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1><?php echo $subject['subject_name']; ?> Quiz</h1>
            <p class="lead">Answer the following <?php echo $actual_num_questions; ?> questions.</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Quiz Questions</h5>
                    <div id="quiz-timer" data-time-limit="30" class="badge bg-light text-dark">30:00</div>
                </div>
                <div class="card-body">
                    <form method="POST" action="quiz.php" id="quiz-form">
                        <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                        
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="question-card mb-4">
                                <input type="hidden" name="question_ids[]" value="<?php echo $question['question_id']; ?>">
                                
                                <div class="question-text mb-3">
                                    <span class="badge bg-secondary me-2">Q<?php echo $index + 1; ?></span>
                                    <?php echo $question['question_text']; ?>
                                </div>
                                
                                <div class="options">
                                    <div class="quiz-option" data-question-id="<?php echo $question['question_id']; ?>">
                                        <input type="radio" name="answers[<?php echo $question['question_id']; ?>]" value="A" id="q<?php echo $question['question_id']; ?>_a" class="d-none">
                                        <label for="q<?php echo $question['question_id']; ?>_a" class="d-block w-100">
                                            A) <?php echo $question['option_a']; ?>
                                        </label>
                                    </div>
                                    
                                    <div class="quiz-option" data-question-id="<?php echo $question['question_id']; ?>">
                                        <input type="radio" name="answers[<?php echo $question['question_id']; ?>]" value="B" id="q<?php echo $question['question_id']; ?>_b" class="d-none">
                                        <label for="q<?php echo $question['question_id']; ?>_b" class="d-block w-100">
                                            B) <?php echo $question['option_b']; ?>
                                        </label>
                                    </div>
                                    
                                    <div class="quiz-option" data-question-id="<?php echo $question['question_id']; ?>">
                                        <input type="radio" name="answers[<?php echo $question['question_id']; ?>]" value="C" id="q<?php echo $question['question_id']; ?>_c" class="d-none">
                                        <label for="q<?php echo $question['question_id']; ?>_c" class="d-block w-100">
                                            C) <?php echo $question['option_c']; ?>
                                        </label>
                                    </div>
                                    
                                    <div class="quiz-option" data-question-id="<?php echo $question['question_id']; ?>">
                                        <input type="radio" name="answers[<?php echo $question['question_id']; ?>]" value="D" id="q<?php echo $question['question_id']; ?>_d" class="d-none">
                                        <label for="q<?php echo $question['question_id']; ?>_d" class="d-block w-100">
                                            D) <?php echo $question['option_d']; ?>
                                        </label>
                                    </div>
                                </div>
                                
                                <?php if ($index < $actual_num_questions - 1): ?>
                                    <hr>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center mt-4">
                            <button type="submit" name="submit_quiz" class="btn btn-primary btn-lg">Submit Quiz</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
