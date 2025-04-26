<?php
$pageTitle = "Manage Subjects";
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
 
requireAdmin();
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $subject_name = sanitizeInput($_POST['subject_name']);
    $subject_description = sanitizeInput($_POST['subject_description']);
    
    if (empty($subject_name)) {
        $_SESSION['error_message'] = "Subject name is required.";
    } else {
        $pdo = getDbConnection();
 
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE LOWER(subject_name) = LOWER(:subject_name)");
        $stmt->bindParam(':subject_name', $subject_name, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error_message'] = "Subject already exists.";
        } else {
          
            $stmt = $pdo->prepare("
                INSERT INTO subjects (subject_name, description)
                VALUES (:subject_name, :description)
            ");
            $stmt->bindParam(':subject_name', $subject_name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $subject_description, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Subject added successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to add subject.";
            }
        }
    }
    
    redirect('../admin/manage_subjects.php');
}

 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_subject'])) {
    $subject_id = (int)$_POST['subject_id'];
    $subject_name = sanitizeInput($_POST['subject_name']);
    $subject_description = sanitizeInput($_POST['subject_description']);
    
    if (empty($subject_name)) {
        $_SESSION['error_message'] = "Subject name is required.";
    } else {
        $pdo = getDbConnection();
        
     
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM subjects 
            WHERE LOWER(subject_name) = LOWER(:subject_name) 
            AND subject_id != :subject_id
        ");
        $stmt->bindParam(':subject_name', $subject_name, PDO::PARAM_STR);
        $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error_message'] = "Subject name already exists.";
        } else {
        
            $stmt = $pdo->prepare("
                UPDATE subjects 
                SET subject_name = :subject_name, description = :description
                WHERE subject_id = :subject_id
            ");
            $stmt->bindParam(':subject_name', $subject_name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $subject_description, PDO::PARAM_STR);
            $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Subject updated successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to update subject.";
            }
        }
    }
    
    redirect('../admin/manage_subjects.php');
}

 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_subject'])) {
    $subject_id = (int)$_POST['subject_id'];
    
    $pdo = getDbConnection();
    
  
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE subject_id = :subject_id");
    $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['error_message'] = "Cannot delete subject. There are questions associated with this subject.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM subjects WHERE subject_id = :subject_id");
        $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Subject deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to delete subject.";
        }
    }
    
    redirect('../admin/manage_subjects.php');
}

 
$pdo = getDbConnection();
$stmt = $pdo->query("SELECT s.*, (SELECT COUNT(*) FROM questions q WHERE q.subject_id = s.subject_id) as question_count FROM subjects s ORDER BY s.subject_name");
$subjects = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Manage Subjects</h1>
            <p class="lead">Add, edit, or delete subjects for exam questions.</p>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                <i class="fas fa-plus me-2"></i> Add New Subject
            </button>
        </div>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
    
   
    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <span>Subjects</span>
                <span>Total: <?php echo count($subjects); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($subjects)): ?>
                <div class="alert alert-info m-3">No subjects found. Add a new subject to get started.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Subject Name</th>
                                <th>Description</th>
                                <th>Questions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjects as $subject): ?>
                                <tr>
                                    <td><?php echo $subject['subject_id']; ?></td>
                                    <td><?php echo $subject['subject_name']; ?></td>
                                    <td><?php echo $subject['description'] ? $subject['description'] : '<em>No description</em>'; ?></td>
                                    <td><?php echo $subject['question_count']; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" title="Edit" data-bs-toggle="modal" data-bs-target="#editSubjectModal<?php echo $subject['subject_id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteSubjectModal<?php echo $subject['subject_id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        
                                        
                                        <div class="modal fade" id="editSubjectModal<?php echo $subject['subject_id']; ?>" tabindex="-1" aria-labelledby="editSubjectModalLabel<?php echo $subject['subject_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editSubjectModalLabel<?php echo $subject['subject_id']; ?>">Edit Subject</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST" action="../admin/manage_subjects.php">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="subject_id" value="<?php echo $subject['subject_id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label for="subject_name<?php echo $subject['subject_id']; ?>" class="form-label">Subject Name</label>
                                                                <input type="text" class="form-control" id="subject_name<?php echo $subject['subject_id']; ?>" name="subject_name" value="<?php echo $subject['subject_name']; ?>" required>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="subject_description<?php echo $subject['subject_id']; ?>" class="form-label">Description</label>
                                                                <textarea class="form-control" id="subject_description<?php echo $subject['subject_id']; ?>" name="subject_description" rows="3"><?php echo $subject['description']; ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="update_subject" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                      
                                        <div class="modal fade" id="deleteSubjectModal<?php echo $subject['subject_id']; ?>" tabindex="-1" aria-labelledby="deleteSubjectModalLabel<?php echo $subject['subject_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteSubjectModalLabel<?php echo $subject['subject_id']; ?>">Confirm Deletion</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to delete the subject "<strong><?php echo $subject['subject_name']; ?></strong>"?</p>
                                                        
                                                        <?php if ($subject['question_count'] > 0): ?>
                                                            <div class="alert alert-warning">
                                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                                This subject has <?php echo $subject['question_count']; ?> question(s) associated with it. You cannot delete it until all associated questions are removed.
                                                            </div>
                                                        <?php else: ?>
                                                            <p class="text-danger">This action cannot be undone!</p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form method="POST" action="../admin/manage_subjects.php">
                                                            <input type="hidden" name="subject_id" value="<?php echo $subject['subject_id']; ?>">
                                                            <button type="submit" name="delete_subject" class="btn btn-danger" <?php echo $subject['question_count'] > 0 ? 'disabled' : ''; ?>>Delete</button>
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
            <?php endif; ?>
        </div>
    </div>
</div>

 
<div class="modal fade" id="addSubjectModal" tabindex="-1" aria-labelledby="addSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSubjectModalLabel">Add New Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="../admin/manage_subjects.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="subject_name" class="form-label">Subject Name</label>
                        <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject_description" class="form-label">Description</label>
                        <textarea class="form-control" id="subject_description" name="subject_description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_subject" class="btn btn-primary">Add Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
