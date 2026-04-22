<?php
// includes/mode_handler.php
session_start();
require_once __DIR__ . '/xml_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buyer_mode'])) {
    $mode = $_POST['buyer_mode'];
    $userId = $_SESSION['user_id'] ?? null;
    
    if (!$userId) {
        header("Location: ../login.php");
        exit;
    }

    if (in_array($mode, ['retail', 'wholesale', 'farmer'])) {
        $xml = loadXml('users');
        $roleUpdated = false;

        // Determine new role based on mode
        $newRole = ($mode === 'farmer') ? 'farmer' : 'customer';

        // Update the XML database with the new role
        foreach ($xml->user as $user) {
            if ((string)$user->id === $userId) {
                if ((string)$user->role !== $newRole) {
                    $user->role = $newRole;
                    $roleUpdated = true;
                }
                break;
            }
        }

        if ($roleUpdated) {
            saveXml('users', $xml);
        }

        // Update session
        $_SESSION['role'] = $newRole;
        $_SESSION['buyer_mode'] = ($mode !== 'farmer') ? $mode : null;

        if ($mode === 'farmer') {
            header("Location: ../dashboard.php");
        } else {
            header("Location: ../marketplace.php");
        }
        exit;
    }
}

// Toggle mode endpoint for customers
if (isset($_GET['action']) && $_GET['action'] === 'toggle_mode') {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer') {
        $current = $_SESSION['buyer_mode'] ?? 'retail';
        $_SESSION['buyer_mode'] = ($current === 'retail') ? 'wholesale' : 'retail';
        header("Location: ../marketplace.php");
        exit;
    }
}

// Fallback
header("Location: ../index.php");
exit;
?>
