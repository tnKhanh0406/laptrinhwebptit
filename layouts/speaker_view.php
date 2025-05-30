<?php
session_start();
require_once '../config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
  header('Location: ./admin_speakers.php');
  exit;
}

$speakerId = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM speakers WHERE speaker_id = :speaker_id");
$stmt->bindParam(':speaker_id', $speakerId, PDO::PARAM_INT);
$stmt->execute();

$speaker = $stmt->fetch(PDO::FETCH_ASSOC);

$agendaStmt = $conn->prepare("
    SELECT a.*, e.topic as event_name, e.start_time as seminar_start, e.end_time as seminar_end,
           l.name as location_name
    FROM agenda a 
    JOIN seminars e ON a.seminar_id = e.seminar_id 
    LEFT JOIN locations l ON e.location_id = l.location_id
    WHERE a.speaker_id = :speaker_id 
    ORDER BY e.start_time DESC, a.start_time ASC
  ");
$agendaStmt->bindParam(':speaker_id', $speakerId, PDO::PARAM_INT);
$agendaStmt->execute();

$agendaItems = $agendaStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Chi tiết Diễn giả</title>
</head>

<body>
  <?php include '../layouts/header.php'; ?>
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>Chi tiết Diễn giả</h1>
      <a href="./speakers.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
      </a>
    </div>

    <?php if (isset($errorMessage)): ?>
      <div class="alert alert-danger">
        <?php echo $errorMessage; ?>
      </div>
    <?php elseif (isset($speaker)): ?>
      <div class="card mb-4">
        <div class="card-body">
          <div class="row">
            <div class="col-md-4 text-center">
              <?php if (!empty($speaker['photo']) && file_exists("../assets/images/" . $speaker['photo'])): ?>
                <img src="../assets/images/<?php echo htmlspecialchars($speaker['photo']); ?>"
                  alt="<?php echo htmlspecialchars($speaker['full_name']); ?>"
                  class="img-fluid rounded mb-3" style="max-height: 300px;">
              <?php else: ?>
                <img src="../assets/images/default-speaker.jpg"
                  alt="Default speaker image"
                  class="img-fluid rounded mb-3" style="max-height: 300px;">
              <?php endif; ?>
            </div>

            <div class="col-md-8">
              <h2><?php echo htmlspecialchars($speaker['full_name']); ?></h2>

              <div class="mb-3">
                <h5>Tiểu sử:</h5>
                <p><?php echo !empty($speaker['bio']) ? nl2br(htmlspecialchars($speaker['bio'])) : 'Chưa cập nhật'; ?></p>
              </div>

              <div class="mb-3">
                <h5>Mô tả:</h5>
                <p><?php echo !empty($speaker['description']) ? nl2br(htmlspecialchars($speaker['description'])) : 'Chưa cập nhật'; ?></p>
              </div>

              <?php if (!empty($speaker['company'])): ?>
                <div class="mb-3">
                  <h5>Công ty:</h5>
                  <p><?php echo htmlspecialchars($speaker['company']); ?></p>
                </div>
              <?php endif; ?>

              <?php if (!empty($speaker['position'])): ?>
                <div class="mb-3">
                  <h5>Chức vụ:</h5>
                  <p><?php echo htmlspecialchars($speaker['position']); ?></p>
                </div>
              <?php endif; ?>

              <?php if (!empty($speaker['email'])): ?>
                <div class="mb-3">
                  <h5>Email:</h5>
                  <p><a href="mailto:<?php echo htmlspecialchars($speaker['email']); ?>"><?php echo htmlspecialchars($speaker['email']); ?></a></p>
                </div>
              <?php endif; ?>

              <?php if (!empty($speaker['phone'])): ?>
                <div class="mb-3">
                  <h5>Điện thoại:</h5>
                  <p><?php echo htmlspecialchars($speaker['phone']); ?></p>
                </div>
              <?php endif; ?>

              <?php if (!empty($speaker['website'])): ?>
                <div class="mb-3">
                  <h5>Website:</h5>
                  <p><a href="<?php echo htmlspecialchars($speaker['website']); ?>" target="_blank"><?php echo htmlspecialchars($speaker['website']); ?></a></p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Lịch sử tham gia sự kiện</h5>
        </div>
        <div class="card-body">
          <?php if (isset($agendaItems) && !empty($agendaItems)): ?>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Sự kiện</th>
                    <th>Chủ đề</th>
                    <th>Thời gian</th>
                    <th>Địa điểm</th>
                  </tr>
                </thead>
                <tbody>
                <tbody>
                  <?php foreach ($agendaItems as $item): ?>
                    <tr>
                      <td>
                        <div>
                          <?php echo htmlspecialchars($item['event_name']); ?>
                        </div>
                        <div class="small text-muted">
                          <?php
                          echo date('d/m/Y', strtotime($item['seminar_start']));
                          ?>
                        </div>
                      </td>
                      <td><?php echo htmlspecialchars($item['title']); ?></td>
                      <td>
                        <?php
                        echo date('H:i', strtotime($item['start_time'])) . ' - ';
                        echo date('H:i', strtotime($item['end_time']));
                        ?>
                      </td>
                      <td>
                        <?php echo !empty($item['location_name']) ? htmlspecialchars($item['location_name']) : '<span class="text-muted">Chưa cập nhật</span>'; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-muted">Diễn giả này chưa tham gia sự kiện nào.</p>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <?php include '../layouts/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>