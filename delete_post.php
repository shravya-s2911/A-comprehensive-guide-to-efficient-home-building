<?php
session_start();
include("includes/config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = $_GET['post_id'] ?? null;

if (!$post_id || !is_numeric($post_id)) {
    header("Location: forum.php");
    exit();
}

// Optionally delete related comments here if needed (manual or via ON DELETE CASCADE)
$stmt = $conn->prepare("DELETE FROM forum_posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();

header("Location: forum.php");
exit();
