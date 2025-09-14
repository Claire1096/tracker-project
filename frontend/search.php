<?php include 'connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Search</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="style.css">

</head>
<body>
<header>
    <a href="homepage.php" class="logo">Studious<span>.</span></a>

    <div class="navbar">
        <a class="active" href="homepage.php">Home</a>
        <a class="active" href="sched.php">Schedule</a>
        <a class="active" href="ins.php">Subjects</a>
        <a class="active" href="assessment.php">Records</a>
        <a class="active" href="account.php">Account</a>

    <form class="search-container" action="search.php" method="GET"> 
    <input type="text" name="query" placeholder="Search something..." />
    <button type="submit"><i class="fas fa-search"></i></button>
</form>

    </div>
</header>


<section class="search-results" style="padding: 20px;">
<?php
if (isset($_GET['query'])) {
    $search = $conn->real_escape_string($_GET['query']);
    echo "<h2>Search Results for: <i>" . htmlspecialchars($search) . "</i></h2>";

    $hasResults = false; // Track if any results found

    // SCHEDULE
    $sql1 = "SELECT * FROM schedule WHERE course_code LIKE '%$search%' OR day LIKE '%$search%' OR time_in LIKE '%$search%' OR time_out LIKE '%$search%' OR instructor_name LIKE '%$search%'";
    $result1 = $conn->query($sql1);
    if ($result1->num_rows > 0) {
        $hasResults = true;
        echo "<h3>Schedule Matches</h3><table class='styled-table'><thead><tr><th>Course Code</th><th>Day</th><th>Time</th><th>Instructor</th></tr></thead><tbody>";
        while ($row = $result1->fetch_assoc()) {
            echo "<tr><td>{$row['course_code']}</td><td>{$row['day']}</td><td>{$row['time_in']} - {$row['time_out']}</td><td>{$row['instructor_name']}</td></tr>";
        }
        echo "</tbody></table>";
    }

    // SUBJECTS
    $sql2 = "SELECT * FROM subjects WHERE course_code LIKE '%$search%' OR course_name LIKE '%$search%' OR instructor_name LIKE '%$search%'";
    $result2 = $conn->query($sql2);
    if ($result2->num_rows > 0) {
        $hasResults = true;
        echo "<h3>Subjects Matches</h3><table class='styled-table'><thead><tr><th>Course Code</th><th>Course Name</th><th>Instructor</th></tr></thead><tbody>";
        while ($row = $result2->fetch_assoc()) {
            echo "<tr><td>{$row['course_code']}</td><td>{$row['course_name']}</td><td>{$row['instructor_name']}</td></tr>";
        }
        echo "</tbody></table>";
    }

    // STUDENTS
    $sql3 = "SELECT * FROM student WHERE student_number LIKE '%$search%' OR student_name LIKE '%$search%' OR middle_name LIKE '%$search%' OR course LIKE '%$search%' OR semester LIKE '%$search%' OR year_section LIKE '%$search%' OR status LIKE '%$search%' OR guardian LIKE '%$search%' OR contact LIKE '%$search%'";
    $result3 = $conn->query($sql3);
    if ($result3->num_rows > 0) {
        $hasResults = true;
        echo "<h3>Student Matches</h3><table class='styled-table'><thead><tr><th>ID</th><th>Name</th><th>Middle Name</th><th>Course</th><th>Semester</th><th>Year/Section</th><th>Status</th><th>Guardian</th><th>Contact</th></tr></thead><tbody>";
        while ($row = $result3->fetch_assoc()) {
            echo "<tr><td>{$row['student_number']}</td><td>{$row['student_name']}</td><td>{$row['middle_name']}</td><td>{$row['course']}</td><td>{$row['semester']}</td><td>{$row['year_section']}</td><td>{$row['status']}</td><td>{$row['guardian']}</td><td>{$row['contact']}</td></tr>";
        }
        echo "</tbody></table>";
    }

    // ACTIVITIES
    $sql4 = "SELECT * FROM activities WHERE course_code LIKE '%$search%' OR Title LIKE '%$search%' OR date_assigned LIKE '%$search%' OR score LIKE '%$search%'";
    $result4 = $conn->query($sql4);
    if ($result4->num_rows > 0) {
        $hasResults = true;
        echo "<h3>Activities Matches</h3><table class='styled-table'><thead><tr><th>Course Code</th><th>Title</th><th>Date</th><th>Score</th></tr></thead><tbody>";
        while ($row = $result4->fetch_assoc()) {
            echo "<tr><td>{$row['course_code']}</td><td>{$row['Title']}</td><td>{$row['date_assigned']}</td><td>{$row['score']}</td></tr>";
        }
        echo "</tbody></table>";
    }

    // EXAMS
    $sql5 = "SELECT * FROM exam WHERE course_code LIKE '%$search%' OR Title LIKE '%$search%' OR date_assigned LIKE '%$search%' OR score LIKE '%$search%'";
    $result5 = $conn->query($sql5);
    if ($result5->num_rows > 0) {
        $hasResults = true;
        echo "<h3>Exam Matches</h3><table class='styled-table'><thead><tr><th>Course Code</th><th>Title</th><th>Date</th><th>Score</th></tr></thead><tbody>";
        while ($row = $result5->fetch_assoc()) {
            echo "<tr><td>{$row['course_code']}</td><td>{$row['Title']}</td><td>{$row['date_assigned']}</td><td>{$row['score']}</td></tr>";
        }
        echo "</tbody></table>";
    }

    // QUIZZES
    $sql6 = "SELECT * FROM quizzes WHERE course_code LIKE '%$search%' OR Title LIKE '%$search%' OR date_assigned LIKE '%$search%' OR score LIKE '%$search%'";
    $result6 = $conn->query($sql6);
    if ($result6->num_rows > 0) {
        $hasResults = true;
        echo "<h3>Quiz Matches</h3><table class='styled-table'><thead><tr><th>Course Code</th><th>Title</th><th>Date</th><th>Score</th></tr></thead><tbody>";
        while ($row = $result6->fetch_assoc()) {
            echo "<tr><td>{$row['course_code']}</td><td>{$row['Title']}</td><td>{$row['date_assigned']}</td><td>{$row['score']}</td></tr>";
        }
        echo "</tbody></table>";
    }

    // INSTRUCTORS
    $sql7 = "SELECT * FROM instructors WHERE instructor_name LIKE '%$search%' OR course_code LIKE '%$search%'";
    $result7 = $conn->query($sql7);
    if ($result7->num_rows > 0) {
        $hasResults = true;
        echo "<h3>Instructor Matches</h3><table class='styled-table'><thead><tr><th>Instructor</th><th>Course Code</th></tr></thead><tbody>";
        while ($row = $result7->fetch_assoc()) {
            echo "<tr><td>{$row['instructor_name']}</td><td>{$row['course_code']}</td></tr>";
        }
        echo "</tbody></table>";
    }

    // ✅ Final message if no matches at all
    if (!$hasResults) {
        echo "<p style='color: #c00; font-weight: bold;'>No matches found for '<em>" . htmlspecialchars($search) . "</em>'.</p>";
    }

} else {
    echo "<h2>Please enter a search query.</h2>";
}


?>
</section>

</body>
</html>


