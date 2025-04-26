<?php
$pageTitle = "My Profile";
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Ensure user is logged in
requireLogin();

// Get user data
$user = getUserById($_SESSION['user_id']);

$success_message = '';
$error_message = '';

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = sanitizeInput($_POST['username']);
        $email = sanitizeInput($_POST['email']);
        
        // Handle profile image upload
        $profile_image = $user['profile_image']; // Default to current image
        
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
                $error_message = "Only JPG, PNG, and GIF files are allowed";
            } elseif ($_FILES['profile_image']['size'] > $max_size) {
                $error_message = "File size should be less than 2MB";
            } else {
                // Create uploads directory if it doesn't exist
                if (!file_exists(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0777, true);
                }
                
                // Generate unique filename
                $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $new_filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
                $upload_path = UPLOAD_DIR . $new_filename;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                    // Delete old profile image if exists
                    if ($user['profile_image'] && file_exists(UPLOAD_DIR . $user['profile_image'])) {
                        unlink(UPLOAD_DIR . $user['profile_image']);
                    }
                    
                    $profile_image = $new_filename;
                } else {
                    $error_message = "Failed to upload file";
                }
            }
        }
        
        if (empty($error_message)) {
            $result = updateProfile($_SESSION['user_id'], $username, $email, $profile_image);
            
            if ($result['success']) {
                $success_message = "Profile updated successfully";
                // Refresh user data
                $user = getUserById($_SESSION['user_id']);
            } else {
                $error_message = implode('<br>', $result['errors']);
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        $result = changePassword($_SESSION['user_id'], $current_password, $new_password, $confirm_password);
        
        if ($result['success']) {
            $success_message = "Password changed successfully";
        } else {
            $error_message = implode('<br>', $result['errors']);
        }
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>My Profile</h1>
            <p class="lead">Manage your account information and preferences.</p>
        </div>
    </div>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="profile-image-container">
                        <?php if ($user['profile_image']): ?>
                            <img src="uploads/<?php echo $user['profile_image']; ?>" alt="Profile Image" class="profile-image">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/150" alt="Default Profile" class="profile-image">
                        <?php endif; ?>
                    </div>
                    <h4><?php echo $user['username']; ?></h4>
                    <p class="text-muted"><?php echo ucfirst($user['role']); ?></p>
                    <p><i class="fas fa-envelope me-2"></i><?php echo $user['email']; ?></p>
                    <p><i class="fas fa-calendar me-2"></i>Joined: <?php echo formatDate($user['created_at']); ?></p>
                    <p><i class="fas fa-clock me-2"></i>Last Login: <?php echo formatDate($user['last_login']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Update Profile</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="profile.php" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo $user['username']; ?>" required>
                            <div class="invalid-feedback">
                                Please enter a username.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image">
                            <div class="form-text">Allowed formats: JPG, PNG, GIF. Max size: 2MB.</div>
                        </div>
                        
                        <?php if ($user['profile_image']): ?>
                            <div class="mb-3">
                                <label class="form-label">Current Image Preview</label>
                                <div>
                                    <img src="uploads/<?php echo $user['profile_image']; ?>" alt="Current Profile" id="profile_image_preview" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5>Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="profile.php" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">
                                    Please enter your current password.
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#new_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">
                                    Please enter a new password.
                                </div>
                            </div>
                            <div class="form-text">Password must be at least 6 characters long.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#confirm_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">
                                    Please confirm your new password.
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
