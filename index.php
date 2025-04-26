<?php
$pageTitle = "Home";
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get total number of questions
$pdo = getDbConnection();
$stmt = $pdo->query("SELECT COUNT(*) FROM questions");
$totalQuestions = $stmt->fetchColumn();

// Get total number of subjects
$stmt = $pdo->query("SELECT COUNT(*) FROM subjects");
$totalSubjects = $stmt->fetchColumn();

// Get total number of users
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmt->fetchColumn();

include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- Hero Section -->
<div class="hero">
    <div class="container text-center">
        <h1>Welcome to <?php echo APP_NAME; ?></h1>
        <p class="lead">Prepare for your exams with our comprehensive collection of questions and answers.</p>
        <?php if (!isLoggedIn()): ?>
            <div class="d-flex justify-content-center gap-3">
                <a href="register.php" class="btn btn-primary btn-lg">Get Started</a>
                <a href="login.php" class="btn btn-outline-secondary btn-lg">Login</a>
            </div>
        <?php else: ?>
            <a href="dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</div>

<!-- Features Section -->
<div class="container features">
    <h2 class="text-center mb-4">Why Use Our Exam Reviewer?</h2>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <i class="fas fa-book"></i>
                    <h5 class="card-title">Comprehensive Question Bank</h5>
                    <p class="card-text">Access a wide range of questions across multiple subjects to help you prepare thoroughly.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <i class="fas fa-chart-line"></i>
                    <h5 class="card-title">Track Your Progress</h5>
                    <p class="card-text">Monitor your performance and identify areas that need improvement.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <i class="fas fa-clock"></i>
                    <h5 class="card-title">Practice at Your Own Pace</h5>
                    <p class="card-text">Take quizzes whenever you want, as many times as you need to master the material.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Section -->
<div class="container mt-5">
    <h2 class="text-center mb-4">Our Platform at a Glance</h2>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-value"><?php echo number_format($totalQuestions); ?></div>
                    <div class="stat-title">Questions</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-value"><?php echo number_format($totalSubjects); ?></div>
                    <div class="stat-title">Subjects</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-value"><?php echo number_format($totalUsers); ?></div>
                    <div class="stat-title">Users</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- How It Works Section -->
<div class="container mt-5">
    <h2 class="text-center mb-4">How It Works</h2>
    
    <div class="row">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-4 mb-3">1</div>
                    <h5 class="card-title">Create an Account</h5>
                    <p class="card-text">Sign up to get access to all features.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-4 mb-3">2</div>
                    <h5 class="card-title">Browse Questions</h5>
                    <p class="card-text">Explore questions categorized by subject.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-4 mb-3">3</div>
                    <h5 class="card-title">Take Quizzes</h5>
                    <p class="card-text">Test your knowledge with quizzes.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-4 mb-3">4</div>
                    <h5 class="card-title">Track Progress</h5>
                    <p class="card-text">Monitor your performance over time.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="container mt-5">
    <div class="card bg-primary text-white">
        <div class="card-body text-center p-5">
            <h2 class="card-title">Ready to Start Learning?</h2>
            <p class="card-text lead">Join our platform today and take your exam preparation to the next level.</p>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-light btn-lg">Sign Up Now</a>
            <?php else: ?>
                <a href="quiz.php" class="btn btn-light btn-lg">Take a Quiz</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white mt-5 py-4">
    <div class="container text-center">
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All Rights Reserved.</p>
    </div>
</footer>

<?php include 'includes/footer.php'; ?>
