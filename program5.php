<?php
// Database setup
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "final_project";

// Connect and create database
$conn = new mysqli($host, $user, $pass);
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

// Create students table
$conn->query("CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    course VARCHAR(100)
)");

// ADD student
if (isset($_POST['add'])) {
    $stmt = $conn->prepare("INSERT INTO students (name, email, course) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['name'], $_POST['email'], $_POST['course']);
    $stmt->execute();
    echo "? Student added.<br>";
    $stmt->close();
}

// DELETE student
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "??? Student deleted.<br>";
    $stmt->close();
}

// UPDATE student
if (isset($_POST['update'])) {
    $stmt = $conn->prepare("UPDATE students SET name=?, email=?, course=? WHERE id=?");
    $stmt->bind_param("sssi", $_POST['name'], $_POST['email'], $_POST['course'], $_POST['id']);
    $stmt->execute();
    echo "?? Student updated.<br>";
    $stmt->close();
}

// EDIT form loader
$edit = false;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $edit = true;
}
?>

<!-- HTML Form -->
<h2><?= $edit ? "Edit Student" : "Add Student" ?></h2>
<form method="POST">
    <input type="hidden" name="id" value="<?= $edit ? $row['id'] : '' ?>">
    Name: <input name="name" value="<?= $edit ? $row['name'] : '' ?>" required><br>
    Email: <input name="email" value="<?= $edit ? $row['email'] : '' ?>" required><br>
    Course: <input name="course" value="<?= $edit ? $row['course'] : '' ?>" required><br>
    <button name="<?= $edit ? 'update' : 'add' ?>">
        <?= $edit ? 'Update' : 'Add' ?> Student
    </button>
</form>

<!-- Display Student Records -->
<h2>Student List</h2>
<table border="1" cellpadding="5">
<tr><th>ID</th><th>Name</th><th>Email</th><th>Course</th><th>Actions</th></tr>
<?php
$res = $conn->query("SELECT * FROM students");
while ($row = $res->fetch_assoc()) {
    echo "<tr>
        <td>{$row['id']}</td>
        <td>{$row['name']}</td>
        <td>{$row['email']}</td>
        <td>{$row['course']}</td>
        <td>
            <a href='?edit={$row['id']}'>??</a>
            <a href='?delete={$row['id']}' onclick='return confirm(\"Delete?\")'>???</a>
        </td>
    </tr>";
}
?>
</table>
