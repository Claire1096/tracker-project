<?php
include 'connect.php';

$editMode = false;
$editData = [];

// Delete student
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM student WHERE student_number = $id");
    header("Location: account.php");
    exit();
}

// Update student
if (isset($_POST['update_student'])) {
    $photoPath = '';

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "uploads/";
        $fileName = basename($_FILES["photo"]["name"]);
        $targetFile = $targetDir . time() . "_" . $fileName;
        move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile);
        $photoPath = $targetFile;

        $stmt = $conn->prepare("UPDATE student SET student_name=?, middle_name=?, course=?, semester=?, year_section=?, status=?, guardian=?, contact=?, photo=? WHERE student_number=?");
        $stmt->bind_param(
            "sssssssssi",
            $_POST['student_name'],
            $_POST['middle_name'],
            $_POST['course'],
            $_POST['semester'],
            $_POST['year_section'],
            $_POST['status'],
            $_POST['guardian'],
            $_POST['contact'],
            $photoPath,
            $_POST['student_number']
        );
    } else {
        $stmt = $conn->prepare("UPDATE student SET student_name=?, middle_name=?, course=?, semester=?, year_section=?, status=?, guardian=?, contact=? WHERE student_number=?");
        $stmt->bind_param(
            "ssssssssi",
            $_POST['student_name'],
            $_POST['middle_name'],
            $_POST['course'],
            $_POST['semester'],
            $_POST['year_section'],
            $_POST['status'],
            $_POST['guardian'],
            $_POST['contact'],
            $_POST['student_number']
        );
    }

    $stmt->execute();
    header("Location: account.php");
    exit();
}

// Load data for editing
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM student WHERE student_number = $id");
    if ($result->num_rows > 0) {
        $editMode = true;
        $editData = $result->fetch_assoc();
    }
}

// Add student
if (isset($_POST['add_student'])) {
    $photoPath = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "uploads/";
        $fileName = basename($_FILES["photo"]["name"]);
        $targetFile = $targetDir . time() . "_" . $fileName;
        move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile);
        $photoPath = $targetFile;
    }

    $stmt = $conn->prepare("INSERT INTO student (student_number, student_name, middle_name, course, semester, year_section, status, guardian, contact, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "isssssssss",
        $_POST['student_number'],
        $_POST['student_name'],
        $_POST['middle_name'],
        $_POST['course'],
        $_POST['semester'],
        $_POST['year_section'],
        $_POST['status'],
        $_POST['guardian'],
        $_POST['contact'],
        $photoPath
    );
    $stmt->execute();
    header("Location: account.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <a href="homepage.php" class="logo">Studious<span>.</span></a>
    <div class="navbar">
        <a class="active" href="homepage.php">Home</a>
        <a class="active" href="sched.php">Schedule</a>
        <a class="active" href="subject.php">Subjects</a>
        <a class="active" href="assessment.php">Records</a>
        <a class="active" href="account.php">Account</a>
    </div>
</header>

<main class="account-page">
    <section class="student-section">
        <h2 class="section-heading">Student Information</h2>

        <?php if ($editMode): ?>
            <div class="form-card">
                <h3>Edit Student</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="student_number" value="<?= $editData['student_number'] ?>">
                    <label>Name</label><input type="text" name="student_name" value="<?= $editData['student_name'] ?>" required>
                    <label>Middle Name</label><input type="text" name="middle_name" value="<?= $editData['middle_name'] ?>" required>
                    <label>Course</label><input type="text" name="course" value="<?= $editData['course'] ?>" required>
                    <label>Semester</label><input type="text" name="semester" value="<?= $editData['semester'] ?>" required>
                    <label>Year/Section</label><input type="text" name="year_section" value="<?= $editData['year_section'] ?>" required>
                    <label>Status</label><input type="text" name="status" value="<?= $editData['status'] ?>" required>
                    <label>Guardian</label><input type="text" name="guardian" value="<?= $editData['guardian'] ?>" required>
                    <label>Contact</label><input type="text" name="contact" value="<?= $editData['contact'] ?>" required>
                    <label>Photo</label><input type="file" name="photo">
                    <input type="submit" name="update_student" value="Update Student">
                </form>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['add'])): ?>
            <div class="form-card">
                <h3>Add New Student</h3>
                <form method="POST" enctype="multipart/form-data">
                    <label>Student ID</label><input type="number" name="student_number" required>
                    <label>Name</label><input type="text" name="student_name" required>
                    <label>Middle Name</label><input type="text" name="middle_name" required>
                    <label>Course</label><input type="text" name="course" required>
                    <label>Semester</label><input type="text" name="semester" required>
                    <label>Year/Section</label><input type="text" name="year_section" required>
                    <label>Status</label><input type="text" name="status" required>
                    <label>Guardian</label><input type="text" name="guardian" required>
                    <label>Contact</label><input type="text" name="contact" required>
                    <label>Photo</label><input type="file" name="photo">
                    <input type="submit" name="add_student" value="Add Student">
                </form>
            </div>
        <?php endif; ?>

        <div class="student-cards-container">
    <?php
    $result = $conn->query("SELECT * FROM student");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='student-card'>
                    <img src='{$row['photo']}' alt='Student Photo' class='student-photo'>
                    <div class='student-info'>
                        <h3>{$row['student_name']} {$row['middle_name']}</h3>
                        <p><strong>ID:</strong> {$row['student_number']}</p>
                        <p><strong>Course:</strong> {$row['course']}</p>
                        <p><strong>Semester:</strong> {$row['semester']}</p>
                        <p><strong>Year/Section:</strong> {$row['year_section']}</p>
                        <p><strong>Status:</strong> {$row['status']}</p>
                        <p><strong>Guardian:</strong> {$row['guardian']}</p>
                        <p><strong>Contact:</strong> {$row['contact']}</p>
                        <div class='card-actions'>
                            <a class='btn edit-btn' href='?edit={$row['student_number']}'>Edit</a>
                            <a class='btn delete-btn' href='?delete={$row['student_number']}' onclick=\"return confirm('Are you sure?')\">Delete</a>
                        </div>
                    </div>
                </div>";
        }
    } else {
        echo "<p>No student data found.</p>";
    }
    ?>
</div>

    </section>
</main>

</body>
</html>
