<?php
session_start();
require_once '../config.php';

if (isset($_GET['action']) && isset($_GET['id'])) {
  $registrationId = (int)$_GET['id'];

  if ($_GET['action'] == 'confirm' || $_GET['action'] == 'cancel') {
    $newStatus = $_GET['action'] == 'confirm' ? 'confirmed' : 'cancelled';

    if ($newStatus == 'cancelled') {
      $stmt = $conn->prepare("
            UPDATE registrations 
            SET status = :status
            WHERE registration_id = :registration_id
          ");

      $stmt->bindParam(':status', $newStatus);
      $stmt->bindParam(':registration_id', $registrationId, PDO::PARAM_INT);
      $stmt->execute();

      $message = "Đã hủy đăng ký thành công!";
      $messageType = "success";
    } else {
      $countStmt = $conn->prepare("
          SELECT COUNT(*) 
          FROM registrations 
          WHERE seminar_id = :seminar_id AND status = 'confirmed'
        ");
      $countStmt->bindParam(':seminar_id', $registration['seminar_id'], PDO::PARAM_INT);
      $countStmt->execute();
      $confirmedCount = $countStmt->fetchColumn();

      if ($confirmedCount >= $registration['max_participants']) {
        $message = "Không thể xác nhận đăng ký vì hội thảo đã đạt số lượng tham gia tối đa!";
        $messageType = "warning";
      } else {
        $stmt = $conn->prepare("
            UPDATE registrations 
            SET status = :status
            WHERE registration_id = :registration_id
          ");

        $stmt->bindParam(':status', $newStatus);
        $stmt->bindParam(':registration_id', $registrationId, PDO::PARAM_INT);
        $stmt->execute();

        $message = "Đã xác nhận đăng ký thành công!";
        $messageType = "success";
      }
    }
  }

  if ($_GET['action'] == 'delete') {
    $stmt = $conn->prepare("DELETE FROM registrations WHERE registration_id = :registration_id");
    $stmt->bindParam(':registration_id', $registrationId, PDO::PARAM_INT);
    $stmt->execute();

    $message = "Đã xóa đăng ký thành công!";
    $messageType = "success";
  }
}

// Phân trang
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($currentPage - 1) * $limit;

$seminarFilter = isset($_GET['seminar_id']) ? (int)$_GET['seminar_id'] : null;

$whereConditions = [];
$params = [];

if ($seminarFilter) {
  $whereConditions[] = "r.seminar_id = :seminar_id";
  $params[':seminar_id'] = $seminarFilter;
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

$countSql = "
    SELECT COUNT(*) 
    FROM registrations r
    INNER JOIN users u ON r.user_id = u.user_id
    INNER JOIN seminars s ON r.seminar_id = s.seminar_id
    $whereClause
  ";

$countStmt = $conn->prepare($countSql);
foreach ($params as $param => $value) {
  $countStmt->bindValue($param, $value);
}
$countStmt->execute();

$totalRegistrations = $countStmt->fetchColumn();
$totalPages = ceil($totalRegistrations / $limit);

$sql = "
    SELECT r.registration_id, r.status, r.created_at,
           u.user_id, u.username, u.full_name,
           s.seminar_id, s.topic, s.start_time, s.end_time,
           s.max_participants,
           (SELECT COUNT(*) FROM registrations WHERE seminar_id = s.seminar_id AND status = 'confirmed') as current_participants
    FROM registrations r
    INNER JOIN users u ON r.user_id = u.user_id
    INNER JOIN seminars s ON r.seminar_id = s.seminar_id
    $whereClause
    ORDER BY r.created_at DESC
    LIMIT :limit OFFSET :offset
  ";

$stmt = $conn->prepare($sql);
foreach ($params as $param => $value) {
  $stmt->bindValue($param, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$seminarStmt = $conn->query("SELECT seminar_id, topic FROM seminars ORDER BY start_time DESC");
$seminars = $seminarStmt->fetchAll(PDO::FETCH_ASSOC);

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
  <title>Quản lý Đăng ký Hội thảo</title>
  <style>
    .badge-pending {
      background-color: #ffc107;
      color: #212529;
    }

    .badge-confirmed {
      background-color: #28a745;
      color: white;
    }

    .badge-cancelled {
      background-color: #dc3545;
      color: white;
    }

    .seminar-info {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 300px;
    }

    .status-dropdown {
      min-width: 160px;
    }
  </style>
</head>

<body>
  <?php include_once './admin_sidebar.php'; ?>

  <div class="content">
    <h1 class="mb-4">Quản lý Đăng ký Hội thảo</h1>

    <?php if (isset($message)): ?>
      <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
      <div class="alert alert-danger">
        <?php echo $errorMessage; ?>
      </div>
    <?php endif; ?>

    <!-- Bộ lọc -->
    <div class="card mb-4">
      <div class="card-body">
        <form method="GET" action="" class="row">
          <?php
          foreach ($_GET as $key => $value) {
            if (!in_array($key, ['seminar_id', 'page'])) {
              echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
            }
          }
          ?>

          <div class="col-md-10 form-group">
            <label for="seminar_id">Lọc theo hội thảo</label>
            <select class="form-control" id="seminar_id" name="seminar_id">
              <option value="">-- Tất cả hội thảo --</option>
              <?php foreach ($seminars as $seminar): ?>
                <option value="<?php echo $seminar['seminar_id']; ?>" <?php echo (isset($_GET['seminar_id']) && $_GET['seminar_id'] == $seminar['seminar_id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($seminar['topic']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-2 form-group d-flex align-items-end">
            <button type="submit" class="btn btn-primary btn-block">
              Lọc
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Danh sách đăng ký -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="thead-dark">
          <tr>
            <th width="5%">ID</th>
            <th width="15%">Người dùng</th>
            <th width="30%">Hội thảo</th>
            <th width="15%">Thông tin</th>
            <th width="10%">Trạng thái</th>
            <th width="10%">Ngày đăng ký</th>
            <th width="15%">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (isset($registrations) && !empty($registrations)): ?>
            <?php foreach ($registrations as $registration): ?>
              <tr>
                <td><?php echo $registration['registration_id']; ?></td>
                <td>
                  <a href="#">
                    <strong><?php echo htmlspecialchars($registration['username']); ?></strong>
                  </a>
                  <div class="small text-muted"><?php echo htmlspecialchars($registration['full_name']); ?></div>
                </td>
                <td class="seminar-info">
                  <a href="./admin_seminar_view.php?id=<?php echo $registration['seminar_id']; ?>" title="Xem chi tiết hội thảo">
                    <?php echo htmlspecialchars($registration['topic']); ?>
                  </a>
                  <div class="small text-muted">
                    <?php
                    echo date('d/m/Y H:i', strtotime($registration['start_time'])) . ' - ' .
                      date('d/m/Y H:i', strtotime($registration['end_time']));
                    ?>
                  </div>
                </td>
                <td>
                  <?php if ($registration['max_participants'] > 0): ?>
                    <div class="small">
                      <i class="fas fa-users"></i>
                      <?php echo $registration['current_participants']; ?>/<?php echo $registration['max_participants']; ?>
                      người tham gia
                      <?php if ($registration['current_participants'] >= $registration['max_participants'] && $registration['status'] != 'confirmed'): ?>
                        <div class="text-danger">Đã đạt giới hạn!</div>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <div class="small text-muted">Không giới hạn số lượng</div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                  $statusClass = 'badge-' . $registration['status'];
                  $statusText = $registration['status'] == 'pending' ? 'Chờ xác nhận' : ($registration['status'] == 'confirmed' ? 'Đã xác nhận' : 'Đã hủy');
                  ?>
                  <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                </td>
                <td>
                  <?php echo date('d/m/Y H:i', strtotime($registration['created_at'])); ?>
                </td>
                <td>
                  <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle status-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-cog"></i> Thao tác
                    </button>
                    <div class="dropdown-menu">
                      <?php if ($registration['status'] == 'pending'): ?>
                        <?php if ($registration['max_participants'] == 0 || $registration['current_participants'] < $registration['max_participants']): ?>
                          <a class="dropdown-item" href="?action=confirm&id=<?php echo $registration['registration_id']; ?><?php echo isset($_GET['seminar_id']) ? '&seminar_id=' . $_GET['seminar_id'] : ''; ?>">
                            <i class="fas fa-check text-success"></i> Xác nhận
                          </a>
                        <?php else: ?>
                          <a class="dropdown-item disabled" href="#">
                            <i class="fas fa-check text-muted"></i> Xác nhận (đủ số lượng)
                          </a>
                        <?php endif; ?>
                      <?php endif; ?>

                      <?php if ($registration['status'] == 'confirmed'): ?>
                        <a class="dropdown-item" href="?action=cancel&id=<?php echo $registration['registration_id']; ?><?php echo isset($_GET['seminar_id']) ? '&seminar_id=' . $_GET['seminar_id'] : ''; ?>" onclick="return confirm('Bạn có chắc muốn hủy đăng ký này?')">
                          <i class="fas fa-times text-danger"></i> Hủy xác nhận
                        </a>
                      <?php endif; ?>

                      <?php if ($registration['status'] == 'cancelled'): ?>
                        <?php if ($registration['max_participants'] == 0 || $registration['current_participants'] < $registration['max_participants']): ?>
                          <a class="dropdown-item" href="?action=confirm&id=<?php echo $registration['registration_id']; ?><?php echo isset($_GET['seminar_id']) ? '&seminar_id=' . $_GET['seminar_id'] : ''; ?>">
                            <i class="fas fa-check text-success"></i> Xác nhận lại
                          </a>
                        <?php else: ?>
                          <a class="dropdown-item disabled" href="#">
                            <i class="fas fa-check text-muted"></i> Xác nhận lại (đủ số lượng)
                          </a>
                        <?php endif; ?>
                      <?php endif; ?>

                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item" href="?action=delete&id=<?php echo $registration['registration_id']; ?><?php echo isset($_GET['seminar_id']) ? '&seminar_id=' . $_GET['seminar_id'] : ''; ?>" onclick="return confirm('Bạn có chắc muốn xóa đăng ký này?')">
                        <i class="fas fa-trash text-danger"></i> Xóa đăng ký
                      </a>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center">Không tìm thấy đăng ký nào.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <?php if ($totalPages > 1): ?>
      <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
          <?php
          $queryParams = $_GET;

          $queryParams['page'] = $currentPage - 1;
          $prevPageUrl = '?' . http_build_query($queryParams);

          $queryParams['page'] = $currentPage + 1;
          $nextPageUrl = '?' . http_build_query($queryParams);
          ?>

          <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo $currentPage <= 1 ? '#' : $prevPageUrl; ?>" aria-label="Previous">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>

          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php
            $queryParams['page'] = $i;
            $pageUrl = '?' . http_build_query($queryParams);
            ?>
            <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
              <a class="page-link" href="<?php echo $pageUrl; ?>"><?php echo $i; ?></a>
            </li>
          <?php endfor; ?>

          <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo $currentPage >= $totalPages ? '#' : $nextPageUrl; ?>" aria-label="Next">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>

    <!-- Tóm tắt số liệu -->
    <div class="card mt-4">
      <div class="card-body">
        <h5 class="card-title">Thông tin tổng quan:</h5>
        <div class="row">
          <?php
          $statsStmt = $conn->query("
              SELECT status, COUNT(*) as count
              FROM registrations
              " . ($seminarFilter ? "WHERE seminar_id = $seminarFilter" : "") . "
              GROUP BY status
            ");
          $stats = $statsStmt->fetchAll(PDO::FETCH_ASSOC);

          $pendingCount = $confirmedCount = $cancelledCount = 0;

          foreach ($stats as $stat) {
            if ($stat['status'] == 'confirmed') $confirmedCount = $stat['count'];
            if ($stat['status'] == 'cancelled') $cancelledCount = $stat['count'];
          }

          $totalCount =  $confirmedCount + $cancelledCount;
          ?>

          <div class="col-md-4">
            <div class="card bg-light">
              <div class="card-body">
                <h6 class="card-title">Tổng số đăng ký</h6>
                <h3 class="text-primary"><?php echo $totalCount; ?></h3>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card bg-light">
              <div class="card-body">
                <h6 class="card-title">Đã xác nhận</h6>
                <h3 class="text-success"><?php echo $confirmedCount; ?></h3>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card bg-light">
              <div class="card-body">
                <h6 class="card-title">Đã hủy</h6>
                <h3 class="text-danger"><?php echo $cancelledCount; ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Bootstrap JS và jQuery -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>