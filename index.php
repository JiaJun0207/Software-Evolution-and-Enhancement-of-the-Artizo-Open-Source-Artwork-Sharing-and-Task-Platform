<?php
include("config.php");// Include the database connection file

session_start(); // Start the session

if (!isset($_SESSION['UID'])) {
  header("Location: login.php"); // Redirect to login if not logged in
  exit();
}

include("navbar.php"); // Include the navigation bar

// Fetch latest job requests
$sql = "SELECT t.*, u.user_name, u.profile_image, c.category_name
        FROM task t
        JOIN user u ON t.post_user_id = u.user_id
        LEFT JOIN category c ON t.category_id = c.category_id
        ORDER BY t.release_at DESC
        LIMIT 9";
$result = $conn->query($sql);
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


<body style="overflow-x: hidden;">
  <div class="banner">
    <img src="assets/homepage/homepage.png" alt="Homepage Banner" class="banner-img">
    <div class="banner-text">
      <h1 class="mb-5">Where Creativity Meets Opportunity.</h1>
      <p>Artizo is a creative showcase and a community-driven job board for artists and clients.</p>
    </div>
  </div>

  <div class="container-fluid px-3 px-md-5" style="margin-bottom: 100px;">
    <h1 class="centered-header inter-bold-44 mb-4 fs-2 fs-md-1">Featured Artworks</h1>
    <div class="row align-items-stretch g-3 g-md-4">
      <div class="col-12 col-md-5 d-flex flex-column gap-3 gap-md-4 mb-3 mb-md-0">
        <div class="flex-fill ratio-6 position-relative">
          <img src="assets/homepage/graphicdesign.png" class="img-fluid w-100 h-100 custom-img" alt="Artwork 1">
          <div class="overlay-text">Graphic Design</div>
        </div>
        <div class="flex-fill ratio-4 position-relative">
          <img src="assets/homepage/photograph.png" class="img-fluid w-100 h-100 custom-img" alt="Artwork 2">
          <div class="overlay-text">Photography</div>
        </div>
      </div>
      <div class="col-12 col-md-7 d-flex flex-column gap-3 gap-md-4">
        <div class="flex-fill ratio-6 position-relative">
          <img src="assets/homepage/illustration.png" class="img-fluid w-100 h-100 custom-img" alt="Artwork 3">
          <div class="overlay-text">Illustration</div>
        </div>
        <div class="d-flex flex-fill gap-3 gap-md-4">
          <div class="col position-relative">
            <img src="assets/homepage/3ddesign.png" class="img-fluid w-100 h-100 custom-img" alt="Artwork 4">
            <div class="overlay-text">3D Art</div>
          </div>
          <div class="col position-relative">
            <img src="assets/homepage/advertising.png" class="img-fluid w-100 h-100 custom-img" alt="Artwork 5">
            <div class="overlay-text">Advertising</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <h1 class="centered-header inter-bold-44 mb-4 fs-2 fs-md-1">Top Artists</h1>
  <div class="position-relative w-100" style="left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw; width: 100vw;">
    <div id="topArtistsCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
      <div class="carousel-indicators">
        <button type="button" data-bs-target="#topArtistsCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Leonardo"></button>
        <button type="button" data-bs-target="#topArtistsCarousel" data-bs-slide-to="1" aria-label="Hafiz"></button>
        <button type="button" data-bs-target="#topArtistsCarousel" data-bs-slide-to="2" aria-label="Lily"></button>
      </div>
      <div class="carousel-inner">
        <div class="carousel-item active">
          <div class="d-flex flex-column align-items-center justify-content-center" style="background-color: #F7822A !important; padding: 50px !important; width: 100vw; min-height: 600px;">
            <div class="mb-4">
              <img src="assets/profile/top1.png" alt="Top Artists" class="mx-auto d-block">
            </div>
            <h1 class="inter-medium-32 mb-4">Leonardo</h1>
            <p class="inter-extralight-24 text-center px-3 px-md-5 mb-4" style="max-width:800px;">
              10 years in creative advertising, Leonard's bold and eye-catching campaigns have helped brands across Malaysia grow.
            </p>
            <div class="d-flex justify-content-center gap-5">
              <div class="d-flex align-items-center">
                <img src="assets/icons/chair.png" alt="Chair Icon" style="width:30px; height:30px; margin-right: 10px;">
                <p class="inter-medium-24 mb-0">Advertising</p>
              </div>
              <div class="d-flex align-items-center">
                <img src="assets/icons/email.png" alt="Email Icon" style="width:30px; height:30px; margin-right: 10px;">
                <p class="inter-medium-24 mb-0">leonard@gmail.com</p>
              </div>
            </div>
          </div>
        </div>
        <div class="carousel-item">
          <div class="d-flex flex-column align-items-center justify-content-center" style="background-color: #8DE45B !important; padding: 50px !important; width: 100vw; min-height: 600px;">
            <div class="mb-4">
              <img src="assets/profile/top2.png" alt="Top Artists" class="mx-auto d-block">
            </div>
            <h1 class="inter-medium-32 mb-4">Hafiz</h1>
            <p class="inter-extralight-24 text-center px-3 px-md-5 mb-4" style="max-width:800px;">
              A 3D artist for over 10 years, Hafiz specializes in architectural visualization and product modeling, bringing concepts to life in realistic detail.
            </p>
            <div class="d-flex justify-content-center gap-5">
              <div class="d-flex align-items-center">
                <img src="assets/icons/chair.png" alt="Chair Icon" style="width:30px; height:30px; margin-right: 10px;">
                <p class="inter-medium-24 mb-0">3D Art</p>
              </div>
              <div class="d-flex align-items-center">
                <img src="assets/icons/email.png" alt="Email Icon" style="width:30px; height:30px; margin-right: 10px;">
                <p class="inter-medium-24 mb-0">hafiz.3dworks@gmail.com</p>
              </div>
            </div>
          </div>
        </div>
        <div class="carousel-item">
          <div class="d-flex flex-column align-items-center justify-content-center" style="background-color: #FD399D !important; padding: 50px !important; width: 100vw; min-height: 600px;">
            <div class="mb-4">
              <img src="assets/profile/top3.png" alt="Top Artists" class="mx-auto d-block">
            </div>
            <h1 class="inter-medium-32 mb-4">Lily</h1>
            <p class="inter-extralight-24 text-center px-3 px-md-5 mb-4" style="max-width:800px;">
              A photographer with 12 years of professional experience, Lily captures powerful visual narratives that blend emotion and precision.
            </p>
            <div class="d-flex justify-content-center gap-5">
              <div class="d-flex align-items-center">
                <img src="assets/icons/chair.png" alt="Chair Icon" style="width:30px; height:30px; margin-right: 10px;">
                <p class="inter-medium-24 mb-0">Photography</p>
              </div>
              <div class="d-flex align-items-center">
                <img src="assets/icons/email.png" alt="Email Icon" style="width:30px; height:30px; margin-right: 10px;">
                <p class="inter-medium-24 mb-0">lily.visuals@gmail.com</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#topArtistsCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#topArtistsCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  </div>
  <div class="container-fluid px-3 px-md-5" style="margin-bottom: 100px;">
    <h1 class="centered-header inter-bold-44 mb-4 fs-2 fs-md-1">Latest Job Requests</h1>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 g-md-4">
      <?php while ($row = $result->fetch_assoc()):
        $profileImg = !empty($row['profile_image']) ? "assets/profile/" . $row['profile_image'] : "assets/profile/user_profile.png";
        $userId = $row['post_user_id'];
        $taskId = $row['task_id'];
        $taskImg = !empty($row['task_image']) ? "assets/uploads/tasks/" . $row['task_image'] : "";
        $categoryName = $row['category_name'];
        ?>
        <div class="col">
          <div class="card_border d-flex flex-column justify-content-between" style="padding: 24px 32px; height: 300px;">
            <!-- Top content -->
            <div>
              <div class="d-flex align-items-center">
                <a href="user_profile.php?uid=<?php echo urlencode($userId); ?>">
                  <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile Image" class="rounded-circle"
                    style="width:46px; height:46px; object-fit:cover;">
                </a>
                <a href="user_profile.php?uid=<?php echo urlencode($userId); ?>" style="text-decoration:none;">
                  <p class="mb-0 inter-medium-32 ms-4 fs-6 fs-md-4" style="color:#000;">
                    <?php echo htmlspecialchars($row['user_name']); ?>
                  </p>
                </a>
              </div>
              <div class="mt-1">
                <a href="task_detail.php?id=<?php echo urlencode($taskId); ?>" style="text-decoration:none;">
                  <p class="inter-extralight-24 fs-7 fs-md-5" style="color:#000;">
                    <?php echo htmlspecialchars($row['task_description']); ?>
                  </p>
                </a>
              </div>
            </div>
            <!-- Bottom right icon -->
            <div class="text-end">
              <a href="task_detail.php?id=<?php echo urlencode($taskId); ?>">
                <div class="card_border" style="padding: 17px; display:inline-block;">
                  <img src="assets/icons/bag.png" alt="Arrow Right Icon" style="width:49px; height:49px;">
                </div>
              </a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
    <div class="d-flex justify-content-center w-100 mt-4 px-3 px-md-5">
      <a href="upload_artwork.php"
        class="btn btn-outline-black inter-medium-25 border_black px-4 px-md-5 py-2 fs-5 fs-md-4"
        style="min-width:180px;">
        View More
      </a>
    </div>
  </div>

</body>
<footer>
  <?php include("footer.php"); // Include the footer ?>
</footer>

</html>