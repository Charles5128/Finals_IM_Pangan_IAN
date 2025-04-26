<?php
$pageTitle = "Dashboard";
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

 
requireLogin();

 
$user = getUserById($_SESSION['user_id']);

 
$stats = getUserStats($_SESSION['user_id']);

 
$recentQuizzes = getRecentQuizResults($_SESSION['user_id']);

 
$subjects = getAllSubjects();

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Welcome, <?php echo $user['username']; ?>!</h1>
            <p class="lead">This is your dashboard where you can track your progress and access quizzes.</p>
        </div>
    </div>
 
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-value"><?php echo $stats['total_quizzes']; ?></div>
                    <div class="stat-title">Quizzes Taken</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-value"><?php echo $stats['average_score']; ?>%</div>
                    <div class="stat-title">Average Score</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-value"><?php echo $stats['highest_score']; ?>%</div>
                    <div class="stat-title">Highest Score</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
      
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Take a Quiz</h5>
                </div>
                <div class="card-body">
                    <form action="quiz.php" method="GET">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Select Subject</label>
                            <select class="form-select" id="subject" name="subject_id" required>
                                <option value="">-- Select Subject --</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?php echo $subject['subject_id']; ?>"><?php echo $subject['subject_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="num_questions" class="form-label">Number of Questions</label>
                            <select class="form-select" id="num_questions" name="num_questions">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="15">15</option>
                                <option value="20">20</option>
                            </select>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Start Quiz</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
 
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Quiz Results</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentQuizzes)): ?>
                        <p class="text-center">You haven't taken any quizzes yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Score</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentQuizzes as $quiz): ?>
                                        <tr>
                                            <td><?php echo $quiz['subject_name']; ?></td>
                                            <td><?php echo $quiz['score']; ?>/<?php echo $quiz['total_questions']; ?> (<?php echo round(($quiz['score'] / $quiz['total_questions']) * 100); ?>%)</td>
                                            <td><?php echo formatDate($quiz['date_taken']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="result.php" class="btn btn-outline-primary btn-sm">View All Results</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
 
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-grid">
                                <a href="questions.php" class="btn btn-outline-primary">
                                    <i class="fas fa-list me-2"></i> Browse Questions
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-grid">
                                <a href="profile.php" class="btn btn-outline-primary">
                                    <i class="fas fa-user me-2"></i> Edit Profile
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-grid">
                                <a href="result.php" class="btn btn-outline-primary">
                                    <i class="fas fa-chart-bar me-2"></i> View Results
                                </a>
                            </div>
                        </div>
                        <?php if (isAdmin()): ?>
                            <div class="col-md-3">
                                <div class="d-grid">
                                    <a href="admin.php" class="btn btn-outline-danger">
                                        <i class="fas fa-cog me-2"></i> Admin Panel
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
