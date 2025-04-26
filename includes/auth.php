<?php
/**
 * Authentication related functions
 */

require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

// Register a new user
function registerUser($username, $email, $password, $confirm_password) {
    $errors = [];
    
    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3 || strlen($username) > 30) {
        $errors[] = "Username must be between 3 and 30 characters";
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    // Confirm password
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // If there are no errors, proceed with registration
    if (empty($errors)) {
        $pdo = getDbConnection();
        
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Username already exists";
            return ['success' => false, 'errors' => $errors];
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already exists";
            return ['success' => false, 'errors' => $errors];
        }
        
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert the new user
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, role, created_at)
            VALUES (:username, :email, :password, :role, NOW())
        ");
        $role = ROLE_USER; // Default role is 'user'
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $role);
        
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            $errors[] = "Registration failed. Please try again.";
            return ['success' => false, 'errors' => $errors];
        }
    }
    
    return ['success' => false, 'errors' => $errors];
}

// Login a user
function loginUser($username, $password) {
    $errors = [];
    
    // Validate inputs
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If there are no errors, proceed with login
    if (empty($errors)) {
        $pdo = getDbConnection();
        
        // Get user from database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Login successful, set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                
                // Update last login time
                $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :user_id");
                $stmt->bindParam(':user_id', $user['user_id']);
                $stmt->execute();
                
                return ['success' => true];
            } else {
                $errors[] = "Invalid password";
            }
        } else {
            $errors[] = "Username not found";
        }
    }
    
    return ['success' => false, 'errors' => $errors];
}

// Logout a user
function logoutUser() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
    
    // Redirect to the login page
    redirect('login.php');
}

// Check if user is logged in, if not redirect to login page
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

// Check if user is admin, if not redirect to dashboard
function requireAdmin() {
    requireLogin();
    
    if (!isAdmin()) {
        redirect('dashboard.php');
    }
}

// Update user profile
function updateProfile($userId, $username, $email, $profileImage = null) {
    $errors = [];
    
    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3 || strlen($username) > 30) {
        $errors[] = "Username must be between 3 and 30 characters";
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // If there are no errors, proceed with update
    if (empty($errors)) {
        $pdo = getDbConnection();
        
        // Check if username already exists for other users
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND user_id != :user_id");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Username already exists";
            return ['success' => false, 'errors' => $errors];
        }
        
        // Check if email already exists for other users
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND user_id != :user_id");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already exists";
            return ['success' => false, 'errors' => $errors];
        }
        
        // Prepare SQL statement
        $sql = "UPDATE users SET username = :username, email = :email";
        $params = [
            ':username' => $username,
            ':email' => $email,
            ':user_id' => $userId
        ];
        
        // Add profile image if provided
        if ($profileImage) {
            $sql .= ", profile_image = :profile_image";
            $params[':profile_image'] = $profileImage;
        }
        
        $sql .= " WHERE user_id = :user_id";
        
        // Execute update
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute($params)) {
            // Update session username
            $_SESSION['username'] = $username;
            return ['success' => true];
        } else {
            $errors[] = "Update failed. Please try again.";
            return ['success' => false, 'errors' => $errors];
        }
    }
    
    return ['success' => false, 'errors' => $errors];
}

// Change user password
function changePassword($userId, $currentPassword, $newPassword, $confirmPassword) {
    $errors = [];
    
    // Validate inputs
    if (empty($currentPassword)) {
        $errors[] = "Current password is required";
    }
    
    if (empty($newPassword)) {
        $errors[] = "New password is required";
    } elseif (strlen($newPassword) < 6) {
        $errors[] = "New password must be at least 6 characters long";
    }
    
    if ($newPassword !== $confirmPassword) {
        $errors[] = "New passwords do not match";
    }
    
    // If there are no errors, proceed with password change
    if (empty($errors)) {
        $pdo = getDbConnection();
        
        // Get current user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $user = $stmt->fetch();
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            $errors[] = "Current password is incorrect";
            return ['success' => false, 'errors' => $errors];
        }
        
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update the password
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE user_id = :user_id");
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':user_id', $userId);
        
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            $errors[] = "Password change failed. Please try again.";
            return ['success' => false, 'errors' => $errors];
        }
    }
    
    return ['success' => false, 'errors' => $errors];
}
?>
