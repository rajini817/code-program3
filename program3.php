<?php
session_start();

// DB connection
$pdo = new PDO("mysql:host=localhost", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create DB and table
$pdo->exec("CREATE DATABASE IF NOT EXISTS advanced_demo");
$pdo->exec("USE advanced_demo");
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role VARCHAR(10) DEFAULT 'user',
    photo VARCHAR(100) DEFAULT NULL
)");

// Registration
if (isset($_POST['register'])) {
    $user = $_POST['username'];
    $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'] ?? 'user';
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$user, $pass, $role]);
        echo "Registration successful. Please log in.<br>";
    } catch (PDOException $e) {
        echo "User already exists.<br>";
    }
}

// Login
if (isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$user]);
    $row = $stmt->fetch();
    if ($row && password_verify($pass, $row['password'])) {
        $_SESSION['user'] = $row['username'];
        $_SESSION['role'] = $row['role'];
    } else {
        echo "Invalid login.<br>";
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Image upload
if (isset($_POST['upload']) && isset($_SESSION['user'])) {
    $filename = $_FILES['photo']['name'];
    $temp = $_FILES['photo']['tmp_name'];
    move_uploaded_file($temp, "uploads/$filename");
    $stmt = $pdo->prepare("UPDATE users SET photo=? WHERE username=?");
    $stmt->execute([$filename, $_SESSION['user']]);
}

// Search
$searchQuery = "";
if (isset($_POST['search'])) {
    $search = "%".$_POST['search']."%";
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username LIKE ?");
    $stmt->execute([$search]);
} else {
    $stmt = $pdo->query("SELECT * FROM users");
}

?>

<!DOCTYPE html>
<html>
<head><title>Advanced PHP+MySQL App</title></head>
<body>
<h2>Advanced PHP & MySQL Demo</h2>

<?php if (!isset($_SESSION['user'])): ?>
<!-- Login & Register -->
<form method="post">
    <h3>Register</h3>
    Username: <input name="username" required>
    Password: <input type="password" name="password" required>
    Role: <select name="role"><option>user</option><option>admin</option></select>
    <button name="register">Register</button>
</form>

<form method="post">
    <h3>Login</h3>
    Username: <input name="username" required>
    Password: <input type="password" name="password" required>
    <button name="login">Login</button>
</form>

<?php else: ?>
<!-- Logged-in UI -->
<p>Welcome, <?= htmlspecialchars($_SESSION['user']) ?> | Role: <?= $_SESSION['role'] ?> | <a href="?logout">Logout</a></p>

<!-- Upload photo -->
<form method="post" enctype="multipart/form-data">
    Upload Profile Photo: <input type="file" name="photo" required>
    <button name="upload">Upload</button>
</form>

<!-- Search -->
<form method="post">
    <input type="text" name="search" placeholder="Search users">
    <button name="searchBtn">Search</button>
</form>

<!-- User List -->
<h3>All Users</h3>
<table border="1" cellpadding="5">
<tr><th>ID</th><th>Username</th><th>Role</th><th>Photo</th></tr>
<?php foreach ($stmt as $row): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['username']) ?></td>
    <td><?= $row['role'] ?></td>
    <td>
        <?php if ($row['photo']): ?>
            <img src="uploads/<?= $row['photo'] ?>" width="50">
        <?php else: ?>
            No Photo
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</body>
</html>
