<?php

require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

function registerUser($username, $email, $password, $confirm_password) {
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3 || strlen($username) > 30) {
        $errors[] = "Username must be between 3 and 30 characters";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        $pdo = getDbConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Username already exists";
            return ['success' => false, 'errors' => $errors];
        }
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already exists";
            return ['success' => false, 'errors' => $errors];
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, role, created_at)
            VALUES (:username, :email, :password, :role, NOW())
        ");
        $role = ROLE_USER;
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

function loginUser($username, $password) {
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    if (empty($errors)) {
        $pdo = getDbConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                
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

function logoutUser() {
    $_SESSION = [];
    session_destroy();
    redirect('login.php');
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function requireAdmin() {
    requireLogin();
    
    if (!isAdmin()) {
        redirect('dashboard.php');
    }
}

function updateProfile($userId, $username, $email, $profileImage = null) {
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3 || strlen($username) > 30) {
        $errors[] = "Username must be between 3 and 30 characters";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($errors)) {
        $pdo = getDbConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND user_id != :user_id");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Username already exists";
            return ['success' => false, 'errors' => $errors];
        }
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND user_id != :user_id");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already exists";
            return ['success' => false, 'errors' => $errors];
        }
        
        $sql = "UPDATE users SET username = :username, email = :email";
        $params = [
            ':username' => $username,
            ':email' => $email,
            ':user_id' => $userId
        ];
        
        if ($profileImage) {
            $sql .= ", profile_image = :profile_image";
            $params[':profile_image'] = $profileImage;
        }
        
        $sql .= " WHERE user_id = :user_id";
        
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute($params)) {
            $_SESSION['username'] = $username;
            return ['success' => true];
        } else {
            $errors[] = "Update failed. Please try again.";
            return ['success' => false, 'errors' => $errors];
        }
    }
    
    return ['success' => false, 'errors' => $errors];
}

function changePassword($userId, $currentPassword, $newPassword, $confirmPassword) {
    $errors = [];
    
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
    
    if (empty($errors)) {
        $pdo = getDbConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $user = $stmt->fetch();
        
        if (!password_verify($currentPassword, $user['password'])) {
            $errors[] = "Current password is incorrect";
            return ['success' => false, 'errors' => $errors];
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
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
