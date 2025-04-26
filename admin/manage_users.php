<?php
$pageTitle = "Manage Users";
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Ensure user is admin
requireAdmin();

// Process user role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = sanitizeInput($_POST['role']);
    
    if ($new_role !== ROLE_ADMIN && $new_role !== ROLE_USER) {
        $_SESSION['error_message'] = "Invalid role specified.";
    } else {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE user_id = :user_id");
        $stmt->bindParam(':role', $new_role, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "User role updated successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to update user role.";
        }
    }
    
    redirect('../admin/manage_users.php');
}

// Process user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Check if attempting to delete self
    if ($user_id === $_SESSION['user_id']) {
        $_SESSION['error_message'] = "You cannot delete your own account.";
        redirect('../admin/manage_users.php');
    }
    
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "User deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to delete user.";
    }
    
    redirect('../admin/manage_users.php');
}

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search parameter
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : null;

// Get users with search and pagination
$pdo = getDbConnection();
$params = [];
$sql = "SELECT * FROM users";

if ($search) {
    $sql .= " WHERE username LIKE :search OR email LIKE :search";
    $params[':search'] = "%$search%";
}

$sql .= " ORDER BY user_id DESC LIMIT :limit OFFSET :offset";
$params[':limit'] = $limit;
$params[':offset'] = $offset;

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$users = $stmt->fetchAll();

// Count total users for pagination
$countSql = "SELECT COUNT(*) FROM users";
if ($search) {
    $countSql .= " WHERE username LIKE :search OR email LIKE :search";
}

$countStmt = $pdo->prepare($countSql);
if ($search) {
    $countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$countStmt->execute();
$total_users = $countStmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Manage Users</h1>
            <p class="lead">View, edit roles, and manage user accounts.</p>
        </div>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
    
    <!-- Search Box -->
    <div class="row mb-4">
        <div class="col-md-6 ms-auto">
            <form action="../admin/manage_users.php" method="GET" id="search-form">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search by username or email..." name="search" id="search" value="<?php echo $search; ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Users List -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <span>Users</span>
                <span>Total: <?php echo $total_users; ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($users)): ?>
                <div class="alert alert-info m-3">No users found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($user['created_at']); ?></td>
                                    <td><?php echo $user['last_login'] ? formatDate($user['last_login']) : 'Never'; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" title="Change Role" data-bs-toggle="modal" data-bs-target="#roleModal<?php echo $user['user_id']; ?>">
                                            <i class="fas fa-user-cog"></i>
                                        </button>
                                        <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
                                            <button type="button" class="btn btn-sm btn-danger" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $user['user_id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <!-- Role Modal -->
                                        <div class="modal fade" id="roleModal<?php echo $user['user_id']; ?>" tabindex="-1" aria-labelledby="roleModalLabel<?php echo $user['user_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="roleModalLabel<?php echo $user['user_id']; ?>">Change Role</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST" action="../admin/manage_users.php">
                                                        <div class="modal-body">
                                                            <p>Change role for user: <strong><?php echo $user['username']; ?></strong></p>
                                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                            <div class="mb-3">
                                                                <label for="role<?php echo $user['user_id']; ?>" class="form-label">Role</label>
                                                                <select class="form-select" id="role<?php echo $user['user_id']; ?>" name="role">
                                                                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="update_role" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Delete Modal -->
                                        <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
                                            <div class="modal fade" id="deleteModal<?php echo $user['user_id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $user['user_id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteModalLabel<?php echo $user['user_id']; ?>">Confirm Deletion</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete the user <strong><?php echo $user['username']; ?></strong>?</p>
                                                            <p class="text-danger">This action cannot be undone!</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <form method="POST" action="../admin/manage_users.php">
                                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                                <button type="submit" name="delete_user" class="btn btn-danger">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="row mt-4">
            <div class="col">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?page=1' . ($search ? '&search=' . urlencode($search) : '') . '">1</a></li>';
                            if ($start_page > 2) {
                                echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                            }
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . '">' . $i . '</a></li>';
                        }
                        
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . ($search ? '&search=' . urlencode($search) : '') . '">' . $total_pages . '</a></li>';
                        }
                        ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
