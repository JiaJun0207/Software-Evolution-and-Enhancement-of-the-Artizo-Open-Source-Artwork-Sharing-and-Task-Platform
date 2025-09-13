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
    <title>Explore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid"
        style="padding-left: 60px; padding-right: 60px; padding-bottom: 60px; margin-top:60px;">
        <div class="row">
            <div class="col-10">
                <?php
                $sql = "SELECT * FROM artwork";
                if (isset($_GET['search'])) {
                    $search = $_GET['search'];
                    $sql .= " WHERE `artwork_title` LIKE '%$search%'
                    OR `artwork_description` LIKE '%$search%'";
                } else {
                    $_GET['search'] = '';
                }
                ?>
                <form action="" method="get">
                    <div id="searchBarWrapper" style="position:relative; background:#000; border-radius:14px;">
                        <input type="text" class="form-control inter-medium-25 left-placeholder border_black"
                            id="searchInput" name="search"
                            value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                            style="padding-right:40px; background:#000; color:#fff;">
                        <span id="searchIcon"
                            style="position:absolute; right:12px; top:50%; transform:translateY(-50%); pointer-events:none;">
                            <img src="assets/Icons/search-white.svg" alt="Search Icon" id="searchImg">
                        </span>
                    </div>
                </form>
                <script>
                    const searchInput = document.getElementById('searchInput');
                    const searchBarWrapper = document.getElementById('searchBarWrapper');
                    const searchImg = document.getElementById('searchImg');

                    searchInput.addEventListener('focus', function () {
                        searchBarWrapper.style.background = "#fff";
                        searchInput.style.background = "#fff";
                        searchInput.style.color = "#000";
                        searchImg.src = "assets/Icons/search-black.svg";
                    });
                    searchInput.addEventListener('blur', function () {
                        searchBarWrapper.style.background = "#000";
                        searchInput.style.background = "#000";
                        searchInput.style.color = "#fff";
                        searchImg.src = "assets/Icons/search-white.svg";
                    });
                </script>

            </div>
            <div class="col-2">
                <a href="upload_artwork.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black">Post Artwork</a>
            </div>
        </div>

        <?php
        // Get selected category from GET
        $selectedCategory = $_GET['category'] ?? 'ALL';

        // Define categories
        $categories = [
            'ALL',
            'Graphic Design',
            'Illustration',
            'Photography',
            '3D Art',
            'Advertising'
        ];
        ?>

        <div class="row" style="margin-top:100px; margin-bottom:100px;">
            <div class="d-flex justify-content-between align-items-center" style="gap:0; padding:0 20px;">
                <?php foreach ($categories as $cat): ?>
                    <?php
                    $isActive = ($selectedCategory === $cat) || ($cat === 'ALL' && $selectedCategory === 'ALL');
                    $catParam = $cat === 'ALL' ? '' : 'category=' . urlencode($cat);
                    $href = $cat === 'ALL' ? 'explore.php' : 'explore.php?' . $catParam;
                    ?>
                    <a href="<?php echo $href; ?>" style="text-decoration:none; text-align:center;">
                        <p class="inter-medium-32"
                            style="margin:0; padding-bottom:8px; color:#000; display:inline-block; border-bottom:<?php echo $isActive ? '3px solid #000' : 'none'; ?>;">
                            <?php echo $cat; ?>
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
        $categoryDescriptions = [
            'Graphic Design' => [
                'title' => 'Graphic Design',
                'desc' => 'Creative works like posters, logos, and product packaging that focus on visual communication and branding.'
            ],
            'Illustration' => [
                'title' => 'Illustration',
                'desc' => 'Hand-drawn or digital artworks that express ideas, concepts, or stories through visual creativity and unique styles.'
            ],
            'Photography' => [
                'title' => 'Photography',
                'desc' => 'Capturing moments, subjects, and environments through the lens to convey mood, narrative, or aesthetic value.'
            ],
            '3D Art' => [
                'title' => '3D Art',
                'desc' => 'Digital or physical three-dimensional creations such as models, animations, and environments that bring designs to life with depth and realism.'
            ],
            'Advertising' => [
                'title' => 'Advertising',
                'desc' => 'Creative visuals and campaigns designed to promote products, services, or brands by capturing audience attention and driving engagement.'
            ]
        ];
        ?>

        <div class="category_description">
            <?php
            if (isset($categoryDescriptions[$selectedCategory])) {
                echo '<p class="inter-bold-56">' . $categoryDescriptions[$selectedCategory]['title'] . '</p>';
                echo '<p class="inter-extralight-24">' . $categoryDescriptions[$selectedCategory]['desc'] . '</p>';
            }
            ?>
        </div>

        <?php
        // Build SQL for category and search
        $sql = "SELECT a.*, u.user_name, u.profile_image, c.category_name 
        FROM artwork a
        JOIN user u ON a.user_id = u.user_id
        JOIN category c ON a.category_id = c.category_id";
        $where = [];
        if (isset($_GET['search']) && $_GET['search'] !== '') {
            $search = $conn->real_escape_string($_GET['search']);
            $where[] = "(a.artwork_title LIKE '%$search%' OR a.artwork_description LIKE '%$search%')";
        }
        if (isset($_GET['category']) && $_GET['category'] !== '' && $_GET['category'] !== 'ALL') {
            $category = $conn->real_escape_string($_GET['category']);
            $where[] = "c.category_name = '$category'";
        }
        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $result = $conn->query($sql);
        ?>

        <div class="row row-cols-3 gx-4" style="--bs-gutter-y: 90px;">
            <?php while ($row = $result->fetch_assoc()): 
                $profileImg = !empty($row['profile_image']) ? "assets/profile/" . $row['profile_image'] : "assets/profile/user_profile.png";
                $userName = $row['user_name'];
                $artworkImg = !empty($row['artwork_image']) ? "assets/uploads/artworks/" . $row['artwork_image'] : "assets/uploads/artworks/default_artwork.jpeg";
                $artworkTitle = $row['artwork_title'];
                $artworkId = $row['artwork_id'];
            ?>
            <div class="col">
                <div class="d-flex align-items-center mb-3">
                    <div class="d-inline-block">
                        <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile Image" class="rounded-circle"
                            style="width:46px; height:46px; object-fit:cover;">
                    </div>
                    <p class="mb-0 inter-extralight-24 ms-3">
                        <?php echo htmlspecialchars($userName); ?>
                    </p>
                </div>
                <a href="artwork_detail.php?id=<?php echo urlencode($artworkId); ?>">
                    <div class="artwork-img-responsive">
                        <img src="<?php echo htmlspecialchars($artworkImg); ?>" alt="artwork_image">
                    </div>
                </a>
                <div>
                    <p class="mb-0 inter-medium-24 mt-3"><?php echo htmlspecialchars($artworkTitle); ?></p>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
</div>
</body>
<footer>
    <?php include("footer.php"); ?>
</footer>

</html>