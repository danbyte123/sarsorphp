<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) || isset($_SESSION['client_id']);
}

function isUser() {
    return isset($_SESSION['user_id']);
}

function isClient() {
    return isset($_SESSION['client_id']);
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getClientId() {
    return $_SESSION['client_id'] ?? null;
}

function logout() {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>