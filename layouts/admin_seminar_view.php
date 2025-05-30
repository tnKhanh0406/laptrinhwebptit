<?php
session_start();

require_once '../config.php';

$seminarId = (int)$_GET['id'];
$seminar = null;
$location = null;
$agendaItems = [];
$registrations = [];

$stmt = $conn->prepare("
    SELECT s.*, l.name as location_name, l.address as location_address, l.photo as location_photo
    FROM seminars s
    LEFT JOIN locations l ON s.location_id = l.location_id
    WHERE s.seminar_id = :seminar_id
  ");
$stmt->bindParam(':seminar_id', $seminarId, PDO::PARAM_INT);
$stmt->execute();

$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
  header('Location: ./admin_seminars.php');
  exit;
}

$seminar = $result;

$agendaStmt = $conn->prepare("
    SELECT a.*, spk.full_name as speaker_name, spk.photo as speaker_photo
    FROM agenda a
    LEFT JOIN speakers spk ON a.speaker_id = spk.speaker_id
    WHERE a.seminar_id = :seminar_id
    ORDER BY a.start_time ASC
  ");
$agendaStmt->bindParam(':seminar_id', $seminarId, PDO::PARAM_INT);
$agendaStmt->execute();
$agendaItems = $agendaStmt->fetchAll(PDO::FETCH_ASSOC);

$regStmt = $conn->prepare("
    SELECT r.*, u.username, u.full_name
    FROM registrations r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.seminar_id = :seminar_id
    ORDER BY r.created_at DESC
  ");
$regStmt->bindParam(':seminar_id', $seminarId, PDO::PARAM_INT);
$regStmt->execute();
$registrations = $regStmt->fetchAll(PDO::FETCH_ASSOC);

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
  <title>Chi tiết Hội thảo</title>
</head>

<body>
  <?php
  if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user') {
    include_once './header.php';
  } else {
    include_once './admin_sidebar.php';
  } ?>

  <div class="<?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 'user') ? 'container mt-5 mb-5' : 'content'; ?>">
    <h1 class="mb-4">Chi tiết Hội thảo</h1>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger">
        <?php echo $error; ?>
      </div>
    <?php else: ?>
      <div class="card mb-4">
        <div class="card-header bg-primary text-white">
          <div class="d-flex justify-content-between align-items-center">
            <span><i class="fas fa-chalkboard-teacher"></i> Thông tin hội thảo</span>
          </div>
        </div>
        <div class="card-body">
          <h2><?php echo htmlspecialchars($seminar['topic']); ?></h2>
          <div class="badge badge-info mb-3"><?php echo htmlspecialchars($seminar['category']); ?></div>
          <?php if ($seminar['status'] == 0): ?>
            <div class="alert alert-warning">
              <i class="fas fa-exclamation-circle"></i> Hội thảo này đang chờ phê duyệt

              <?php if ($_SESSION['role'] === 'admin'): ?>
                <form method="post" class="d-inline ml-3" action="pending_seminars.php">
                  <input type="hidden" name="seminar_id" value="<?php echo $seminarId; ?>">
                  <button type="submit" name="approve_seminar" class="btn btn-sm btn-success">
                    <i class="fas fa-check"></i> Phê duyệt ngay
                  </button>
                </form>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <div class="row mb-3">
            <div class="col-md-6">
              <p>
                <i class="fas fa-calendar"></i> <strong>Thời gian:</strong><br>
                <?php
                echo date('d/m/Y H:i', strtotime($seminar['start_time']));
                ?> - <?php
                      echo date('d/m/Y H:i', strtotime($seminar['end_time']));
                      ?>
              </p>
            </div>
            <div class="col-md-6">
              <p>
                <i class="fas fa-users"></i> <strong>Số người tham gia tối đa:</strong>
                <?php echo $seminar['max_participants']; ?>
              </p>
            </div>
          </div>

          <div class="mb-4">
            <h5><i class="fas fa-align-left"></i> Nội dung</h5>
            <div class="p-3 bg-light">
              <?php echo nl2br(htmlspecialchars($seminar['content'])); ?>
            </div>
          </div>

          <div class="mb-4">
            <h5><i class="fa-solid fa-image"></i> Ảnh hội thảo</h5>
            <div class="col-md-4">
              <img src="<?php echo htmlspecialchars('../assets/images/' . $seminar['photo']); ?>"
                class="img-fluid rounded" alt="<?php echo htmlspecialchars($seminar['topic']); ?>">
            </div>
          </div>

          <?php if ($seminar['location_name']): ?>
            <div class="mb-4">
              <h5><i class="fas fa-map-marker-alt"></i> Địa điểm</h5>
              <div class="card">
                <div class="card-body">
                  <div class="row">
                    <?php if ($seminar['location_photo']): ?>
                      <div class="col-md-4">
                        <img src="<?php echo htmlspecialchars('../assets/images/' . $seminar['location_photo']); ?>"
                          class="img-fluid rounded" alt="<?php echo htmlspecialchars($seminar['location_name']); ?>">
                      </div>
                    <?php endif; ?>
                    <div class="col-md-<?php echo $seminar['location_photo'] ? '8' : '12'; ?>">
                      <h5><?php echo htmlspecialchars($seminar['location_name']); ?></h5>
                      <?php if ($seminar['location_address']): ?>
                        <p><i class="fas fa-map"></i> <?php echo htmlspecialchars($seminar['location_address']); ?></p>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header bg-success text-white">
          <div class="d-flex justify-content-between align-items-center">
            <span><i class="fas fa-calendar-alt"></i> Chương trình hội thảo</span>
            <a href="./admin_agenda_create.php?seminar_id=<?php echo $seminarId; ?>" class="btn btn-sm btn-light">
              <i class="fas fa-plus"></i> Thêm mới
            </a>
          </div>
        </div>
        <div class="card-body">
          <?php if (empty($agendaItems)): ?>
            <div class="alert alert-info">
              Chưa có chương trình nào cho hội thảo này.
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead class="thead-light">
                  <tr>
                    <th>Thời gian</th>
                    <th>Tiêu đề</th>
                    <th>Diễn giả</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($agendaItems as $item): ?>
                    <tr>
                      <td>
                        <?php echo date('H:i', strtotime($item['start_time'])); ?> -
                        <?php echo date('H:i', strtotime($item['end_time'])); ?>
                        <div class="small text-muted">
                          <?php echo date('d/m/Y', strtotime($item['start_time'])); ?>
                        </div>
                      </td>
                      <td>
                        <?php echo htmlspecialchars($item['title']); ?>
                        <?php if (!empty($item['description'])): ?>
                          <button class="btn btn-sm btn-link" type="button" data-toggle="collapse"
                            data-target="#description-<?php echo $item['agenda_id']; ?>">
                            <i class="fas fa-info-circle"></i>
                          </button>
                          <div class="collapse mt-2" id="description-<?php echo $item['agenda_id']; ?>">
                            <div class="card card-body small">
                              <?php echo nl2br(htmlspecialchars($item['description'])); ?>
                            </div>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if ($item['speaker_name']): ?>
                          <div class="d-flex align-items-center">
                            <?php if ($item['speaker_photo']): ?>
                              <img src="<?php echo htmlspecialchars('../assets/images/' . $item['speaker_photo']); ?>"
                                class="rounded-circle mr-2" width="30" height="30" alt="">
                            <?php endif; ?>
                            <?php echo htmlspecialchars($item['speaker_name']); ?>
                          </div>
                        <?php else: ?>
                          <span class="text-muted">Không có</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <a href="./admin_agenda_edit.php?id=<?php echo $item['agenda_id']; ?>&seminar_id=<?php echo $seminarId; ?>" class="btn btn-sm btn-warning">
                          <i class="fas fa-edit"></i>
                        </a>
                        <a href="./admin_agenda_delete.php?id=<?php echo $item['agenda_id']; ?>&seminar_id=<?php echo $seminarId; ?>" class="btn btn-sm btn-danger">
                          <i class="fas fa-trash"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-header bg-info text-white">
          <i class="fas fa-user-check"></i> Danh sách đăng ký tham gia (<?php echo count($registrations); ?>/<?php echo $seminar['max_participants']; ?>)
        </div>
        <div class="card-body">
          <?php if (empty($registrations)): ?>
            <div class="alert alert-info">
              Chưa có người đăng ký tham gia hội thảo này.
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead class="thead-light">
                  <tr>
                    <th>Họ tên</th>
                    <th>Thời gian đăng ký</th>
                    <th>Trạng thái</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($registrations as $reg): ?>
                    <tr>
                      <td>
                        <?php echo htmlspecialchars($reg['full_name']); ?>
                        <div class="small text-muted">@<?php echo htmlspecialchars($reg['username']); ?></div>
                      </td>
                      <td><?php echo date('d/m/Y H:i', strtotime($reg['created_at'])); ?></td>
                      <td>
                        <?php
                        $status = '';
                        $badgeClass = '';

                        switch ($reg['status']) {
                          case 'pending':
                            $status = 'Chờ xác nhận';
                            $badgeClass = 'warning';
                            break;
                          case 'confirmed':
                            $status = 'Đã xác nhận';
                            $badgeClass = 'success';
                            break;
                          case 'cancelled':
                            $status = 'Đã hủy';
                            $badgeClass = 'danger';
                            break;
                        }
                        ?>
                        <span class="badge badge-<?php echo $badgeClass; ?>">
                          <?php echo $status; ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <?php
  if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user') {
    include_once './footer.php';
  } ?>

  <!-- Bootstrap JS và jQuery -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>