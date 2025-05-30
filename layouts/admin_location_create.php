<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../index.php');
  exit;
}

require_once '../config.php';

$message = '';
$messageType = '';

$targetDir = "../assets/images/";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $address = trim($_POST['address'] ?? '');

  $errors = [];

  if (empty($name)) {
    $errors[] = "Tên địa điểm không được để trống";
  }

  $photoPath = null;
  $photoName = null;

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

    $validMime = false;
    foreach ($allowed as $mime) {
      if ($filetype == $mime) {
        $validMime = true;
        break;
      }
    }

    if ($validMime) {
      $newname = uniqid() . '.' . $ext;
      $photoPath = $targetDir . $newname;

      $photoName = $newname;
    } else {
      $errors[] = "Lỗi: Có vấn đề với định dạng file. Tải lên bị hủy. (MIME: $filetype)";
    }
  }

  if (empty($errors)) {
    // Nếu có hình ảnh, di chuyển vào thư mục lưu trữ
    if ($photoPath && !move_uploaded_file($_FILES["photo"]["tmp_name"], $photoPath)) {
      throw new Exception("Không thể tải lên hình ảnh. Vui lòng thử lại sau.");
    }

    // Thêm địa điểm mới
    $stmt = $conn->prepare("
        INSERT INTO locations (name, address, photo) 
        VALUES (:name, :address, :photo)
      ");

    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':photo', $photoName);

    $stmt->execute();

    $message = "Địa điểm đã được tạo thành công!";
    $messageType = "success";

    $name = $address = '';

    // Chuyển hướng sau 1.5 giây
    header('refresh:1.5;url=./admin_locations.php');
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
  <title>Thêm Địa điểm - Admin</title>
</head>

<body>
  <?php include_once './admin_sidebar.php'; ?>

  <div class="content">
    <h1 class="mb-4">Thêm Địa điểm Mới</h1>

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./admin_locations.php">Danh sách địa điểm</a></li>
        <li class="breadcrumb-item active" aria-current="page">Thêm địa điểm mới</li>
      </ol>
    </nav>

    <?php if (!empty($message)): ?>
      <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
          <div class="form-group">
            <label for="name">
              <i class="fas fa-map-marked-alt"></i> Tên địa điểm <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="name" name="name" required
              value="<?php echo htmlspecialchars($name ?? ''); ?>">
          </div>

          <div class="form-group">
            <label for="photo">
              <i class="fas fa-image"></i> Hình ảnh
            </label>
            <div class="custom-file">
              <input type="file" class="custom-file-input" id="photo" name="photo" accept="image/*" onchange="previewPhoto(this)">
              <label class="custom-file-label" for="photo">Chọn hình ảnh...</label>
            </div>
            <small class="form-text text-muted">Định dạng hỗ trợ: JPG, JPEG, PNG, GIF. Kích thước tối đa: 5MB.</small>
          </div>

          <div class="form-group">
            <label for="address">
              <i class="fas fa-address-card"></i> Địa chỉ
            </label>
            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
            <small class="form-text text-muted">Địa chỉ cụ thể của địa điểm.</small>
          </div>

          <div class="form-group">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Lưu
            </button>
            <a href="./admin_locations.php" class="btn btn-secondary ml-2">
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