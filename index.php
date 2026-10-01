<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u");
    $stmt->execute(['u' => $_POST['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($_POST['password'], $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: books.php");
        exit;
    } else {
        $error = "Invalid login.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="container mt-4" style="max-width:400px">
<h3>Login</h3>
<?php if (isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<form method="POST">
    <input name="username" class="form-control mb-2" placeholder="Username" required>
    <input name="password" type="password" class="form-control mb-2" placeholder="Password" required>
    <button class="btn btn-primary">Login</button>
    <a href="register.php">Register instead</a>
</form>
</body>
</html>