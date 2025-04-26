<?php
$pageTitle = "Browse Questions";
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireLogin();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : null;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : null;

$questions = getQuestions($offset, $limit, $subject_id, $search);
$total_questions = countQuestions($subject_id, $search);
$total_pages = ceil($total_questions / $limit);

$subjects = getAllSubjects();

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Browse Questions</h1>
            <p class="lead">Explore our collection of exam questions to help you prepare.</p>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-8">
            <form action="questions.php" method="GET" id="search-form">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search questions..." name="search" id="search" value="<?php echo $search; ?>">
                    <?php if ($subject_id): ?>
                        <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                    <?php endif; ?>
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
        <div class="col-md-4">
            <form action="questions.php" method="GET">
                <div class="input-group">
                    <select class="form-select" name="subject_id" onchange="this.form.submit()">
                        <option value="">All Subjects</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['subject_id']; ?>" <?php echo $subject_id == $subject['subject_id'] ? 'selected' : ''; ?>>
                                <?php echo $subject['subject_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?php echo $search; ?>">
                    <?php endif; ?>
                    <button class="btn btn-outline-secondary" type="submit">Filter</button>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (empty($questions)): ?>
        <div class="alert alert-info">
            No questions found. Try adjusting your search or filter criteria.
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Questions</span>
                            <span>Total: <?php echo $total_questions; ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="question-card">
                                <div class="question-text">
                                    <span class="badge bg-secondary me-2"><?php echo $question['subject_name']; ?></span>
                                    Q<?php echo ($offset + $index + 1); ?>: <?php echo $question['question_text']; ?>
                                </div>
                                <ul class="options-list">
                                    <li class="<?php echo $question['correct_answer'] === 'A' ? 'correct-answer' : ''; ?>">
                                        A) <?php echo $question['option_a']; ?>
                                    </li>
                                    <li class="<?php echo $question['correct_answer'] === 'B' ? 'correct-answer' : ''; ?>">
                                        B) <?php echo $question['option_b']; ?>
                                    </li>
                                    <li class="<?php echo $question['correct_answer'] === 'C' ? 'correct-answer' : ''; ?>">
                                        C) <?php echo $question['option_c']; ?>
                                    </li>
                                    <li class="<?php echo $question['correct_answer'] === 'D' ? 'correct-answer' : ''; ?>">
                                        D) <?php echo $question['option_d']; ?>
                                    </li>
                                </ul>
                                <?php if ($question['explanation']): ?>
                                    <div class="explanation">
                                        <strong>Explanation:</strong> <?php echo $question['explanation']; ?>
                                    </div>
                                <?php endif; ?>
                                <hr>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <div class="row mt-4">
                <div class="col">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo $subject_id ? '&subject_id=' . $subject_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            if ($start_page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?page=1' . ($subject_id ? '&subject_id=' . $subject_id : '') . ($search ? '&search=' . urlencode($search) : '') . '">1</a></li>';
                                if ($start_page > 2) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++) {
                                echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . ($subject_id ? '&subject_id=' . $subject_id : '') . ($search ? '&search=' . urlencode($search) : '') . '">' . $i . '</a></li>';
                            }
                            
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . ($subject_id ? '&subject_id=' . $subject_id : '') . ($search ? '&search=' . urlencode($search) : '') . '">' . $total_pages . '</a></li>';
                            }
                            ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo $subject_id ? '&subject_id=' . $subject_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
