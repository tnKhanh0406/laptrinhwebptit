<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../index.php');
  exit;
}

require_once '../config.php';

$message = '';
$messageType = '';
$location = null;

$targetDir = "../assets/images/";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: ./admin_locations.php');
  exit;
}

$locationId = (int)$_GET['id'];

try {
  $stmt = $conn->prepare("SELECT * FROM locations WHERE location_id = :location_id");
  $stmt->bindParam(':location_id', $locationId, PDO::PARAM_INT);
  $stmt->execute();

  $location = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$location) {
    header('Location: ./admin_locations.php');
    exit;
  }
} catch (PDOException $e) {
  $message = "Lỗi khi lấy thông tin địa điểm: " . $e->getMessage();
  $messageType = "danger";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $address = trim($_POST['address'] ?? '');

  $errors = [];

  if (empty($name)) {
    $errors[] = "Tên địa điểm không được để trống";
  }

  $photoPath = null;
  $photoName = $location['photo'];
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
    if ($photoPath) {
      if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $photoPath)) {
        throw new Exception("Không thể tải lên hình ảnh. Vui lòng thử lại sau.");
      }

      // Xóa ảnh cũ nếu có
      if (!empty($location['photo']) && $location['photo'] !== $photoName && file_exists($targetDir . $location['photo'])) {
        unlink($targetDir . $location['photo']);
      }
    }

    // Cập nhật địa điểm
    $stmt = $conn->prepare("
        UPDATE locations 
        SET name = :name,
            address = :address,
            photo = :photo
        WHERE location_id = :location_id
      ");

    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':photo', $photoName);
    $stmt->bindParam(':location_id', $locationId, PDO::PARAM_INT);

    $stmt->execute();

    $message = "Thông tin địa điểm đã được cập nhật thành công!";
    $messageType = "success";

    // Cập nhật thông tin địa điểm trong biến $location
    $location['name'] = $name;
    $location['address'] = $address;
    $location['photo'] = $photoName;

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
  <title>Chỉnh Sửa Địa điểm - Admin</title>
  <style>
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
    <h1 class="mb-4">Chỉnh Sửa Địa điểm</h1>

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./admin_locations.php">Danh sách địa điểm</a></li>
        <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa địa điểm</li>
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
      <div class="card-header bg-primary text-white">
        <i class="fas fa-edit"></i> Chỉnh sửa địa điểm: <?php echo htmlspecialchars($location['name']); ?>
      </div>
      <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
          <div class="form-group">
            <label for="name">
              <i class="fas fa-map-marked-alt"></i> Tên địa điểm <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="name" name="name" required
              value="<?php echo htmlspecialchars($location['name']); ?>">
          </div>

          <div class="form-group">
            <label for="photo">
              <i class="fas fa-image"></i> Hình ảnh
            </label>
            <?php if (!empty($location['photo']) && file_exists($targetDir . $location['photo'])): ?>
              <div class="mb-3">
                <p>Ảnh hiện tại:</p>
                <img src="<?php echo '../assets/images/' . htmlspecialchars($location['photo']); ?>"
                  alt="Ảnh địa điểm hiện tại" class="current-photo img-thumbnail">
              </div>
            <?php endif; ?>
            <div class="custom-file">
              <input type="file" class="custom-file-input" id="photo" name="photo" accept="image/*" onchange="previewPhoto(this)">
              <label class="custom-file-label" for="photo">Chọn hình ảnh mới...</label>
            </div>
            <small class="form-text text-muted">Để trống nếu muốn giữ nguyên ảnh cũ. Định dạng hỗ trợ: JPG, JPEG, PNG, GIF. Kích thước tối đa: 5MB.</small>
          </div>

          <div class="form-group">
            <label for="address">
              <i class="fas fa-address-card"></i> Địa chỉ
            </label>
            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($location['address'] ?? ''); ?></textarea>
            <small class="form-text text-muted">Địa chỉ cụ thể của địa điểm.</small>
          </div>

          <div class="form-group">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Lưu thay đổi
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