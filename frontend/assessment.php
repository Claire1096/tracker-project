<?php
include 'connect.php';

$error = "";

// DELETE
if (isset($_GET['delete']) && isset($_GET['type'])) {
    $id = intval($_GET['delete']);
    $type = $_GET['type'];

    if ($type == 'exam') {
        $conn->query("DELETE FROM Exam WHERE exam_num = $id");
    } elseif ($type == 'quizzes') {
        $conn->query("DELETE FROM Quizzes WHERE quiz_num = $id");
    } elseif ($type == 'activities') {
        $conn->query("DELETE FROM Activities WHERE activity_num = $id");
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// EDIT and SAVE
if (isset($_POST['edit_save']) && isset($_POST['type'])) {
    $type = $_POST['type'];
    $id = intval($_POST['id']);
    $course_code = $_POST['course_code'];
    $title = $_POST['Title'];
    $date_assigned = $_POST['date_assigned'];
    $score = $_POST['score'];

    if ($type == 'exam') {
        $stmt = $conn->prepare("UPDATE Exam SET course_code=?, Title=?, date_assigned=?, score=? WHERE exam_num=?");
    } elseif ($type == 'quizzes') {
        $stmt = $conn->prepare("UPDATE Quizzes SET course_code=?, Title=?, date_assigned=?, score=? WHERE quiz_num=?");
    } elseif ($type == 'activities') {
        $stmt = $conn->prepare("UPDATE Activities SET course_code=?, Title=?, date_assigned=?, score=? WHERE activity_num=?");
    }

    if (isset($stmt)) {
        $stmt->bind_param("sssii", $course_code, $title, $date_assigned, $score, $id);
        $stmt->execute();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// ADD new entry
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_score'])) {
    $type = $_POST['assessment_type'];
    $course_code = $_POST['course_code'];
    $title = $_POST['Title'];
    $date_assigned = $_POST['date_assigned'];
    $score = $_POST['score'];

    // Validate course_code
    $check = $conn->prepare("SELECT course_code FROM subjects WHERE course_code = ?");
    $check->bind_param("s", $course_code);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        $error = "Course code does not exist. Please enter a valid course code.";
    } else {
        if ($type == 'exam') {
            $stmt = $conn->prepare("INSERT INTO Exam (course_code, Title, date_assigned, score) VALUES (?, ?, ?, ?)");
        } elseif ($type == 'quizzes') {
            $stmt = $conn->prepare("INSERT INTO Quizzes (course_code, Title, date_assigned, score) VALUES (?, ?, ?, ?)");
        } elseif ($type == 'activities') {
            $stmt = $conn->prepare("INSERT INTO Activities (course_code, Title, date_assigned, score) VALUES (?, ?, ?, ?)");
        }

        if (isset($stmt)) {
            $stmt->bind_param("sssi", $course_code, $title, $date_assigned, $score);
            $stmt->execute();
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link rel="stylesheet" href="style.css">
    <title>Track Scores</title>
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
<div class="form-card">
    <h2>Add Score</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
        <label>Assessment Type</label>
        <select name="assessment_type" required>
            <option value="exam">Exam</option>
            <option value="quizzes">Quizzes</option>
            <option value="activities">Activities</option>
        </select>
        <label>Course Code</label>
        <input type="text" name="course_code" required>
        <label>Title</label>
        <input type="text" name="Title" required>
        <label>Date Assigned</label>
        <input type="date" name="date_assigned" required>
        <label>Score</label>
        <input type="number" name="score" required>
        <input type="submit" name="save_score" value="Save Score">
    </form>
</div>

<?php

function showTable($conn, $type, $tableName, $idField) {
    echo "<div class='table-card'>";
    echo "<h3>" . ucfirst($type) . "</h3>";
    echo "<table><tr><th>ID</th><th>Course Code</th><th>Title</th><th>Date</th><th>Score</th><th>Actions</th></tr>";

    $result = $conn->query("SELECT * FROM $tableName ORDER BY date_assigned DESC");


    while ($row = $result->fetch_assoc()) {
        $edit_id = $_GET['edit'] ?? null;

        if (isset($_GET['type']) && $_GET['type'] === $type && $edit_id == $row[$idField]) {
            echo "<tr><td colspan='6'>
                <form class='edit-form' method='POST'>
                    <input type='hidden' name='edit_save' value='1'>
                    <input type='hidden' name='type' value='$type'>
                    <input type='hidden' name='id' value='{$row[$idField]}'>
                    <label>Course Code</label>
                    <input type='text' name='course_code' value='{$row['course_code']}' required>
                    <label>Title</label>
                    <input type='text' name='Title' value='{$row['Title']}' required>
                    <label>Date</label>
                    <input type='date' name='date_assigned' value='{$row['date_assigned']}' required>
                    <label>Score</label>
                    <input type='number' name='score' value='{$row['score']}' required>
                    <input type='submit' class='btn' value='Save'>
                </form>
            </td></tr>";
        } else {
            echo "<tr>
                <td>{$row[$idField]}</td>
                <td>{$row['course_code']}</td>
                <td>{$row['Title']}</td>
                <td>{$row['date_assigned']}</td>
                <td>{$row['score']}</td>
                <td class='actions'>
                    <a class='btn' href='?edit={$row[$idField]}&type=$type'>Edit</a>
                    <a class='btn' href='?delete={$row[$idField]}&type=$type' onclick=\"return confirm('Delete this entry?')\">Delete</a>
                </td>
            </tr>";
        }
    }

    echo "</table></div>";
}
?>


<!-- EXAM TABLE -->
<?php
$edit_id = $_GET['edit'] ?? null;
$type = $_GET['type'] ?? "";
?>
<div class="table-cards-wrapper">
    <!-- Exam Table -->
    <div class='table-card'>
        <h3>Exam</h3>
        <table>
            <tr><th>Course Code</th><th>Title</th><th>Date</th><th>Score</th><th>Actions</th></tr>
            <?php
            $result = $conn->query("SELECT * FROM Exam ORDER BY exam_num DESC");
            while ($row = $result->fetch_assoc()) {
                if ($type == "exam" && $edit_id == $row['exam_num']) {
                    echo "<tr><td colspan='5'>
                        <form class='edit-form' method='POST'>
                            <input type='hidden' name='edit_save' value='1'>
                            <input type='hidden' name='type' value='exam'>
                            <input type='hidden' name='id' value='{$row['exam_num']}'>
                            <label>Course Code</label>
                            <input type='text' name='course_code' value='{$row['course_code']}' required>
                            <label>Title</label>
                            <input type='text' name='Title' value='{$row['Title']}' required>
                            <label>Date</label>
                            <input type='date' name='date_assigned' value='{$row['date_assigned']}' required>
                            <label>Score</label>
                            <input type='number' name='score' value='{$row['score']}' required>
                            <input type='submit' class='btn' value='Save'>
                        </form>
                    </td></tr>";
                } else {
                    echo "<tr>
                        <td>{$row['course_code']}</td>
                        <td>{$row['Title']}</td>
                        <td>{$row['date_assigned']}</td>
                        <td>{$row['score']}</td>
                        <td class='actions'>
                            <a class='btn' href='?edit={$row['exam_num']}&type=exam'>Edit</a>
                            <a class='btn' href='?delete={$row['exam_num']}&type=exam' onclick=\"return confirm('Delete this entry?')\">Delete</a>
                        </td>
                    </tr>";
                }
            }
            ?>
        </table>
    </div>

    <!-- Quizzes Table -->
    <div class='table-card'>
        <h3>Quizzes</h3>
        <table>
            <tr><th>Course Code</th><th>Title</th><th>Date</th><th>Score</th><th>Actions</th></tr>
            <?php
            $result = $conn->query("SELECT * FROM Quizzes ORDER BY quiz_num DESC");
            while ($row = $result->fetch_assoc()) {
                if ($type == "quizzes" && $edit_id == $row['quiz_num']) {
                    echo "<tr><td colspan='5'>
                        <form class='edit-form' method='POST'>
                            <input type='hidden' name='edit_save' value='1'>
                            <input type='hidden' name='type' value='quizzes'>
                            <input type='hidden' name='id' value='{$row['quiz_num']}'>
                            <label>Course Code</label>
                            <input type='text' name='course_code' value='{$row['course_code']}' required>
                            <label>Title</label>
                            <input type='text' name='Title' value='{$row['Title']}' required>
                            <label>Date</label>
                            <input type='date' name='date_assigned' value='{$row['date_assigned']}' required>
                            <label>Score</label>
                            <input type='number' name='score' value='{$row['score']}' required>
                            <input type='submit' class='btn' value='Save'>
                        </form>
                    </td></tr>";
                } else {
                    echo "<tr>
                        <td>{$row['course_code']}</td>
                        <td>{$row['Title']}</td>
                        <td>{$row['date_assigned']}</td>
                        <td>{$row['score']}</td>
                        <td class='actions'>
                            <a class='btn' href='?edit={$row['quiz_num']}&type=quizzes'>Edit</a>
                            <a class='btn' href='?delete={$row['quiz_num']}&type=quizzes' onclick=\"return confirm('Delete this entry?')\">Delete</a>
                        </td>
                    </tr>";
                }
            }
            ?>
        </table>
    </div>

    <!-- Activities Table -->
    <div class='table-card'>
        <h3>Activities</h3>
        <table>
            <tr><th>Course Code</th><th>Title</th><th>Date</th><th>Score</th><th>Actions</th></tr>
            <?php
            $result = $conn->query("SELECT * FROM Activities ORDER BY activity_num DESC");
            while ($row = $result->fetch_assoc()) {
                if ($type == "activities" && $edit_id == $row['activity_num']) {
                    echo "<tr><td colspan='5'>
                        <form class='edit-form' method='POST'>
                            <input type='hidden' name='edit_save' value='1'>
                            <input type='hidden' name='type' value='activities'>
                            <input type='hidden' name='id' value='{$row['activity_num']}'>
                            <label>Course Code</label>
                            <input type='text' name='course_code' value='{$row['course_code']}' required>
                            <label>Title</label>
                            <input type='text' name='Title' value='{$row['Title']}' required>
                            <label>Date</label>
                            <input type='date' name='date_assigned' value='{$row['date_assigned']}' required>
                            <label>Score</label>
                            <input type='number' name='score' value='{$row['score']}' required>
                            <input type='submit' class='btn' value='Save'>
                        </form>
                    </td></tr>";
                } else {
                    echo "<tr>
                        <td>{$row['course_code']}</td>
                        <td>{$row['Title']}</td>
                        <td>{$row['date_assigned']}</td>
                        <td>{$row['score']}</td>
                        <td class='actions'>
                            <a class='btn' href='?edit={$row['activity_num']}&type=activities'>Edit</a>
                            <a class='btn' href='?delete={$row['activity_num']}&type=activities' onclick=\"return confirm('Delete this entry?')\">Delete</a>
                        </td>
                    </tr>";
                }
            }
            ?>
        </table>
    </div>
</div>
</body>
</html>
