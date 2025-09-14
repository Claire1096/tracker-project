<?php
include 'connect.php';

if (isset($_GET['delete'])) {
  $delete_code = $_GET['delete'];
  $stmt = $conn->prepare("DELETE FROM subjects WHERE course_code = ?");
  $stmt->bind_param("s", $delete_code);
  $stmt->execute();
  $stmt->close();
  header("Location: subject.php");
  exit();
}

$editMode = false;
$editData = [];

if (isset($_GET['edit'])) {
  $editMode = true;
  $edit_code = $_GET['edit'];
  $stmt = $conn->prepare("SELECT * FROM subjects WHERE course_code = ?");
  $stmt->bind_param("s", $edit_code);
  $stmt->execute();
  $result_edit = $stmt->get_result();
  if ($result_edit->num_rows > 0) {
    $editData = $result_edit->fetch_assoc();
  }
  $stmt->close();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['update_subject'])) {
  $course_code = $_POST['course_code'];
  $course_name = $_POST['course_name'];
  $instructor_name = $_POST['instructor_name'];

  if (!empty($course_code) && !empty($course_name) && !empty($instructor_name)) {
    $stmt = $conn->prepare("INSERT INTO subjects (course_code, course_name, instructor_name) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $course_code, $course_name, $instructor_name);
    $stmt->execute();
    $stmt->close();
  }
}

if (isset($_POST['update_subject'])) {
  $stmt = $conn->prepare("UPDATE subjects SET course_code = ?, course_name = ?, instructor_name = ? WHERE course_code = ?");
  $stmt->bind_param("ssss", $_POST['course_code'], $_POST['course_name'], $_POST['instructor_name'], $_POST['old_course_code']);
  $stmt->execute();
  $stmt->close();

  // Also update in related tables
  $stmt = $conn->prepare("UPDATE instructors SET course_code = ? WHERE course_code = ?");
  $stmt->bind_param("ss", $_POST['course_code'], $_POST['old_course_code']);
  $stmt->execute();
  $stmt->close();

  $stmt = $conn->prepare("UPDATE schedule SET course_code = ? WHERE course_code = ?");
  $stmt->bind_param("ss", $_POST['course_code'], $_POST['old_course_code']);
  $stmt->execute();
  $stmt->close();

  header("Location: subject.php");
  exit();
}




$result = $conn->query("SELECT course_code, course_name, instructor_name FROM subjects ORDER BY course_code DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"/>
  <link rel="stylesheet" href="style.css" />
  <title>Subjects & Instructors</title>
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

<div class="card-container">
  <h2>Subjects</h2>
  <form method="POST" action="">
  <input type="text" name="course_code" placeholder="Course Code" required 
         value="<?= $editMode ? htmlspecialchars($editData['course_code']) : '' ?>" 
         />
  <?php if ($editMode): ?>
  <input type="hidden" name="old_course_code" value="<?= htmlspecialchars($editData['course_code']) ?>" />
<?php endif; ?>
  
  <input type="text" name="course_name" placeholder="Course Name" required 
         value="<?= $editMode ? htmlspecialchars($editData['course_name']) : '' ?>" />
  
  <input type="text" name="instructor_name" placeholder="Instructor" required 
         value="<?= $editMode ? htmlspecialchars($editData['instructor_name']) : '' ?>" />
  
  <?php if ($editMode): ?>
    <button type="submit" name="update_subject">Update</button>
    <a href="ins.php" class="btn">Cancel</a>
  <?php else: ?>
    <button type="submit">Add to Table</button>
  <?php endif; ?>
</form>



  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Course Code</th>
          <th>Course Names</th>
          <th>Instructors</th>
        </tr>
      </thead>
      <tbody>
  <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($row['course_code']) ?></td>
      <td><?= htmlspecialchars($row['course_name']) ?></td>
      <td><?= htmlspecialchars($row['instructor_name']) ?></td>
      <td>
        <a class="btn" href="?edit=<?= urlencode($row['course_code']) ?>">Edit</a>
        <a class="btn" href="?delete=<?= urlencode($row['course_code']) ?>" onclick="return confirm('Delete this subject?')">Delete</a>
      </td>
    </tr>
  <?php endwhile; ?>
</tbody>

    </table>
  </div>
</div>

</body>
</html>


