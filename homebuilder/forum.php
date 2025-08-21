<?php
session_start();
include("includes/config.php");
include("includes/functions.php");

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$error = "";
$success = "";

// Handle post creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_post'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    // Validate inputs
    if (empty($title) || empty($content)) {
        $error = "Please fill in all fields";
    } else {
        // Insert new post
        $sql = "INSERT INTO forum_posts (user_id, title, content, timestamp) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $user_id, $title, $content);
        
        if ($stmt->execute()) {
            $success = "Post created successfully!";
            
            // Redirect to forum page to show all posts
            header("Location: forum.php");
            exit();
        } else {
            $error = "Failed to create post: " . $conn->error;
        }
    }
}

// Handle comment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_comment'])) {
    $post_id = $_POST['post_id'];
    $content = $_POST['comment'];
    
    // Validate inputs
    if (empty($content)) {
        $error = "Comment cannot be empty";
    } else {
        // Insert new comment
        $sql = "INSERT INTO forum_comments (post_id, user_id, content, timestamp) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $post_id, $user_id, $content);
        
        if ($stmt->execute()) {
            $success = "Comment added successfully!";
            
            // Redirect to the same post
            header("Location: forum.php?post_id=" . $post_id);
            exit();
        } else {
            $error = "Failed to add comment: " . $conn->error;
        }
    }
}

// Fetch single post with comments
$selected_post = null;
$comments = [];

if (isset($_GET['post_id']) && is_numeric($_GET['post_id'])) {
    $post_id = $_GET['post_id'];
    
    // Fetch post details
    $sql = "SELECT p.*, u.username FROM forum_posts p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $selected_post = $result->fetch_assoc();
        
        // Fetch comments for this post
        $sql = "SELECT c.*, u.username FROM forum_comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.post_id = ? 
                ORDER BY c.timestamp ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
    }
}

// Fetch all posts for listing
$posts = [];
$sql = "SELECT p.*, u.username, 
        (SELECT COUNT(*) FROM forum_comments WHERE post_id = p.id) AS comment_count 
        FROM forum_posts p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.timestamp DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}

// Get remaining budget (for header display)
$remaining_budget = getRemainingBudget($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Forum - Home Builder</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include("includes/header.php"); ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Community Forum</h1>
            <p>Connect with other builders, share experiences, and get advice</p>
            
            <?php if (!$selected_post): ?>
                <button id="newPostBtn" class="btn btn-primary">Create New Post</button>
            <?php else: ?>
                <a href="forum.php" class="btn btn-secondary">Back to All Posts</a>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (!$selected_post): ?>
            <!-- New Post Form (Hidden by default) -->
            <div id="newPostForm" class="form-card" style="display: none;">
                <h2>Create New Post</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea id="content" name="content" rows="5" required></textarea>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="button" id="cancelPostBtn" class="btn btn-secondary">Cancel</button>
                        <button type="submit" name="create_post" class="btn btn-primary">Post</button>
                    </div>
                </form>
            </div>
            
            <!-- Posts Listing -->
            <div class="forum-posts">
                <?php if (empty($posts)): ?>
                    <p class="no-data">No posts yet. Be the first to create a discussion!</p>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="forum-post-card">
                            <div class="post-header">
                                <h3><a href="forum.php?post_id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                                <div class="post-meta">
                                    <span class="post-author">
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($post['username']); ?>
                                    </span>
                                    <span class="post-date">
                                        <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($post['timestamp'])); ?>
                                    </span>
                                    <span class="post-comments">
                                        <i class="fas fa-comments"></i> <?php echo $post['comment_count']; ?> comments
                                    </span>
                                </div>
                                <?php if ($post['user_id'] == $user_id): ?>
                                    <div class="post-actions">
                                        <a href="edit_post.php?post_id=<?php echo $post['id']; ?>" class="btn btn-warning">Edit</a>
                                        <a href="delete_post.php?post_id=<?php echo $post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                                    </div>
                                <?php endif; ?>

                            </div>
                            
                            <div class="post-preview">
                                <?php 
                                    $preview = substr(strip_tags($post['content']), 0, 200);
                                    echo nl2br(htmlspecialchars($preview));
                                    if (strlen($post['content']) > 200) echo '...';
                                ?>
                            </div>
                            
                            <a href="forum.php?post_id=<?php echo $post['id']; ?>" class="btn btn-secondary">Read More</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Single Post View -->
            <div class="single-post">
                <div class="post-card">
                    <div class="post-header">
                        <h2><?php echo htmlspecialchars($selected_post['title']); ?></h2>
                        <div class="post-meta">
                            <span class="post-author">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($selected_post['username']); ?>
                            </span>
                            <span class="post-date">
                                <i class="fas fa-calendar"></i> <?php echo date('M d, Y h:i A', strtotime($selected_post['timestamp'])); ?>
                            </span>
                        </div>
                        <?php if ($selected_post['user_id'] == $user_id): ?>
                            <div class="post-actions">
                                <a href="edit_post.php?post_id=<?php echo $selected_post['id']; ?>" class="btn btn-warning">Edit</a>
                                <a href="delete_post.php?post_id=<?php echo $selected_post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                            </div>
                        <?php endif; ?>

                    </div>
                    
                    <div class="post-content">
                        <?php echo nl2br(htmlspecialchars($selected_post['content'])); ?>
                    </div>
                </div>
                
                <div class="comments-section">
                    <h3><?php echo count($comments); ?> Comments</h3>
                    
                    <?php if (empty($comments)): ?>
                        <p class="no-data">No comments yet. Be the first to comment!</p>
                    <?php else: ?>
                        <div class="comments-list">
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment-card">
                                    <div class="comment-header">
                                        <span class="comment-author">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($comment['username']); ?>
                                        </span>
                                        <span class="comment-date">
                                            <i class="fas fa-calendar"></i> <?php echo date('M d, Y h:i A', strtotime($comment['timestamp'])); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="comment-content">
                                        <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="comment-form">
                        <h4>Add a Comment</h4>
                        <form method="POST" action="">
                            <input type="hidden" name="post_id" value="<?php echo $selected_post['id']; ?>">
                            
                            <div class="form-group">
                                <textarea name="comment" rows="3" placeholder="Write your comment..." required></textarea>
                            </div>
                            
                            <button type="submit" name="add_comment" class="btn btn-primary">Post Comment</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include("includes/footer.php"); ?>
    
    <script>
        // Toggle new post form
        const newPostBtn = document.getElementById('newPostBtn');
        const newPostForm = document.getElementById('newPostForm');
        const cancelPostBtn = document.getElementById('cancelPostBtn');
        
        if (newPostBtn && newPostForm && cancelPostBtn) {
            newPostBtn.addEventListener('click', function() {
                newPostForm.style.display = 'block';
                newPostBtn.style.display = 'none';
            });
            
            cancelPostBtn.addEventListener('click', function() {
                newPostForm.style.display = 'none';
                newPostBtn.style.display = 'block';
            });
        }
    </script>
    
    <script src="js/script.js"></script>
</body>
</html>