<?php
session_start();
require_once '../config.php';

$userId = $_SESSION['user_id'];

if (isset($_POST['cancel_registration']) && isset($_POST['registration_id'])) {
  $registrationId = (int)$_POST['registration_id'];
  
  try {
    // Kiểm tra xem đăng ký có thuộc về người dùng này không
    $checkStmt = $conn->prepare("
      SELECT registration_id FROM registrations 
      WHERE registration_id = ? AND user_id = ?
    ");
    $checkStmt->execute([$registrationId, $userId]);
    
    if ($checkStmt->rowCount() > 0) {
      $deleteStmt = $conn->prepare("DELETE FROM registrations WHERE registration_id = ?");
      $deleteStmt->execute([$registrationId]);
      
      $_SESSION['message'] = "Đã hủy đăng ký tham gia hội thảo thành công!";
      $_SESSION['message_type'] = "success";
    } else {
      $_SESSION['message'] = "Bạn không có quyền hủy đăng ký này!";
      $_SESSION['message_type'] = "danger";
    }
  } catch (PDOException $e) {
    $_SESSION['message'] = "Lỗi khi hủy đăng ký: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
  }
  
  header('Location: ./my_registrations.php');
  exit;
}

// Lấy danh sách hội thảo đã đăng ký
$stmt = $conn->prepare("
  SELECT r.registration_id, r.status,
         s.seminar_id, s.topic, s.start_time, s.end_time, s.category, s.photo,
         l.name as location_name
  FROM registrations r
  INNER JOIN seminars s ON r.seminar_id = s.seminar_id
  LEFT JOIN locations l ON s.location_id = l.location_id
  WHERE r.user_id = ? AND s.status = 1
  ORDER BY s.start_time
");
$stmt->execute([$userId]);
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hội thảo đã đăng ký</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .registration-card {
      margin-bottom: 20px;
      transition: transform 0.3s;
      position: relative;
    }
    
    .registration-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .seminar-img {
      height: 180px;
      object-fit: cover;
    }
    
    .status-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      z-index: 10;
    }
  </style>
</head>

<body>
  <?php include 'header.php'; ?>

  <div class="container mt-5 mb-5">
    <div class="row mb-4">
      <div class="col-md-12">
        <h1><i class="fas fa-clipboard-list"></i> Hội thảo đã đăng ký</h1>
      </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
      <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
    <?php endif; ?>

    <div class="card">
      <div class="card-header bg-primary text-white">
        <h3 class="mb-0"><i class="fas fa-list"></i> Danh sách hội thảo</h3>
      </div>
      <div class="card-body">
        <?php if (empty($registrations)): ?>
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Bạn chưa đăng ký tham gia hội thảo nào.
          </div>
        <?php else: ?>
          <div class="row">
            <?php foreach ($registrations as $seminar): ?>
              <div class="col-md-6 col-lg-4">
                <div class="card registration-card">
                  <?php if ($seminar['photo']): ?>
                    <img src="../assets/images/<?php echo htmlspecialchars($seminar['photo']); ?>" alt="<?php echo htmlspecialchars($seminar['topic']); ?>" class="card-img-top seminar-img">
                  <?php else: ?>
                    <img src="../assets/images/default-seminar.jpg" alt="Ảnh mặc định" class="card-img-top seminar-img">
                  <?php endif; ?>
                  
                  <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($seminar['topic']); ?></h5>
                    
                    <p class="card-text">
                      <small class="text-muted">
                        <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($seminar['start_time'])); ?><br>
                        <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($seminar['start_time'])); ?> - <?php echo date('H:i', strtotime($seminar['end_time'])); ?><br>
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($seminar['location_name'] ?? 'Chưa cập nhật'); ?><br>
                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($seminar['category']); ?>
                      </small>
                    </p>
                    
                    <div class="mt-3">
                      <a href="./seminar_detail.php?id=<?php echo $seminar['seminar_id']; ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-info-circle"></i> Chi tiết
                      </a>
                      
                      <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#cancelModal<?php echo $seminar['registration_id']; ?>">
                        <i class="fas fa-times-circle"></i> Hủy đăng ký
                      </button>
                      
                      <!-- Modal xác nhận hủy đăng ký -->
                      <div class="modal fade" id="cancelModal<?php echo $seminar['registration_id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                              <h5 class="modal-title">Xác nhận hủy đăng ký</h5>
                              <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <p>Bạn có chắc chắn muốn hủy đăng ký tham gia hội thảo "<strong><?php echo htmlspecialchars($seminar['topic']); ?></strong>" không?</p>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                              <form method="post" action="">
                                <input type="hidden" name="registration_id" value="<?php echo $seminar['registration_id']; ?>">
                                <button type="submit" name="cancel_registration" class="btn btn-danger">
                                  <i class="fas fa-times-circle"></i> Hủy đăng ký
                                </button>
                              </form>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS và jQuery -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>