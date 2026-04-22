<?php
// auth_handler.php
session_start();
require_once __DIR__ . '/xml_db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (isLoggedIn()) {
        return findUserById($_SESSION['user_id']);
    }
    return null;
}

function requireAuth($role = null) {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
    if ($role) {
        $user = getCurrentUser();
        if ((string)$user->role !== $role) {
            header("Location: index.php");
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'register') {
        $name = htmlspecialchars($_POST['name']);
        $email = htmlspecialchars($_POST['email']);
        $password = $_POST['password'];
        $role = 'unassigned'; // Default role, user will choose during onboarding
        $location = htmlspecialchars($_POST['location']);

        if (findUserByEmail($email)) {
            $_SESSION['error'] = "Email already exists!";
            header("Location: ../register.php");
            exit;
        }

        $xml = loadXml('users');
        $user = $xml->addChild('user');
        $user->addChild('id', uniqid('u_'));
        $user->addChild('name', $name);
        $user->addChild('email', $email);
        $user->addChild('password', password_hash($password, PASSWORD_DEFAULT));
        $user->addChild('role', $role);
        $user->addChild('location', $location);
        $user->addChild('rating', '5.0');
        
        saveXml('users', $xml);
        
        // Auto-login the user
        $_SESSION['user_id'] = (string)$user->id;
        $_SESSION['role'] = (string)$user->role;
        $_SESSION['email'] = (string)$user->email;
        
        // Redirect to onboarding directly
        header("Location: ../onboarding.php");
        exit;
    }

    if ($_POST['action'] === 'login') {
        $email = htmlspecialchars($_POST['email']);
        $password = $_POST['password'];

        $user = findUserByEmail($email);
        if ($user && password_verify($password, (string)$user->password)) {
            $_SESSION['user_id'] = (string)$user->id;
            $_SESSION['role'] = (string)$user->role;
            // ALWAYS redirect to onboarding.php after login per new requirements
            header("Location: ../onboarding.php");
            exit;
        } else {
            $_SESSION['error'] = "Invalid credentials!";
            header("Location: ../login.php");
            exit;
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: ../index.php");
    exit;
}
?>
