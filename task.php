<?php
include("config.php");// Include the database connection file

session_start(); // Start the session

if (!isset($_SESSION['UID'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

include("navbar.php"); // Include the navigation bar
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid"
        style="padding-left: 60px; padding-right: 60px; padding-bottom: 60px; margin-top:60px;">
        <div class="row">
            <div class="col-8">
                <?php
                $sql = "SELECT * FROM task";
                if (isset($_GET['search'])) {
                    $search = $_GET['search'];
                    $sql .= " WHERE `task_title` LIKE '%$search%'";
                } else {
                    $_GET['search'] = '';
                }
                ?>
                <form action="" method="get" style="position:relative;">
                    <input type="text" class="form-control inter-medium-25 left-placeholder border_black"
                        id="searchInput" name="search" placeholder="Add a title"
                        value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                        style="padding-right:40px;">
                    <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); pointer-events:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zm-5.442 1.398a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z"/>
                        </svg>
                    </span>
                </form>

            </div>
            <div class="col-2">
                <a href="upload_task.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black">Post Task</a>
            </div>
            <div class="col-2">
                <a href="accepted_task.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black">Accepted Task</a>
            </div>
        </div>
    </div>
</body>
<footer>
    <?php include("footer.php"); // Include the footer ?>
</footer>

</html>