<link href="https://fonts.googleapis.com/css2?family=Inter:wght@200&display=swap" rel="stylesheet">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="background-color: #000;">
  <div class="container-fluid artizo-navbar-inner">
    <a class="navbar-brand" href="index.php">
      <img src="assets/logo/navbar_logo.png" alt="Logo" class="artizo-navbar-logo">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarText">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center navbar-main-list">
        <li class="nav-item">
          <a class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? ' active' : ''; ?>" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'explore.php' ? ' active' : ''; ?>" href="explore.php">Explore</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'task.php' ? ' active' : ''; ?>" href="task.php">Task</a>
        </li>
        <li class="nav-item">
          <?php
          $profile_img = "assets/profile/user_profile.png";
          if (isset($_SESSION['UID'])) {
              include_once("config.php");
              $uid = $_SESSION['UID'];
              $sql = "SELECT profile_image FROM user WHERE user_id = ?";
              $stmt = $conn->prepare($sql);
              $stmt->bind_param("i", $uid);
              $stmt->execute();
              $result = $stmt->get_result();
              if ($row = $result->fetch_assoc()) {
                  if (!empty($row['profile_image'])) {
                      $profile_img = "assets/profile/" . htmlspecialchars($row['profile_image']);
                  }
              }
          }
          ?>
          <a href="user_profile.php">
            <img src="<?php echo htmlspecialchars($profile_img); ?>" alt="Profile" class="artizo-navbar-profile" />
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<style>
@media (max-width: 991.98px) {
  .navbar-main-list {
    font-family: 'Inter', sans-serif;
    gap: 20px !important;
    font-weight: 200 !important;
    font-size: 18px !important;
  }
}
@media (min-width: 992px) {
  .navbar-main-list {
    font-family: 'Inter', sans-serif;
    gap: 34px !important;
    font-weight: 200 !important;
    font-size: 20px !important;
  }
}
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
