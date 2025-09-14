<?php
include "connect.php";


$result = $conn->query("SELECT * FROM schedule");

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$schedule = [];

while ($row = $result->fetch_assoc()) {
    $schedule[$row['day']][] = $row;
}

// --- DELETE ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM schedule WHERE sched_id = $id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- EDIT MODE ---
$edit_mode = false;
$edit_data = [];

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM schedule WHERE sched_id = $id");

    if ($result->num_rows > 0) {
        $edit_data = $result->fetch_assoc();
        $edit_mode = true;
    }
}

// --- INSERT OR UPDATE ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $day = $_POST['day'];
  $course_code = $_POST['course_code'];
  $time = $_POST['time_in'];  // This will contain both time_in and time_out
  $instructor = $_POST['instructor'];

  // Split the time range into time_in and time_out
  $times = explode(" - ", $time);  // Split at the dash (e.g., "9:00 AM - 10:00 AM")

  if (count($times) == 2) {
      $time_in = trim($times[0]);  // Start time
      $time_out = trim($times[1]); // End time
  } else {
      $error_message = "Please enter the time in the correct format (e.g. 9:00 AM - 10:00 AM).";
  }

  // Proceed if both times are valid
  if (!isset($error_message)) {
      // Check if the course_code exists in the subjects table
      $check_subject = $conn->prepare("SELECT * FROM subjects WHERE course_code = ?");
      $check_subject->bind_param("s", $course_code);
      $check_subject->execute();
      $subject_result = $check_subject->get_result();

      if ($subject_result->num_rows == 0) {
          $error_message = "Error: The provided course code does not exist in the subjects table.";
      } else {
          // Insert or Update the schedule
          if (!empty($_POST['update_sched_id'])) {
              // Update schedule
              $id = $_POST['update_sched_id'];
              $stmt = $conn->prepare("UPDATE schedule SET day=?, course_code=?, time_in=?, time_out=?, instructor_name=? WHERE sched_id=?");
              $stmt->bind_param("sssssi", $day, $course_code, $time_in, $time_out, $instructor, $id);
          } else {
              // Insert new schedule
              $stmt = $conn->prepare("INSERT INTO schedule (day, course_code, time_in, time_out, instructor_name) VALUES (?, ?, ?, ?, ?)");
              $stmt->bind_param("sssss", $day, $course_code, $time_in, $time_out, $instructor);
          }

          if (!$stmt->execute()) {
              $error_message = "Database error: " . $stmt->error;
          } else {
              $success_message = "Schedule saved successfully.";
              $stmt->close();
              header("Location: " . $_SERVER['PHP_SELF']);
              exit();
          }
      }
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Schedule</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.net/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
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

<!-- Schedule Form -->
<div class="container-schedule">
  <form class="form-section-sched" method="POST">
    <?php if ($edit_mode): ?>
      <input type="hidden" name="update_sched_id" value="<?= $edit_data['sched_id'] ?>">
      <label class="form-label-sched">Edit Schedule</label>
    <?php else: ?>
      <label class="form-label">Set Schedule</label>
    <?php endif; ?>

    <!-- Day Dropdown -->
    <select class="form-select" name="day" required>
      <option value="" disabled <?= !$edit_mode ? 'selected' : '' ?>>Select Day</option>
      <?php foreach ($days as $d): ?>
        <option value="<?= $d ?>" <?= ($edit_mode && $edit_data['day'] === $d) ? 'selected' : '' ?>><?= $d ?></option>
      <?php endforeach; ?>
    </select>

    <!-- Input Fields -->
    <input type="text" class="form-control mt-2" name="course_code" placeholder="Enter Course Code" required value="<?= $edit_mode ? $edit_data['course_code'] : '' ?>">

    <input type="text" class="form-control mt-2" name="time_in" placeholder="Enter Time (e.g. 9:00 AM - 10:00 AM)" required value="<?= $edit_mode ? $edit_data['time_in'] : '' ?>">
    <input type="text" class="form-control mt-2" name="instructor" placeholder="Enter Instructor Name" required value="<?= $edit_mode ? $edit_data['instructor_name'] : '' ?>">

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger mt-3"><?= $error_message ?></div>
    <?php endif; ?>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success mt-3"><?= $success_message ?></div>
    <?php endif; ?>

    <button class="btn mt-3" type="submit"><?= $edit_mode ? "Update Entry" : "Add to Table" ?></button>
  </form>

  <!-- Schedule Table -->
  <div class="table-section">
    <table class="custom-table">
      <thead>
        <tr>
          <?php foreach ($days as $d): echo "<th>$d</th>"; endforeach; ?>
        </tr>
      </thead>
      <tbody>
<?php
// Find the highest number of rows needed
$max_rows = 0;
foreach ($days as $day) {
    if (isset($schedule[$day])) {
        $max_rows = max($max_rows, count($schedule[$day]));
    }
}

// Now loop through rows
for ($i = 0; $i < $max_rows; $i++): ?>
  <tr>
    <?php foreach ($days as $day): ?>
      <td>
        <?php if (isset($schedule[$day][$i])): 
          $entry = $schedule[$day][$i]; ?>
          <strong><?= $entry['course_code'] ?></strong><br>
          <?= $entry['time_in'] ?> - <?= $entry['time_out'] ?><br>
          <?= $entry['instructor_name'] ?><br>
          <a class='edit-btn' href='?edit=<?= $entry['sched_id'] ?>'>Edit</a>
          <a class='delete-btn' href='?delete=<?= $entry['sched_id'] ?>' onclick='return confirm("Are you sure?")'>Delete</a>
        <?php endif; ?>
      </td>
    <?php endforeach; ?>
  </tr>
<?php endfor; ?>
</tbody>

    </table>
  </div>
</div>

</body>
</html>





