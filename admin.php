<?php
$pageTitle = "Admin Dashboard";
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdmin();

$pdo = getDbConnection();

$stmt = $pdo->query("SELECT COUNT(*) FROM questions");
$total_questions = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM subjects");
$total_subjects = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM quiz_results");
$total_quizzes = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT q.*, s.subject_name 
    FROM questions q
    JOIN subjects s ON q.subject_id = s.subject_id
    ORDER BY q.question_id DESC 
    LIMIT 5
");
$recent_questions = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Admin Dashboard</h1>
            <p class="lead">Welcome to the admin panel. Manage questions, subjects, and users.</p>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?php echo $total_questions; ?></h3>
                    <p class="mb-0">Total Questions</p>
                </div>
                <div class="card-footer bg-transparent border-0 text-center">
                    <a href="admin/manage_questions.php" class="text-white">View All <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?php echo $total_users; ?></h3>
                    <p class="mb-0">Total Users</p>
                </div>
                <div class="card-footer bg-transparent border-0 text-center">
                    <a href="admin/manage_users.php" class="text-white">View All <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3><?php echo $total_subjects; ?></h3>
                    <p class="mb-0">Total Subjects</p>
                </div>
                <div class="card-footer bg-transparent border-0 text-center">
                    <a href="admin/manage_subjects.php" class="text-white">View All <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3><?php echo $total_quizzes; ?></h3>
                    <p class="mb-0">Quizzes Taken</p>
                </div>
                <div class="card-footer bg-transparent border-0 text-center">
                    <a href="#" class="text-dark">View Stats <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-grid">
                                <a href="admin/add_question.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i> Add Question
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-grid">
                                <a href="admin/manage_subjects.php" class="btn btn-primary">
                                    <i class="fas fa-book me-2"></i> Manage Subjects
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-grid">
                                <a href="admin/manage_users.php" class="btn btn-primary">
                                    <i class="fas fa-users me-2"></i> Manage Users
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-grid">
                                <a href="admin/manage_questions.php" class="btn btn-primary">
                                    <i class="fas fa-list me-2"></i> All Questions
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Users</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Date Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['user_id']; ?></td>
                                        <td><?php echo $user['username']; ?></td>
                                        <td><span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                        <td><?php echo formatDate($user['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="admin/manage_users.php" class="btn btn-outline-primary btn-sm">View All Users</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Questions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Question</th>
                                    <th>Subject</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_questions as $question): ?>
                                    <tr>
                                        <td><?php echo $question['question_id']; ?></td>
                                        <td><?php echo strlen($question['question_text']) > 50 ? substr($question['question_text'], 0, 50) . '...' : $question['question_text']; ?></td>
                                        <td><?php echo $question['subject_name']; ?></td>
                                        <td>
                                            <a href="admin/edit_question.php?id=<?php echo $question['question_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="admin/manage_questions.php" class="btn btn-outline-primary btn-sm">View All Questions</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
