<?xml version="1.0" encoding="UTF-8"?>
<?php
// xml_db.php - Helper for XML Database Operations
define('DATA_DIR', __DIR__ . '/../data/');

function getXmlPath($table) {
    return DATA_DIR . $table . '.xml';
}

function loadXml($table) {
    $file = getXmlPath($table);
    if (!file_exists($file)) {
        // Create if doesn't exist
        $root = '<' . $table . '></' . $table . '>';
        file_put_contents($file, $root);
    }
    return simplexml_load_file($file);
}

function saveXml($table, $xmlObj) {
    // Format nicely
    $dom = new DOMDocument("1.0");
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xmlObj->asXML());
    $dom->save(getXmlPath($table));
}

function findUserByEmail($email) {
    $xml = loadXml('users');
    foreach ($xml->user as $user) {
        if ((string)$user->email === $email) {
            return $user;
        }
    }
    return null;
}

function findUserById($id) {
    $xml = loadXml('users');
    foreach ($xml->user as $user) {
        if ((string)$user->id === $id) {
            return $user;
        }
    }
    return null;
}
?>
