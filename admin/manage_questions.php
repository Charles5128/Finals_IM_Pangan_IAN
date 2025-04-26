<?php
$pageTitle = "Manage Questions";
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

 
requireAdmin();

  
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_question'])) {
    $question_id = (int)$_POST['question_id'];
    
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("DELETE FROM questions WHERE question_id = :question_id");
    $stmt->bindParam(':question_id', $question_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Question deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to delete question.";
    }
    
    redirect('../admin/manage_questions.php');
}

 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

 
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : null;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : null;

 
$questions = getQuestions($offset, $limit, $subject_id, $search);
$total_questions = countQuestions($subject_id, $search);
$total_pages = ceil($total_questions / $limit);

 
$subjects = getAllSubjects();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Manage Questions</h1>
            <p class="lead">Add, edit, or delete exam questions.</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="../admin/add_question.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Add New Question
            </a>
        </div>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
    
  
    <div class="row mb-4">
        <div class="col-md-8">
            <form action="../admin/manage_questions.php" method="GET" id="search-form">
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
            <form action="../admin/manage_questions.php" method="GET">
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
       
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <span>Questions</span>
                    <span>Total: <?php echo $total_questions; ?></span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Question</th>
                                <th>Subject</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($questions as $question): ?>
                                <tr>
                                    <td><?php echo $question['question_id']; ?></td>
                                    <td><?php echo strlen($question['question_text']) > 100 ? substr($question['question_text'], 0, 100) . '...' : $question['question_text']; ?></td>
                                    <td><?php echo $question['subject_name']; ?></td>
                                    <td>
                                        <a href="../admin/edit_question.php?id=<?php echo $question['question_id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $question['question_id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        
                                       
                                        <div class="modal fade" id="deleteModal<?php echo $question['question_id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $question['question_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel<?php echo $question['question_id']; ?>">Confirm Deletion</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete this question?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form method="POST" action="../admin/manage_questions.php">
                                                            <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
                                                            <button type="submit" name="delete_question" class="btn btn-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
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

<?php include '../includes/footer.php'; ?>
