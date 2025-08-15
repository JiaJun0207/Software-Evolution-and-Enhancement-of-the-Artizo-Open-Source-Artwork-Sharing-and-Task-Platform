<?php
include("config.php");// Include the database connection file

session_start(); // Start the session

if (!isset($_SESSION['UID'])) {
  header("Location: login.php"); // Redirect to login if not logged in
  exit();
}

include("navbar.php"); // Include the navigation bar
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>


<body>
<div class="card text-bg-dark">
  <img src="assets/homepage/homepage.png" class="card-img" alt="homepageimg">
  <div class="card-img-overlay">
    <h1 class="card-title">Where Creativity Meets Opportunity.</h1>
    <p class="card-text">Artizo is a creative showcase and a community-driven job board for artists and clients.</p>
  </div>
</div>

  <a href="logout.php" class="btn btn-danger">Logout</a>

  <form action="" method="post"> <!-- Form to add a product -->
    <label for="artwork_id">Product Name</label>
    <input type="text" name="pname">
    <label for="artwork_title">Artwork Title</label>
    <input type="text" name="ptitle">
    <label for="artwork_description">Artwork Description</label>
    <input type="text" name="pdescription">

    <select class="form-select" aria-label="Default select example">
      <?php
      // Fetch categories from the database
      $sql = "SELECT * FROM `category`";
      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
          ?>
          <option value="<?php echo $row['category_id'] ?>">
            <?php echo $row['category_name'] ?>
          </option>
          <?php
        }
      }
      ?>
      
    </select>

    <input type="submit" value="Add Product" class="btn btn-primary">
  </form>
</body>

</html>