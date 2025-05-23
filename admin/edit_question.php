<?php
$pageTitle = "Edit Question";
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

 
requireAdmin();
 
$question_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$question_id) {
    redirect('../admin/manage_questions.php');
}

$error_message = '';
$success_message = '';

 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $subject_id = (int)$_POST['subject_id'];
    $question_text = sanitizeInput($_POST['question_text']);
    $option_a = sanitizeInput($_POST['option_a']);
    $option_b = sanitizeInput($_POST['option_b']);
    $option_c = sanitizeInput($_POST['option_c']);
    $option_d = sanitizeInput($_POST['option_d']);
    $correct_answer = sanitizeInput($_POST['correct_answer']);
    $explanation = sanitizeInput($_POST['explanation']);
    
 
    if (empty($subject_id)) {
        $error_message = "Please select a subject";
    } elseif (empty($question_text)) {
        $error_message = "Question text is required";
    } elseif (empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d)) {
        $error_message = "All options are required";
    } elseif (empty($correct_answer)) {
        $error_message = "Correct answer is required";
    } else {
 
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            UPDATE questions 
            SET subject_id = :subject_id, 
                question_text = :question_text, 
                option_a = :option_a, 
                option_b = :option_b, 
                option_c = :option_c, 
                option_d = :option_d, 
                correct_answer = :correct_answer, 
                explanation = :explanation
            WHERE question_id = :question_id
        ");
        
        $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
        $stmt->bindParam(':question_text', $question_text, PDO::PARAM_STR);
        $stmt->bindParam(':option_a', $option_a, PDO::PARAM_STR);
        $stmt->bindParam(':option_b', $option_b, PDO::PARAM_STR);
        $stmt->bindParam(':option_c', $option_c, PDO::PARAM_STR);
        $stmt->bindParam(':option_d', $option_d, PDO::PARAM_STR);
        $stmt->bindParam(':correct_answer', $correct_answer, PDO::PARAM_STR);
        $stmt->bindParam(':explanation', $explanation, PDO::PARAM_STR);
        $stmt->bindParam(':question_id', $question_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $success_message = "Question updated successfully!";
        } else {
            $error_message = "Error updating question. Please try again.";
        }
    }
}
 
$question = getQuestionById($question_id);

if (!$question) {
    redirect('../admin/manage_questions.php');
}
$subjects = getAllSubjects();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Edit Question</h1>
            <p class="lead">Modify an existing exam question.</p>
        </div>
    </div>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Question Details</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="../admin/edit_question.php?id=<?php echo $question_id; ?>" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="subject_id" class="form-label">Subject</label>
                    <select class="form-select" id="subject_id" name="subject_id" required>
                        <option value="">-- Select Subject --</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['subject_id']; ?>" <?php echo $question['subject_id'] == $subject['subject_id'] ? 'selected' : ''; ?>>
                                <?php echo $subject['subject_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">
                        Please select a subject.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="question_text" class="form-label">Question</label>
                    <textarea class="form-control" id="question_text" name="question_text" rows="3" required><?php echo $question['question_text']; ?></textarea>
                    <div class="invalid-feedback">
                        Please enter the question.
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="option_a" class="form-label">Option A</label>
                        <input type="text" class="form-control" id="option_a" name="option_a" value="<?php echo $question['option_a']; ?>" required>
                        <div class="invalid-feedback">
                            Please enter option A.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="option_b" class="form-label">Option B</label>
                        <input type="text" class="form-control" id="option_b" name="option_b" value="<?php echo $question['option_b']; ?>" required>
                        <div class="invalid-feedback">
                            Please enter option B.
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="option_c" class="form-label">Option C</label>
                        <input type="text" class="form-control" id="option_c" name="option_c" value="<?php echo $question['option_c']; ?>" required>
                        <div class="invalid-feedback">
                            Please enter option C.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="option_d" class="form-label">Option D</label>
                        <input type="text" class="form-control" id="option_d" name="option_d" value="<?php echo $question['option_d']; ?>" required>
                        <div class="invalid-feedback">
                            Please enter option D.
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Correct Answer</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="correct_answer" id="correct_a" value="A" <?php echo $question['correct_answer'] === 'A' ? 'checked' : ''; ?> required>
                            <label class="form-check-label" for="correct_a">A</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="correct_answer" id="correct_b" value="B" <?php echo $question['correct_answer'] === 'B' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="correct_b">B</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="correct_answer" id="correct_c" value="C" <?php echo $question['correct_answer'] === 'C' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="correct_c">C</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="correct_answer" id="correct_d" value="D" <?php echo $question['correct_answer'] === 'D' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="correct_d">D</label>
                        </div>
                        <div class="invalid-feedback">
                            Please select the correct answer.
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="explanation" class="form-label">Explanation (Optional)</label>
                    <textarea class="form-control" id="explanation" name="explanation" rows="3"><?php echo $question['explanation']; ?></textarea>
                    <div class="form-text">Provide an explanation for the correct answer if needed.</div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="../admin/manage_questions.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
