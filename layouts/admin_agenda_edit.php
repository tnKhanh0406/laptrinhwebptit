<?php
session_start();

require_once '../config.php';

$message = '';
$messageType = '';
$agenda = null;
$seminar = null;

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['seminar_id']) || !is_numeric($_GET['seminar_id'])) {
  header('Location: ./admin_seminars.php');
  exit;
}

$agendaId = (int)$_GET['id'];
$seminarId = (int)$_GET['seminar_id'];

$stmt = $conn->prepare("SELECT * FROM seminars WHERE seminar_id = :seminar_id");
$stmt->bindParam(':seminar_id', $seminarId, PDO::PARAM_INT);
$stmt->execute();
$seminar = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$seminar) {
  header('Location: ./admin_seminars.php');
  exit;
}

$stmt = $conn->prepare("SELECT * FROM agenda WHERE agenda_id = :agenda_id AND seminar_id = :seminar_id");
$stmt->bindParam(':agenda_id', $agendaId, PDO::PARAM_INT);
$stmt->bindParam(':seminar_id', $seminarId, PDO::PARAM_INT);
$stmt->execute();

$agenda = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$agenda) {
  header('Location: ./admin_seminar_edit.php?id=' . $seminarId);
  exit;
}
$speakerStmt = $conn->query("SELECT speaker_id, full_name FROM speakers ORDER BY full_name");
$speakers = $speakerStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $speakerId = !empty($_POST['speaker_id']) ? (int)$_POST['speaker_id'] : null;
  $startTime = $_POST['start_time'] ?? '';
  $endTime = $_POST['end_time'] ?? '';
  $description = trim($_POST['description'] ?? '');

  $errors = [];

  if (empty($title)) {
    $errors[] = "Tiêu đề không được để trống";
  }

  if (empty($startTime)) {
    $errors[] = "Thời gian bắt đầu không được để trống";
  }

  if (empty($endTime)) {
    $errors[] = "Thời gian kết thúc không được để trống";
  } elseif ($startTime >= $endTime) {
    $errors[] = "Thời gian kết thúc phải sau thời gian bắt đầu";
  }

  $seminarStartTime = new DateTime($seminar['start_time']);
  $seminarEndTime = new DateTime($seminar['end_time']);
  $agendaStartTime = new DateTime($startTime);
  $agendaEndTime = new DateTime($endTime);

  if ($agendaStartTime < $seminarStartTime || $agendaEndTime > $seminarEndTime) {
    $errors[] = "Thời gian chương trình phải nằm trong khoảng thời gian của hội thảo";
  }

  if (empty($errors)) {
    $stmt = $conn->prepare("
        UPDATE agenda 
        SET speaker_id = :speaker_id,
            title = :title,
            start_time = :start_time,
            end_time = :end_time,
            description = :description
        WHERE agenda_id = :agenda_id AND seminar_id = :seminar_id
      ");

    $stmt->bindParam(':speaker_id', $speakerId, PDO::PARAM_INT);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':start_time', $startTime);
    $stmt->bindParam(':end_time', $endTime);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':agenda_id', $agendaId, PDO::PARAM_INT);
    $stmt->bindParam(':seminar_id', $seminarId, PDO::PARAM_INT);

    $stmt->execute();

    $message = "Chương trình đã được cập nhật thành công!";
    $messageType = "success";

    $agenda['speaker_id'] = $speakerId;
    $agenda['title'] = $title;
    $agenda['start_time'] = $startTime;
    $agenda['end_time'] = $endTime;
    $agenda['description'] = $description;

    header('refresh:1.5;url=./admin_seminar_edit.php?id=' . $seminarId);
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <title>Chỉnh sửa Chương trình</title>
</head>

<body>
  <?php include_once './admin_sidebar.php'; ?>

  <div class="content">
    <h1 class="mb-4">Chỉnh sửa Chương trình</h1>

    <?php if (!empty($message)): ?>
      <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    <?php endif; ?>

    <div class="card mb-4">
      <div class="card-header bg-info text-white">
        <i class="fas fa-info-circle"></i> Hội thảo: <?php echo htmlspecialchars($seminar['topic']); ?>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <p><strong>Thời gian bắt đầu:</strong>
              <?php echo date('d/m/Y H:i', strtotime($seminar['start_time'])); ?>
            </p>
          </div>
          <div class="col-md-6">
            <p><strong>Thời gian kết thúc:</strong>
              <?php echo date('d/m/Y H:i', strtotime($seminar['end_time'])); ?>
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header bg-primary text-white">
        <i class="fas fa-calendar-alt"></i> Thông tin chương trình
      </div>
      <div class="card-body">
        <form method="POST" action="">
          <div class="form-group">
            <label for="title">
              <i class="fas fa-heading"></i> Tiêu đề <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="title" name="title" required
              value="<?php echo htmlspecialchars($agenda['title']); ?>">
          </div>

          <div class="form-group">
            <label for="speaker_id">
              <i class="fas fa-microphone"></i> Diễn giả
            </label>
            <select class="form-control" id="speaker_id" name="speaker_id">
              <option value="">-- Chọn diễn giả --</option>
              <?php foreach ($speakers as $speaker): ?>
                <option value="<?php echo $speaker['speaker_id']; ?>"
                  <?php echo ($agenda['speaker_id'] == $speaker['speaker_id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($speaker['full_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="start_time">
                  <i class="fas fa-clock"></i> Thời gian bắt đầu <span class="text-danger">*</span>
                </label>
                <input type="datetime-local" class="form-control" id="start_time" name="start_time" required
                  value="<?php echo date('Y-m-d\TH:i', strtotime($agenda['start_time'])); ?>">
                <small class="form-text text-muted">Phải nằm trong khoảng thời gian của hội thảo</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="end_time">
                  <i class="fas fa-clock"></i> Thời gian kết thúc <span class="text-danger">*</span>
                </label>
                <input type="datetime-local" class="form-control" id="end_time" name="end_time" required
                  value="<?php echo date('Y-m-d\TH:i', strtotime($agenda['end_time'])); ?>">
                <small class="form-text text-muted">Phải sau thời gian bắt đầu và trong khoảng thời gian của hội thảo</small>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="description">
              <i class="fas fa-align-left"></i> Mô tả
            </label>
            <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($agenda['description'] ?? ''); ?></textarea>
          </div>

          <div class="form-group">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Lưu thay đổi
            </button>
            <a href="./admin_seminar_edit.php?id=<?php echo $seminarId; ?>" class="btn btn-secondary ml-2">
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
</body>

</html>