<?php
session_start();

require_once '../config.php';

$message = '';
$messageType = '';

$targetDir = "../assets/images/";

$userId = $_SESSION['user_id'];
$locationStmt = $conn->prepare("SELECT location_id, name, status FROM locations WHERE status = 1 OR user_id = ? ORDER BY status DESC, name ASC");
$locationStmt->execute([$userId]);
$locations = $locationStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $topic = trim($_POST['topic'] ?? '');
  $content = trim($_POST['content'] ?? '');
  $startTime = $_POST['start_time'] ?? '';
  $endTime = $_POST['end_time'] ?? '';
  $locationId = !empty($_POST['location_id']) ? (int)$_POST['location_id'] : null;
  $maxParticipants = !empty($_POST['max_participants']) ? (int)$_POST['max_participants'] : null;
  $category = trim($_POST['category'] ?? '');

  $errors = [];

  if (empty($topic)) {
    $errors[] = "Chủ đề không được để trống";
  }

  if (empty($content)) {
    $errors[] = "Nội dung không được để trống";
  }

  if (empty($startTime)) {
    $errors[] = "Thời gian bắt đầu không được để trống";
  }

  if (empty($endTime)) {
    $errors[] = "Thời gian kết thúc không được để trống";
  } elseif ($startTime >= $endTime) {
    $errors[] = "Thời gian kết thúc phải sau thời gian bắt đầu";
  }

  if (empty($category)) {
    $errors[] = "Danh mục không được để trống";
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
    if ($photoPath && !move_uploaded_file($_FILES["photo"]["tmp_name"], $photoPath)) {
      throw new Exception("Không thể tải lên hình ảnh. Vui lòng thử lại sau.");
    }

    $stmt = $conn->prepare("
        INSERT INTO seminars (topic, content, start_time, end_time, location_id, max_participants, category, photo, status, user_id) 
        VALUES (:topic, :content, :start_time, :end_time, :location_id, :max_participants, :category, :photo, :status, :user_id)
      ");

    $stmt->bindParam(':topic', $topic);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':start_time', $startTime);
    $stmt->bindParam(':end_time', $endTime);
    $stmt->bindParam(':location_id', $locationId, PDO::PARAM_INT);
    $stmt->bindParam(':max_participants', $maxParticipants, PDO::PARAM_INT);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':photo', $photoName);
    $stmt->bindValue(':status', ($_SESSION['role'] === 'admin') ? 1 : 0);
    $stmt->bindValue(':user_id', $_SESSION['user_id']);

    $stmt->execute();

    $message = "Hội thảo đã được tạo thành công!";
    $messageType = "success";

    $topic = $content = $startTime = $endTime = $category = '';
    $locationId = $maxParticipants = null;
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
  <title>Thêm Hội Thảo</title>
</head>

<body>
  <?php
  if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user') {
    include_once './header.php';
  } else {
    include_once './admin_sidebar.php';
  } ?>

  <div class="<?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 'user') ? 'container mt-4 mb-4' : 'content'; ?>">
    <h1 class="mb-4">Thêm Hội Thảo Mới</h1>

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
            <label for="topic">
              <i class="fas fa-heading"></i> Chủ đề <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="topic" name="topic" required
              value="<?php echo htmlspecialchars($topic ?? ''); ?>">
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
            <label for="content">
              <i class="fas fa-align-left"></i> Nội dung <span class="text-danger">*</span>
            </label>
            <textarea class="form-control" id="content" name="content" rows="5" required><?php echo htmlspecialchars($content ?? ''); ?></textarea>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="start_time">
                  <i class="fas fa-clock"></i> Thời gian bắt đầu <span class="text-danger">*</span>
                </label>
                <input type="datetime-local" class="form-control" id="start_time" name="start_time" required
                  value="<?php echo htmlspecialchars($startTime ?? ''); ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="end_time">
                  <i class="fas fa-clock"></i> Thời gian kết thúc <span class="text-danger">*</span>
                </label>
                <input type="datetime-local" class="form-control" id="end_time" name="end_time" required
                  value="<?php echo htmlspecialchars($endTime ?? ''); ?>">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="location_id">
                  <i class="fas fa-map-marker-alt"></i> Địa điểm
                </label>
                <select class="form-control" id="location_id" name="location_id">
                  <option value="">-- Chọn địa điểm --</option>
                  <?php foreach ($locations as $location): ?>
                    <option value="<?php echo $location['location_id']; ?>"
                      <?php echo (isset($locationId) && $locationId == $location['location_id']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($location['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user'): ?>
                  <div class="input-group-append mt-3">
                    <a href="./admin_location_create.php" class="btn btn-outline-secondary">
                      <i class="fas fa-plus"></i> Thêm địa điểm
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="max_participants">
                  <i class="fas fa-users"></i> Số người tham gia tối đa
                </label>
                <input type="number" class="form-control" id="max_participants" name="max_participants" min="1"
                  value="<?php echo htmlspecialchars($maxParticipants ?? ''); ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="category">
                  <i class="fas fa-tag"></i> Danh mục <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="category" name="category" required
                  value="<?php echo htmlspecialchars($category ?? ''); ?>">
              </div>
            </div>
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Lưu
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>

  <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user'): ?>
    <?php include_once './footer.php'; ?>
  <?php endif; ?>

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