<?php
session_start();
require_once '../config.php';

$message = '';
$messageType = '';
$speaker = null;

$targetDir = "../assets/images/";
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: ./admin_speakers.php');
  exit;
}

$speakerId = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM speakers WHERE speaker_id = :speaker_id");
$stmt->bindParam(':speaker_id', $speakerId, PDO::PARAM_INT);
$stmt->execute();

$speaker = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$speaker) {
  header('Location: ./admin_speakers.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullName = trim($_POST['full_name'] ?? '');
  $bio = trim($_POST['bio'] ?? '');
  $description = trim($_POST['description'] ?? '');

  $errors = [];

  if (empty($fullName)) {
    $errors[] = "Họ tên không được để trống";
  }

  $photoPath = null;
  $photoName = $speaker['photo'];

  if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
    $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
    $filename = $_FILES["photo"]["name"];
    $filetype = $_FILES["photo"]["type"];
    $filesize = $_FILES["photo"]["size"];

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!array_key_exists($ext, $allowed)) {
      $errors[] = "Lỗi: Vui lòng chọn định dạng file hợp lệ. Chỉ chấp nhận JPG, JPEG, PNG, GIF.";
    }

    $maxsize = 5 * 1024 * 1024;
    if ($filesize > $maxsize) {
      $errors[] = "Lỗi: Kích thước file vượt quá giới hạn cho phép (5MB).";
    }

    $newname = uniqid() . '.' . $ext;
    $photoPath = $targetDir . $newname;

    $photoName = $newname;
  }

  if (empty($errors)) {
    if ($photoPath) {
      move_uploaded_file($_FILES["photo"]["tmp_name"], $photoPath);

      if (!empty($speaker['photo']) && $speaker['photo'] !== $photoName && file_exists($targetDir . $speaker['photo'])) {
        unlink($targetDir . $speaker['photo']);
      }
    }

    $stmt = $conn->prepare("
        UPDATE speakers 
        SET full_name = :full_name,
            bio = :bio,
            description = :description,
            photo = :photo
        WHERE speaker_id = :speaker_id
      ");

    $stmt->bindParam(':full_name', $fullName);
    $stmt->bindParam(':bio', $bio);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':photo', $photoName);
    $stmt->bindParam(':speaker_id', $speakerId, PDO::PARAM_INT);

    $stmt->execute();

    $message = "Thông tin diễn giả đã được cập nhật thành công!";
    $messageType = "success";

    $speaker['full_name'] = $fullName;
    $speaker['bio'] = $bio;
    $speaker['description'] = $description;
    $speaker['photo'] = $photoName;

    header('refresh:1.5;url=./admin_speakers.php');
  } else {
    $message = implode("<br>", $errors);
    $messageType = "danger";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <link rel="stylesheet" href="../assets/css/style-admin.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Chỉnh Sửa Diễn giả</title>
  <style>
    .photo-preview {
      max-width: 100%;
      max-height: 200px;
      margin-top: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 5px;
      display: none;
    }

    .current-photo {
      max-height: 200px;
      margin-bottom: 10px;
      border-radius: 4px;
    }
  </style>
</head>

<body>
  <?php include_once './admin_sidebar.php'; ?>

  <div class="content">
    <h1 class="mb-4">Chỉnh Sửa Diễn giả</h1>

    <?php if (!empty($message)): ?>
      <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header bg-primary text-white">
        <i class="fas fa-edit"></i> Chỉnh sửa diễn giả: <?php echo htmlspecialchars($speaker['full_name']); ?>
      </div>
      <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
          <div class="form-group">
            <label for="full_name">
              <i class="fas fa-user"></i> Họ và tên <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="full_name" name="full_name" required
              value="<?php echo htmlspecialchars($speaker['full_name']); ?>">
          </div>

          <div class="form-group">
            <label for="photo">
              <i class="fas fa-image"></i> Hình ảnh
            </label>
            <?php if (!empty($speaker['photo']) && file_exists($targetDir . $speaker['photo'])): ?>
              <div class="mb-3">
                <p>Ảnh hiện tại:</p>
                <img src="<?php echo '../assets/images/' . htmlspecialchars($speaker['photo']); ?>"
                  alt="Ảnh diễn giả hiện tại" class="current-photo img-thumbnail">
              </div>
            <?php endif; ?>
            <div class="custom-file">
              <input type="file" class="custom-file-input" id="photo" name="photo" accept="image/*" onchange="previewPhoto(this)">
              <label class="custom-file-label" for="photo">Chọn hình ảnh mới...</label>
            </div>
            <small class="form-text text-muted">Để trống nếu muốn giữ nguyên ảnh cũ. Định dạng hỗ trợ: JPG, JPEG, PNG, GIF. Kích thước tối đa: 5MB.</small>
          </div>

          <div class="form-group">
            <label for="bio">
              <i class="fas fa-address-card"></i> Tiểu sử
            </label>
            <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo htmlspecialchars($speaker['bio'] ?? ''); ?></textarea>
            <small class="form-text text-muted">Tiểu sử ngắn gọn của diễn giả.</small>
          </div>

          <div class="form-group">
            <label for="description">
              <i class="fas fa-align-left"></i> Mô tả chi tiết
            </label>
            <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($speaker['description'] ?? ''); ?></textarea>
            <small class="form-text text-muted">Mô tả chi tiết về kinh nghiệm, thành tựu và chuyên môn của diễn giả.</small>
          </div>

          <div class="form-group">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Lưu thay đổi
            </button>
            <a href="./admin_speakers.php" class="btn btn-secondary ml-2">
              <i class="fas fa-arrow-left"></i> Quay lại
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS và jQuery -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
  <script>
    // Hiển thị tên file đã chọn
    $(".custom-file-input").on("change", function() {
      var fileName = $(this).val().split("\\").pop();
      $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
  </script>
</body>

</html>