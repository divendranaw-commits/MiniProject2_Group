<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :u");
    $stmt->execute(['u' => $username]);

    if ($stmt->fetch()) {
        $error = "Username already taken.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:u, :p, 'student')");
        $stmt->execute(['u' => $username, 'p' => $hash]);
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head><link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="container mt-4" style="max-width:400px">
<h3>Register</h3>
<?php if (isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<form method="POST">
    <input name="username" class="form-control mb-2" placeholder="Username" required minlength="3">
    <input name="password" type="password" class="form-control mb-2" placeholder="Password" required minlength="6">
    <button class="btn btn-primary">Register</button>
    <a href="index.php">Login instead</a>
</form>
</body>
</html>