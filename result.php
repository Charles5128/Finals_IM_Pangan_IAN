<?php
$pageTitle = "Quiz Results";
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireLogin();

$is_new_result = isset($_GET['new']) && $_GET['new'] == 1 && isset($_SESSION['quiz_result']);

$user = getUserById($_SESSION['user_id']);

$pdo = getDbConnection();

if ($is_new_result) {
    $quiz_result = $_SESSION['quiz_result'];
    
    $stmt = $pdo->prepare("SELECT subject_name FROM subjects WHERE subject_id = :subject_id");
    $stmt->bindParam(':subject_id', $quiz_result['subject_id'], PDO::PARAM_INT);
    $stmt->execute();
    $subject = $stmt->fetch();
    $quiz_result['subject_name'] = $subject['subject_name'];
    
    unset($_SESSION['quiz_result']);
} else {
    $stmt = $pdo->prepare("
        SELECT qr.*, s.subject_name 
        FROM quiz_results qr
        JOIN subjects s ON qr.subject_id = s.subject_id
        WHERE qr.user_id = :user_id
        ORDER BY qr.date_taken DESC
    ");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $all_results = $stmt->fetchAll();
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container">
    <?php if ($is_new_result): ?>
        <div class="row mb-4">
            <div class="col">
                <h1>Quiz Result</h1>
                <p class="lead">You have completed the <?php echo $quiz_result['subject_name']; ?> quiz.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Your Score</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="display-1 mb-3">
                            <?php echo round($quiz_result['percentage']); ?>%
                        </div>
                        <p class="lead">You answered <?php echo $quiz_result['score']; ?> out of <?php echo $quiz_result['total']; ?> questions correctly.</p>
                        
                        <div class="progress mb-4" style="height: 30px;">
                            <div class="progress-bar bg-<?php 
                                if ($quiz_result['percentage'] >= 80) echo 'success';
                                elseif ($quiz_result['percentage'] >= 60) echo 'primary';
                                elseif ($quiz_result['percentage'] >= 40) echo 'warning';
                                else echo 'danger';
                            ?>" role="progressbar" style="width: <?php echo $quiz_result['percentage']; ?>%" aria-valuenow="<?php echo $quiz_result['percentage']; ?>" aria-valuemin="0" aria-valuemax="100">
                                <?php echo round($quiz_result['percentage']); ?>%
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5>Performance Rating</h5>
                            <p>
                                <?php 
                                if ($quiz_result['percentage'] >= 90) echo 'Excellent! You have mastered this subject.';
                                elseif ($quiz_result['percentage'] >= 80) echo 'Very good! You have a strong understanding of this subject.';
                                elseif ($quiz_result['percentage'] >= 70) echo 'Good! You have a good grasp of this subject.';
                                elseif ($quiz_result['percentage'] >= 60) echo 'Satisfactory. You understand most of the concepts.';
                                elseif ($quiz_result['percentage'] >= 50) echo 'Fair. You need to review some concepts.';
                                else echo 'Needs improvement. You should consider revisiting this subject.';
                                ?>
                            </p>
                        </div>
                        
                        <div class="d-flex justify-content-center gap-3">
                            <a href="quiz.php?subject_id=<?php echo $quiz_result['subject_id']; ?>&num_questions=<?php echo $quiz_result['total']; ?>" class="btn btn-primary">
                                <i class="fas fa-redo me-2"></i> Retake Quiz
                            </a>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-home me-2"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row mb-4">
            <div class="col">
                <h1>My Quiz Results</h1>
                <p class="lead">View your performance history across all quizzes.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col">
                <?php if (empty($all_results)): ?>
                    <div class="alert alert-info">
                        You haven't taken any quizzes yet. <a href="dashboard.php">Go to the dashboard</a> to start a quiz.
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Quiz History</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Subject</th>
                                            <th>Score</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_results as $result): ?>
                                            <tr>
                                                <td><?php echo formatDate($result['date_taken']); ?></td>
                                                <td><?php echo $result['subject_name']; ?></td>
                                                <td><?php echo $result['score']; ?>/<?php echo $result['total_questions']; ?></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <?php $percentage = ($result['score'] / $result['total_questions']) * 100; ?>
                                                        <div class="progress-bar bg-<?php 
                                                            if ($percentage >= 80) echo 'success';
                                                            elseif ($percentage >= 60) echo 'primary';
                                                            elseif ($percentage >= 40) echo 'warning';
                                                            else echo 'danger';
                                                        ?>" role="progressbar" style="width: <?php echo $percentage; ?>%" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                                            <?php echo round($percentage); ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
