<?php
session_start();
require_once '../config.php';
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
        SELECT s.*, l.name as location_name, 
               (SELECT COUNT(*) FROM registrations WHERE seminar_id = s.seminar_id AND status = 'confirmed') as registered_count
        FROM seminars s
        LEFT JOIN locations l ON s.location_id = l.location_id
        WHERE s.user_id = ?
        ORDER BY s.start_time DESC
    ");
$stmt->execute([$user_id]);
$seminars = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['delete_seminar']) && isset($_POST['seminar_id'])) {
  $seminarId = (int)$_POST['seminar_id'];

  $checkStmt = $conn->prepare("
            SELECT seminar_id, status FROM seminars 
            WHERE seminar_id = ? AND user_id = ?
        ");
  $checkStmt->execute([$seminarId, $user_id]);
  $seminar = $checkStmt->fetch(PDO::FETCH_ASSOC);

  if ($seminar && $seminar['status'] == 0) {
    $deleteAgendaStmt = $conn->prepare("DELETE FROM agenda WHERE seminar_id = ?");
    $deleteAgendaStmt->execute([$seminarId]);

    $deleteSeminarStmt = $conn->prepare("DELETE FROM seminars WHERE seminar_id = ?");
    $deleteSeminarStmt->execute([$seminarId]);

    $_SESSION['message'] = "Đã xóa hội thảo thành công.";
    $_SESSION['message_type'] = "success";
    header('Location: my_seminars.php');
    exit;
  } else {
    $_SESSION['message'] = "Không thể xóa hội thảo này. Có thể hội thảo đã được duyệt hoặc không tồn tại.";
    $_SESSION['message_type'] = "danger";
  }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hội thảo đã đề xuất</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .status-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      z-index: 10;
    }

    .seminar-card {
      margin-bottom: 20px;
      transition: transform 0.3s;
      position: relative;
    }

    .seminar-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .seminar-img {
      height: 180px;
      object-fit: cover;
    }
  </style>
</head>

<body>
  <?php include 'header.php'; ?>

  <div class="container mt-5 mb-5">
    <div class="row mb-4">
      <div class="col-md-6">
        <h1>
          <i class="fas fa-chalkboard-teacher"></i> Hội thảo đã đề xuất
        </h1>
      </div>
      <div class="col-md-6 text-right">
        <a href="admin_seminar_create.php" class="btn btn-primary">
          <i class="fas fa-plus-circle"></i> Tạo hội thảo mới
        </a>
      </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
      <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <?php unset($_SESSION['message']);
      unset($_SESSION['message_type']); ?>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
      <div class="alert alert-danger">
        <?php echo $errorMessage; ?>
      </div>
    <?php endif; ?>

    <?php if (empty($seminars)): ?>
      <div class="alert alert-info" style="margin-bottom: 310px;">
        <i class="fas fa-info-circle"></i> Bạn chưa đề xuất hội thảo nào.
        <a href="admin_seminar_create.php" class="btn btn-info btn-sm ml-3">Tạo hội thảo mới</a>
      </div>
    <?php else: ?>
      <div class="row">
        <?php foreach ($seminars as $seminar): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card seminar-card">
              <?php if ($seminar['status'] == 0): ?>
                <div class="status-badge badge badge-warning">Chờ duyệt</div>
              <?php else: ?>
                <div class="status-badge badge badge-success">Đã duyệt</div>
              <?php endif; ?>

              <?php if (!empty($seminar['photo'])): ?>
                <img src="../assets/images/<?php echo htmlspecialchars($seminar['photo']); ?>" class="card-img-top seminar-img" alt="<?php echo htmlspecialchars($seminar['topic']); ?>">
              <?php else: ?>
                <img src="assets/images/default-seminar.jpg" class="card-img-top seminar-img" alt="Default seminar image">
              <?php endif; ?>

              <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($seminar['topic']); ?></h5>

                <p class="card-text small">
                  <i class="fas fa-clock"></i>
                  <?php echo date('d/m/Y H:i', strtotime($seminar['start_time'])); ?> -
                  <?php echo date('d/m/Y H:i', strtotime($seminar['end_time'])); ?>
                </p>

                <p class="card-text small">
                  <i class="fas fa-map-marker-alt"></i>
                  <?php echo htmlspecialchars($seminar['location_name'] ?? 'Chưa cập nhật'); ?>
                  <?php if (isset($seminar['location_status']) && $seminar['location_status'] == 0): ?>
                    <span class="badge badge-secondary">Chờ duyệt</span>
                  <?php endif; ?>
                </p>

                <?php if ($seminar['max_participants'] > 0): ?>
                  <p class="card-text small">
                    <i class="fas fa-users"></i>
                    <?php echo $seminar['registered_count']; ?>/<?php echo $seminar['max_participants']; ?> người đăng ký
                  </p>
                <?php endif; ?>

                <div class="mt-3">
                  <a href="./admin_seminar_view.php?id=<?php echo $seminar['seminar_id']; ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-info-circle"></i> Chi tiết
                  </a>

                  <?php if ($seminar['status'] == 0): ?>
                    <a href="./admin_seminar_edit.php?id=<?php echo $seminar['seminar_id']; ?>" class="btn btn-outline-primary btn-sm">
                      <i class="fas fa-edit"></i> Sửa
                    </a>

                    <button type="button" class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#deleteSeminarModal<?php echo $seminar['seminar_id']; ?>">
                      <i class="fas fa-trash"></i> Xóa
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Modal xác nhận xóa -->
            <div class="modal fade" id="deleteSeminarModal<?php echo $seminar['seminar_id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                      <i class="fas fa-exclamation-triangle"></i> Xác nhận xóa
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    Bạn có chắc chắn muốn xóa hội thảo "<strong><?php echo htmlspecialchars($seminar['topic']); ?></strong>" không?<br>
                    Hành động này không thể hoàn tác.
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <form method="post" action="">
                      <input type="hidden" name="seminar_id" value="<?php echo $seminar['seminar_id']; ?>">
                      <button type="submit" name="delete_seminar" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Xóa
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <?php include 'footer.php'; ?>

  <!-- Bootstrap JS và jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>