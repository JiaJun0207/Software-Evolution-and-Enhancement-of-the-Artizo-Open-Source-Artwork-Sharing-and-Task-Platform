<?php
include("config.php");// Include the database connection file

session_start(); // Start the session

if (!isset($_SESSION['UID'])) {
  header("Location: login.php"); // Redirect to login if not logged in
  exit();
}

include("navbar.php"); // Include the navigation bar

// Unified category source: the same `category` table that explore/artwork uses.
$taskCategories = [];
$categoryStmt = $conn->prepare("SELECT category_id, category_name FROM category ORDER BY category_id ASC");
if ($categoryStmt) {
    $categoryStmt->execute();
    $categoryResult = $categoryStmt->get_result();
    while ($categoryRow = $categoryResult->fetch_assoc()) {
        $taskCategories[] = $categoryRow;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container-fluid" style="padding-left: 60px; padding-right: 60px;">
    <h1 class="inter-bold-44 mb-4" style="margin-top:60px;">Post Task</h1>
    <?php if (isset($_SESSION['feedback'])): ?>
        <div class="alert alert-info inter-extralight-24" role="status">
            <?php echo htmlspecialchars($_SESSION['feedback']); ?>
        </div>
        <?php unset($_SESSION['feedback']); ?>
    <?php endif; ?>
    <form action="upload_task_form.php" method="POST" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="taskTitle" class="inter-bold-32 mb-3">Title</label>
                <input type="text" class="form-control inter-medium-25 left-placeholder border_black" id="task_title" name="task_title" placeholder="Add a title" required>
        </div>
        <div class="mb-4">
            <label for="taskDescription" class="inter-bold-32 mb-3">Description</label>
            <textarea class="form-control inter-medium-25 left-placeholder border_black" id="task_description" name="task_description" rows="3" placeholder="Add a detailed description" required></textarea>
        </div>
        <div class="mb-4">
            <label for="category_id" class="inter-bold-32 mb-3">Category</label>
            <select class="form-control inter-medium-25 border_black" id="category_id" name="category_id" required>
                <option value="">Select a category</option>
                <?php foreach ($taskCategories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-5">
            <label for="task_image" class="inter-bold-32 mb-3">Upload Image</label>
            <div id="drop-area" class="border border-2 border-dark rounded-3 text-center mb-3 d-flex flex-column align-items-center justify-content-center" style="cursor:pointer; background:#fafafa; padding:60px;">
                <img src="assets/icons/upload.png" alt="Upload Icon" style="width:48px; height:48px; margin-bottom:12px; display:block;">
                <span id="drop-text" class="inter-medium-24" style="display:block;">Drag & drop image here or click to select</span>
            </div>
            <div id="task-image-preview-wrap" class="task-image-preview-wrap d-none mb-3">
                <img id="task-image-preview" src="" alt="Task image preview">
            </div>
            <input type="file" class="form-control d-none" id="task_image" name="task_image" accept="image/*" required>
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
const fileInput = document.getElementById('task_image');
const dropText = document.getElementById('drop-text');
const previewWrap = document.getElementById('task-image-preview-wrap');
const previewImg = document.getElementById('task-image-preview');

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
