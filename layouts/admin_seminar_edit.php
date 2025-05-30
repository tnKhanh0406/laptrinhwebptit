<?php
session_start();

require_once '../config.php';

$message = '';
$messageType = '';
$seminar = null;

$targetDir = "../assets/images/";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: ./admin_seminars.php');
  exit;
}

$seminarId = (int)$_GET['id'];

try {
  $locationStmt = $conn->query("SELECT location_id, name FROM locations ORDER BY name");
  $locations = $locationStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $locations = [];
}

$stmt = $conn->prepare("SELECT * FROM seminars WHERE seminar_id = :seminar_id");
$stmt->bindParam(':seminar_id', $seminarId, PDO::PARAM_INT);
$stmt->execute();

$seminar = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$seminar) {
  header('Location: ./admin_seminars.php');
  exit;
}

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
  $photoName = $seminar['photo'];

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

      if (!empty($seminar['photo']) && file_exists($targetDir . $seminar['photo'])) {
        unlink($targetDir . $seminar['photo']);
      }
    }

    $stmt = $conn->prepare("
        UPDATE seminars 
        SET topic = :topic,
            content = :content,
            start_time = :start_time,
            end_time = :end_time,
            location_id = :location_id,
            max_participants = :max_participants,
            category = :category,
            photo = :photo
        WHERE seminar_id = :seminar_id
      ");

    $stmt->bindParam(':topic', $topic);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':start_time', $startTime);
    $stmt->bindParam(':end_time', $endTime);
    $stmt->bindParam(':location_id', $locationId, PDO::PARAM_INT);
    $stmt->bindParam(':max_participants', $maxParticipants, PDO::PARAM_INT);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':photo', $photoName);
    $stmt->bindParam(':seminar_id', $seminarId, PDO::PARAM_INT);

    $stmt->execute();

    $message = "Hội thảo đã được cập nhật thành công!";
    $messageType = "success";

    $seminar['topic'] = $topic;
    $seminar['content'] = $content;
    $seminar['start_time'] = $startTime;
    $seminar['end_time'] = $endTime;
    $seminar['location_id'] = $locationId;
    $seminar['max_participants'] = $maxParticipants;
    $seminar['category'] = $category;
    $seminar['photo'] = $photoName;

    header('refresh:1.5;url=./admin_seminars.php');
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
  <title>Chỉnh Sửa Hội Thảo</title>
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
    <h1 class="mb-4">Chỉnh Sửa Hội Thảo</h1>

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
        <i class="fas fa-edit"></i> Chỉnh sửa hội thảo: <?php echo htmlspecialchars($seminar['topic']); ?>
      </div>
      <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
          <div class="form-group">
            <label for="topic">
              <i class="fas fa-heading"></i> Chủ đề <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="topic" name="topic" required
              value="<?php echo htmlspecialchars($seminar['topic']); ?>">
          </div>

          <div class="form-group">
            <label for="photo">
              <i class="fas fa-image"></i> Hình ảnh
            </label>
            <?php if (!empty($seminar['photo']) && file_exists($targetDir . $seminar['photo'])): ?>
              <div class="mb-3">
                <p>Ảnh hiện tại:</p>
                <img src="<?php echo '../assets/images/' . htmlspecialchars($seminar['photo']); ?>"
                  alt="Ảnh hiện tại" class="current-photo img-thumbnail">
              </div>
            <?php endif; ?>
            <div class="custom-file">
              <input type="file" class="custom-file-input" id="photo" name="photo" accept="image/*" onchange="previewPhoto(this)">
              <label class="custom-file-label" for="photo">Chọn hình ảnh mới...</label>
            </div>
            <small class="form-text text-muted">Để trống nếu muốn giữ nguyên ảnh cũ. Định dạng hỗ trợ: JPG, JPEG, PNG, GIF. Kích thước tối đa: 5MB.</small>
          </div>

          <div class="form-group">
            <label for="content">
              <i class="fas fa-align-left"></i> Nội dung <span class="text-danger">*</span>
            </label>
            <textarea class="form-control" id="content" name="content" rows="5" required><?php echo htmlspecialchars($seminar['content']); ?></textarea>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="start_time">
                  <i class="fas fa-clock"></i> Thời gian bắt đầu <span class="text-danger">*</span>
                </label>
                <input type="datetime-local" class="form-control" id="start_time" name="start_time" required
                  value="<?php echo date('Y-m-d\TH:i', strtotime($seminar['start_time'])); ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="end_time">
                  <i class="fas fa-clock"></i> Thời gian kết thúc <span class="text-danger">*</span>
                </label>
                <input type="datetime-local" class="form-control" id="end_time" name="end_time" required
                  value="<?php echo date('Y-m-d\TH:i', strtotime($seminar['end_time'])); ?>">
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
                      <?php echo ($seminar['location_id'] == $location['location_id']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($location['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="max_participants">
                  <i class="fas fa-users"></i> Số người tham gia tối đa
                </label>
                <input type="number" class="form-control" id="max_participants" name="max_participants" min="1"
                  value="<?php echo htmlspecialchars($seminar['max_participants'] ?? ''); ?>">
                <small class="form-text text-muted">Để trống nếu không giới hạn số lượng.</small>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="category">
              <i class="fas fa-tag"></i> Danh mục <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="category" name="category" required
              value="<?php echo htmlspecialchars($seminar['category']); ?>">
          </div>

          <div class="form-group">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Lưu thay đổi
            </button>
            <a href="./admin_seminars.php" class="btn btn-secondary ml-2">
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