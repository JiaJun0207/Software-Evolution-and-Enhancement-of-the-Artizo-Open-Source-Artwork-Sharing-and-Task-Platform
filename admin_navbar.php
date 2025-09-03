<link href="https://fonts.googleapis.com/css2?family=Inter:wght@200&display=swap" rel="stylesheet">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="background-color: #000;">
  <div class="container-fluid" style="padding: 15px 60px 15px 60px;">
    <a class="navbar-brand" href="index.php">
      <img src="assets/logo/navbar_logo.png" alt="Logo">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText"
      aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarText">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center"
        style="font-family: 'Inter', sans-serif; font-weight: 200; font-size: 30px; gap: 60px;">
        <li class="nav-item">
          <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['admin_index.php', 'admin_task.php']) ? ' active' : ''; ?>"
            href="admin_index.php">Post Preview</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_user_preview.php' ? ' active' : ''; ?>"
            href="admin_user_preview.php">User Preview</a>
        </li>
        <li class="nav-item">
          <?php
          // Show user profile image from database if logged in
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
            <img src="<?php echo htmlspecialchars($profile_img); ?>" alt="Profile"
              style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;" />
          </a>
        </li>
      </ul>
    </div>
</nav>