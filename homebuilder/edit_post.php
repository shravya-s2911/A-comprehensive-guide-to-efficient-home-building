<?php
session_start();
include("includes/config.php");
include("includes/functions.php");  // This should define getRemainingBudget()


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = $_GET['post_id'] ?? null;
$error = "";
$success = "";

if (!$post_id || !is_numeric($post_id)) {
    header("Location: forum.php");
    exit();
}

// Fetch the post to edit (ensure ownership)
$stmt = $conn->prepare("SELECT * FROM forum_posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    echo "Unauthorized or post not found.";
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (empty($title) || empty($content)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("UPDATE forum_posts SET title = ?, content = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $title, $content, $post_id, $user_id);
        if ($stmt->execute()) {
            header("Location: forum.php?post_id=" . $post_id);
            exit();
        } else {
            $error = "Failed to update post.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Post - Community Forum</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
    <?php include("includes/header.php"); ?>

    <div class="container">
        <div class="page-header">
            <h1>Edit Post</h1>
            <a href="forum.php?post_id=<?php echo $post_id; ?>" class="btn btn-secondary">Back to Post</a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?php echo htmlspecialchars($post['title']); ?>"
                        required
                    />
                </div>

                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea
                        id="content"
                        name="content"
                        rows="7"
                        required
                    ><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>

                <div class="form-buttons">
                    <a href="forum.php?post_id=<?php echo $post_id; ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Post</button>
                </div>
            </form>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>
</body>
</html>
