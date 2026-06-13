<?php
include("config.php");// Include the database connection file

session_start(); // Start the session

if (!isset($_SESSION['UID'])) {
  header("Location: login.php"); // Redirect to login if not logged in
  exit();
}

// Load categories from the shared `category` table (same source as Explore / Task).
$artworkCategories = [];
$artworkCategoryStmt = $conn->prepare("SELECT category_id, category_name FROM category ORDER BY category_id ASC");
if ($artworkCategoryStmt) {
    $artworkCategoryStmt->execute();
    $artworkCategoryResult = $artworkCategoryStmt->get_result();
    while ($artworkCategoryRow = $artworkCategoryResult->fetch_assoc()) {
        $artworkCategories[] = $artworkCategoryRow;
    }
}

include("navbar.php"); // Include the navigation bar
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Artwork</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container-fluid" style="padding-left: 60px; padding-right: 60px;">
    <h1 class="inter-bold-44 mb-4" style="margin-top:60px;">Post Artwork</h1>
    <form action="upload_artwork_form.php" method="POST" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="artworkTitle" class="inter-bold-32 mb-3">Title</label>
                <input type="text" class="form-control inter-medium-25 left-placeholder border_black" id="artwork_title" name="artwork_title" placeholder="Add a title" required>
        </div>
        <div class="mb-4">
            <label for="artworkDescription" class="inter-bold-32 mb-3">Description</label>
            <textarea class="form-control inter-medium-25 left-placeholder border_black" id="artwork_description" name="artwork_description" rows="3" placeholder="Add a detailed description" required></textarea>
        </div>
        <div class="mb-4">
            <label class="inter-bold-32 mb-3">Category</label>
            <div class="category-scroll">
              <div class="d-flex gap-2 flex-wrap" role="group" aria-label="artwork_category">
                <?php foreach ($artworkCategories as $index => $artworkCategory):
                    $catId = intval($artworkCategory['category_id']);
                    $radioId = 'artwork_category_' . $catId;
                ?>
                <input type="radio" class="btn-check" name="artwork_category" id="<?php echo htmlspecialchars($radioId); ?>"
                    value="<?php echo htmlspecialchars($catId); ?>" autocomplete="off" <?php echo $index === 0 ? 'required' : ''; ?>>
                <label class="btn btn-outline-black flex-fill inter-medium-25 border_black category-label" for="<?php echo htmlspecialchars($radioId); ?>"><?php echo htmlspecialchars($artworkCategory['category_name']); ?></label>
                <?php endforeach; ?>
              </div>
            </div>
        </div>
        <div class="mb-5">
            <label for="artwork_image" class="inter-bold-32 mb-3">Upload Image</label>
            <div id="drop-area" class="border border-2 border-dark rounded-3 text-center mb-3 d-flex flex-column align-items-center justify-content-center" style="cursor:pointer; background:#fafafa; padding:60px;">
                <img src="assets/icons/upload.png" alt="Upload Icon" style="width:48px; height:48px; margin-bottom:12px; display:block;">
                <span id="drop-text" class="inter-medium-24" style="display:block;">Drag & drop image here or click to select</span>
            </div>
            <div id="artwork-image-preview-wrap" class="task-image-preview-wrap d-none mb-3">
                <img id="artwork-image-preview" src="" alt="Artwork image preview">
            </div>
            <input type="file" class="form-control d-none" id="artwork_image" name="artwork_image" accept="image/*" required>
        </div>
        <div class="text-end" style="padding-bottom: 60px;">
            <button type="submit" class="btn btn-outline-black inter-medium-24 active">
                <img src="assets/icons/post.png" alt="post Icon" style="width:20px; height:20px; margin-right:8px; vertical-align:middle;">
                Post
            </button>
        </div>
    </form>
<script>
const dropArea = document.getElementById('drop-area');
const fileInput = document.getElementById('artwork_image');
const dropText = document.getElementById('drop-text');
const previewWrap = document.getElementById('artwork-image-preview-wrap');
const previewImg = document.getElementById('artwork-image-preview');

function updateImagePreview(file) {
  if (!file || !file.type.startsWith('image/')) {
    previewWrap.classList.add('d-none');
    previewImg.src = '';
    return;
  }
  const reader = new FileReader();
  reader.onload = (event) => {
    previewImg.src = event.target.result;
    previewWrap.classList.remove('d-none');
  };
  reader.readAsDataURL(file);
}

dropArea.addEventListener('click', () => fileInput.click());
dropArea.addEventListener('dragover', (e) => {
  e.preventDefault();
  dropArea.classList.add('bg-light');
  dropText.textContent = 'Release to upload';
});
dropArea.addEventListener('dragleave', (e) => {
  e.preventDefault();
  dropArea.classList.remove('bg-light');
  dropText.textContent = 'Drag & drop image here or click to select';
});
dropArea.addEventListener('drop', (e) => {
  e.preventDefault();
  dropArea.classList.remove('bg-light');
  if (e.dataTransfer.files.length) {
    try {
      fileInput.files = e.dataTransfer.files;
    } catch (error) {
      dropText.textContent = 'Use the file picker to select this image';
      updateImagePreview(e.dataTransfer.files[0]);
      return;
    }
    dropText.textContent = e.dataTransfer.files[0].name;
    updateImagePreview(e.dataTransfer.files[0]);
  }
});
fileInput.addEventListener('change', () => {
  if (fileInput.files.length) {
    dropText.textContent = fileInput.files[0].name;
    updateImagePreview(fileInput.files[0]);
  } else {
    dropText.textContent = 'Choose a file or drag and drop here';
    updateImagePreview(null);
  }
});
</script>
</div>
</body>
<footer>
    <?php include("footer.php"); // Include the footer ?>
</footer>
</html>