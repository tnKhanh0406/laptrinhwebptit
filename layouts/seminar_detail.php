<?php

include '../config.php';
include '../layouts/header.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$stmt = $conn->prepare("SELECT * FROM seminars WHERE seminar_id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$seminar = $stmt->fetch(PDO::FETCH_ASSOC);

$locationStmt = $conn->prepare("SELECT * FROM locations WHERE location_id = :location_id");
$locationStmt->bindParam(':location_id', $seminar['location_id'], PDO::PARAM_INT);
$locationStmt->execute();
$location = $locationStmt->fetch(PDO::FETCH_ASSOC);

$speakersStmt = $conn->prepare("
  SELECT speakers.* FROM speakers
  JOIN agenda ON speakers.speaker_id = agenda.speaker_id
  WHERE agenda.seminar_id = :seminar_id
");
$speakersStmt->bindParam(':seminar_id', $id, PDO::PARAM_INT);
$speakersStmt->execute();
$speakers = $speakersStmt->fetchAll(PDO::FETCH_ASSOC);

$registrationsStmt = $conn->prepare("SELECT COUNT(*) as total FROM registrations WHERE seminar_id = :seminar_id");
$registrationsStmt->bindParam(':seminar_id', $id, PDO::PARAM_INT);
$registrationsStmt->execute();
$registeredCount = $registrationsStmt->fetch(PDO::FETCH_ASSOC)['total'];

$currentDateTime = new DateTime();
$startDateTime = new DateTime($seminar['start_time']);
$endDateTime = new DateTime($seminar['end_time']);

if ($startDateTime > $currentDateTime) {
  $status = 'upcoming';
  $statusText = 'Sắp diễn ra';
  $statusClass = 'success';
} elseif ($endDateTime < $currentDateTime) {
  $status = 'past';
  $statusText = 'Đã kết thúc';
  $statusClass = 'secondary';
} else {
  $status = 'ongoing';
  $statusText = 'Đang diễn ra';
  $statusClass = 'danger';
}

$registrationMessage = '';
$isRegistered = false;

if (isset($_SESSION['user_id'])) {
  $checkRegStmt = $conn->prepare("
    SELECT registration_id FROM registrations 
    WHERE user_id = :user_id AND seminar_id = :seminar_id
  ");
  $checkRegStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
  $checkRegStmt->bindParam(':seminar_id', $id, PDO::PARAM_INT);
  $checkRegStmt->execute();
  $isRegistered = $checkRegStmt->rowCount() > 0;

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register']) && !$isRegistered) {
      if ($registeredCount < $seminar['max_participants']) {
        $regStmt = $conn->prepare("
          INSERT INTO registrations (user_id, seminar_id, created_at) 
          VALUES (:user_id, :seminar_id, NOW())
        ");
        $regStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $regStmt->bindParam(':seminar_id', $id, PDO::PARAM_INT);

        if ($regStmt->execute()) {
          $registrationMessage = '<div class="alert alert-success">Đăng ký tham gia hội thảo thành công!</div>';
          $isRegistered = true;
          $registeredCount++;
        } else {
          $registrationMessage = '<div class="alert alert-danger">Có lỗi xảy ra khi đăng ký. Vui lòng thử lại!</div>';
        }
      } else {
        $registrationMessage = '<div class="alert alert-warning">Hội thảo đã đạt số lượng người tham gia tối đa!</div>';
      }
    }
    elseif (isset($_POST['unregister']) && $isRegistered) {
      $unregStmt = $conn->prepare("
        UPDATE registrations 
        SET status = 'cancelled'
        WHERE user_id = :user_id AND seminar_id = :seminar_id
      ");
      $unregStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
      $unregStmt->bindParam(':seminar_id', $id, PDO::PARAM_INT);

      if ($unregStmt->execute()) {
        $registrationMessage = '<div class="alert alert-info">Đã hủy đăng ký tham gia hội thảo!</div>';
        $isRegistered = false;
        $registeredCount--;
      } else {
        $registrationMessage = '<div class="alert alert-danger">Có lỗi xảy ra khi hủy đăng ký. Vui lòng thử lại!</div>';
      }
    }
  }
}
$agendaStmt = $conn->prepare("
    SELECT a.*, s.full_name as speaker_name, s.speaker_id, s.photo as speaker_photo 
    FROM agenda a
    LEFT JOIN speakers s ON a.speaker_id = s.speaker_id
    WHERE a.seminar_id = :seminar_id
    ORDER BY a.start_time ASC
");
$agendaStmt->bindParam(':seminar_id', $id, PDO::PARAM_INT);
$agendaStmt->execute();
$agendaItems = $agendaStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css"
    integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/seminar_detail.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <title><?php echo htmlspecialchars($seminar['topic']); ?></title>
</head>

<body>
  <div class="container">
    <div class="seminar-detail">
      <?php echo $registrationMessage; ?>

      <img src="<?php echo htmlspecialchars('../assets/images/' . $seminar['photo']); ?>"
        alt="<?php echo htmlspecialchars($seminar['topic']); ?>"
        class="seminar-image">

      <div class="seminar-info">
        <h1 class="seminar-title">
          <?php echo htmlspecialchars($seminar['topic']); ?>
          <span class="badge badge-<?php echo $statusClass; ?> badge-status">
            <?php echo $statusText; ?>
          </span>
        </h1>

        <div class="row">
          <div class="col-md-8">
            <div class="details-container">
              <h4>Thông tin hội thảo</h4>
              <p><strong><i class="fas fa-calendar-alt"></i> Thời gian:</strong>
                <?php
                echo $startDateTime->format('d/m/Y, H:i') . ' - ' . $endDateTime->format('H:i');
                ?>
              </p>
              <p><strong><i class="fas fa-map-marker-alt"></i> Địa điểm:</strong>
                <?php htmlspecialchars($location['name'] . ', ' . $location['address']); ?>
              </p>
              <p><strong><i class="fas fa-tag"></i> Phân loại:</strong>
                <?php echo htmlspecialchars($seminar['category']); ?>
              </p>
              <p><strong><i class="fas fa-users"></i> Số người tham gia:</strong>
                <?php echo $registeredCount . '/' . $seminar['max_participants']; ?> người
              </p>

              <?php if (!empty($seminar['description'])): ?>
                <hr><h4>Mô tả hội thảo</h4>
                <p><?php echo nl2br(htmlspecialchars($seminar['description'])); ?></p>
              <?php endif; ?>
            </div>
          </div>

          <div class="col-md-4">
            <div class="details-container">
              <h4>Đăng ký tham gia</h4>
              <?php if ($status === 'past'): ?>
                <div class="alert alert-secondary text-center">
                  Hội thảo đã kết thúc
                </div>
              <?php elseif ($registeredCount >= $seminar['max_participants'] && !$isRegistered): ?>
                <div class="alert alert-warning text-center">
                  Hội thảo đã đủ người tham gia
                </div>
              <?php elseif (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                <form method="post">
                  <?php if (!$isRegistered): ?>
                    <button type="submit" name="register" class="btn btn-primary btn-block">
                      <i class="fas fa-check-circle"></i> Đăng ký tham gia
                    </button>
                  <?php else: ?>
                    <div class="alert alert-info text-center mb-3">
                      Bạn đã đăng ký tham gia hội thảo này
                    </div>
                    <button type="submit" name="unregister" class="btn btn-outline-danger btn-block">
                      <i class="fas fa-times-circle"></i> Hủy đăng ký
                    </button>
                  <?php endif; ?>
                </form>
              <?php else: ?>
                <div class="alert alert-info text-center">
                  <p>Vui lòng đăng nhập để đăng ký tham gia</p>
                  <a href="login.php" class="btn btn-primary btn-sm">Đăng nhập ngay</a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <?php if (!empty($agendaItems)): ?>
        <div class="details-container mt-4">
          <h4><i class="fas fa-calendar-day mr-2"></i>Lịch trình hội thảo</h4>
          <div class="agenda-timeline">
            <?php
            $seminarDate = $startDateTime->format('Y-m-d');
            $previousTime = null;
            foreach ($agendaItems as $item):
              $itemStart = new DateTime($item['start_time']);
              $itemEnd = new DateTime($item['end_time']);
            ?>
              <div class="agenda-item">
                <div class="agenda-time">
                  <?php echo $itemStart->format('H:i') . ' - ' . $itemEnd->format('H:i'); ?>
                </div>
                <div class="agenda-content">
                  <h5 class="agenda-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                  <?php if (!empty($item['speaker_id'])): ?>
                    <div class="agenda-speaker">
                      <img src="<?php echo htmlspecialchars('../assets/images/' . (!empty($item['speaker_photo']) ? $item['speaker_photo'] : 'default-speaker.jpg')); ?>"
                        alt="<?php echo htmlspecialchars($item['speaker_name']); ?>" class="agenda-speaker-img">
                      <span><b>Người trình bày: <?php echo htmlspecialchars($item['speaker_name']); ?></b></span>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($item['description'])): ?>
                    <p class="agenda-description"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($speakers)): ?>
        <div class="details-container mt-4">
          <h4>Diễn giả</h4>
          <div class="row">
            <?php foreach ($speakers as $speaker): ?>
              <div class="col-md-6">
                <div class="speaker-card">
                  <img src="<?php echo htmlspecialchars('../assets/images/' . (!empty($speaker['photo']) ? $speaker['photo'] : 'default-speaker.jpg')); ?>"
                    alt="<?php echo htmlspecialchars($speaker['full_name']); ?>"
                    class="speaker-img">
                  <div class="speaker-info">
                    <h5 class="speaker-name"><?php echo htmlspecialchars($speaker['full_name']); ?></h5>
                    <p class="speaker-bio">
                      <?php echo htmlspecialchars(substr($speaker['bio'] ?? '', 0, 100) . (strlen($speaker['bio'] ?? '') > 100 ? '...' : '')); ?>
                    </p>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="text-center back-btn">
        <a href="seminars.php" class="btn btn-outline-secondary">
          <i class="fas fa-arrow-left"></i> Quay lại danh sách hội thảo
        </a>
      </div>
    </div>
  </div>

  <?php include '../layouts/footer.php'; ?>

  <!-- Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>