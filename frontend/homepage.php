<?php
include "connect.php"
 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studious</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
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

    <form class="search-container" action="search.php" method="GET"> 
    <input type="text" name="query" placeholder="Search something..." />
    <button type="submit"><i class="fas fa-search"></i></button>
</form>

    </div>
</header>

<section class="welcome" id="welcome">
    <div class="content">
        <h3>Welcome Back!</h3>
        <p><b>Your Education, Organized and Simplified.</b></p>
        <span>This web application manages student information and ensures students have easy access to course registration, 
        semester schedules, grades, and records to keep track of their academic progress.</span>
    </div>

   
</form>

</section>


</body>
</html>