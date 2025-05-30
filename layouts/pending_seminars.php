<?php
session_start();
require_once '../config.php';

if (isset($_POST['approve_seminar']) && isset($_POST['seminar_id'])) {
  $seminarId = (int)$_POST['seminar_id'];

  $seminarStmt = $conn->prepare("UPDATE seminars SET status = 1 WHERE seminar_id = ?");
  $seminarStmt->execute([$seminarId]);

  $locationStmt = $conn->prepare("SELECT location_id FROM seminars WHERE seminar_id = ?");
  $locationStmt->execute([$seminarId]);
  $locationId = $locationStmt->fetchColumn();

  if ($locationId) {
    $updateLocationStmt = $conn->prepare("UPDATE locations SET status = 1 WHERE location_id = ?");
    $updateLocationStmt->execute([$locationId]);
  }

  $speakerStmt = $conn->prepare("
      SELECT DISTINCT speaker_id FROM agenda 
      WHERE seminar_id = ? AND speaker_id IS NOT NULL
    ");
  $speakerStmt->execute([$seminarId]);
  $speakerIds = $speakerStmt->fetchAll(PDO::FETCH_COLUMN);

  if (!empty($speakerIds)) {
    $placeholders = implode(',', array_fill(0, count($speakerIds), '?'));
    $updateSpeakersStmt = $conn->prepare("UPDATE speakers SET status = 1 WHERE speaker_id IN ($placeholders)");
    $updateSpeakersStmt->execute($speakerIds);
  }
  $_SESSION['message'] = "Hội thảo đã được phê duyệt thành công!";
  $_SESSION['message_type'] = "success";

  header('Location: ./pending_seminars.php');
  exit;
}

$stmt = $conn->prepare("
  SELECT s.*, u.full_name as creator_name, u.username, 
         l.name as location_name, l.status as location_status
  FROM seminars s
  INNER JOIN users u ON s.user_id = u.user_id
  LEFT JOIN locations l ON s.location_id = l.location_id
  WHERE s.status = 0
  ORDER BY s.start_time DESC
");
$stmt->execute();
$pendingSeminars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style-admin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Hội thảo chờ duyệt</title>
</head>

<body>
  <?php include_once './admin_sidebar.php'; ?>

  <div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1><i class="fas fa-clipboard-check"></i> Hội thảo chờ duyệt</h1>
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

    <?php if (empty($pendingSeminars)): ?>
      <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Không có hội thảo nào đang chờ duyệt.
      </div>
    <?php else: ?>
      <div class="card">
        <div class="card-header bg-primary text-white">
          <i class="fas fa-list"></i> Danh sách hội thảo chờ duyệt
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>Chủ đề</th>
                  <th>Người tạo</th>
                  <th>Thời gian</th>
                  <th>Địa điểm</th>
                  <th>Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pendingSeminars as $seminar): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($seminar['topic']); ?></td>
                    <td>
                      <?php echo htmlspecialchars($seminar['creator_name']); ?>
                      <small class="text-muted d-block"><?php echo htmlspecialchars($seminar['username']); ?></small>
                    </td>
                    <td>
                      <small>
                        <i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($seminar['start_time'])); ?><br>
                        <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($seminar['start_time'])); ?> -
                        <?php echo date('H:i', strtotime($seminar['end_time'])); ?>
                      </small>
                    </td>
                    <td>
                      <?php if (!empty($seminar['location_name'])): ?>
                        <?php echo htmlspecialchars($seminar['location_name']); ?>
                        <?php if ($seminar['location_status'] == 0): ?>
                          <span class="badge badge-warning">Chờ duyệt</span>
                        <?php endif; ?>
                      <?php else: ?>
                        <span class="text-muted">Chưa cập nhật</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <a href="./admin_seminar_view.php?id=<?php echo $seminar['seminar_id']; ?>" class="btn btn-sm btn-info mb-1" title="Xem chi tiết">
                        <i class="fas fa-eye"></i>
                      </a>

                      <button type="button" class="btn btn-sm btn-success mb-1" data-toggle="modal" data-target="#approveSeminarModal<?php echo $seminar['seminar_id']; ?>" title="Phê duyệt">
                        <i class="fas fa-check"></i>
                      </button>
                    </td>
                  </tr>

                  <!-- Modal phê duyệt -->
                  <div class="modal fade" id="approveSeminarModal<?php echo $seminar['seminar_id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                          <h5 class="modal-title">
                            <i class="fas fa-check-circle"></i> Phê duyệt hội thảo
                          </h5>
                          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <div class="modal-body">
                          <p>Bạn có chắc chắn muốn phê duyệt hội thảo "<strong><?php echo htmlspecialchars($seminar['topic']); ?></strong>"?</p>

                          <div class="alert alert-info">
                            <small><i class="fas fa-info-circle"></i> Khi phê duyệt, hội thảo này và các thành phần liên quan (địa điểm, diễn giả) sẽ được cập nhật trạng thái thành "Đã duyệt".</small>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                          <form method="post" action="">
                            <input type="hidden" name="seminar_id" value="<?php echo $seminar['seminar_id']; ?>">
                            <button type="submit" name="approve_seminar" class="btn btn-success">
                              <i class="fas fa-check"></i> Phê duyệt
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Bootstrap JS và jQuery -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>