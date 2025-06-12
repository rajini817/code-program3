<?php
// Database connection
$host = "localhost";
$user = "root";
$password = ""; // default for XAMPP
$dbname = "secure_app";

// Connect & create DB if not exists
$conn = new mysqli($host, $user, $password);
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

// Create users table
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255)
)");

// Simple form handler
function sanitize($data) {
    return htmlspecialchars(trim($data));
}

// Register User
if (isset($_POST['register'])) {
    $username = sanitize($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);
    if ($stmt->execute()) {
        echo "? Registered successfully!";
    } else {
        echo "? Username may already exist.";
    }
    $stmt->close();
}

// Login User
if (isset($_POST['login'])) {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hashed);
    if ($stmt->fetch() && password_verify($password, $hashed)) {
        echo "? Login successful!";
    } else {
        echo "? Invalid credentials!";
    }
    $stmt->close();
}
?>

<!-- Simple HTML Form -->
<form method="POST">
    <h3>Register</h3>
    Username: <input name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <button name="register">Register</button>
</form>

<form method="POST">
    <h3>Login</h3>
    Username: <input name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <button name="login">Login</button>
</form>
