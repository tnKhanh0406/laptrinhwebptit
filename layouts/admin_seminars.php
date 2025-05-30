<?php
session_start();

require_once '../config.php';

$message = '';
$messageType = '';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
  try {
    $seminarId = (int)$_GET['id'];

    $stmt = $conn->prepare("DELETE FROM agenda WHERE seminar_id = :seminar_id");
    $stmt->bindParam(':seminar_id', $seminarId, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM registrations WHERE seminar_id = :seminar_id");
    $stmt->bindParam(':seminar_id', $seminarId, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM seminars WHERE seminar_id = :seminar_id");
    $stmt->bindParam(':seminar_id', $seminarId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
      $message = "Đã xóa hội thảo và dữ liệu liên quan thành công!";
      $messageType = "success";
    } else {
      $message = "Không tìm thấy hội thảo để xóa!";
      $messageType = "warning";
    }
  } catch (PDOException $e) {
    $message = "Lỗi khi xóa hội thảo: " . $e->getMessage();
    $messageType = "danger";
  }
}

$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($currentPage - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$whereClause = [];
$params = [];

$whereClause[] = "s.status = 1";

if (!empty($search)) {
  $whereClause[] = "(s.topic LIKE :search)";
  $params[':search'] = "%{$search}%";
}

if (!empty($category)) {
  $whereClause[] = "(s.category = :category)";
  $params[':category'] = $category;
}

$sqlWhere = !empty($whereClause) ? " WHERE " . implode(" AND ", $whereClause) : "";

$countSql = "SELECT COUNT(*) FROM seminars s WHERE s.status = 1" . 
            (!empty($whereClause) ? " AND " . implode(" AND ", $whereClause) : "");

if (!empty($params)) {
  $countStmt = $conn->prepare($countSql);
  $countStmt->execute($params);
} else {
  $countStmt = $conn->query($countSql);
}
$totalSeminars = $countStmt->fetchColumn();
$totalPages = ceil($totalSeminars / $limit);

$sql = "SELECT s.seminar_id, s.topic, s.start_time, s.end_time, 
          s.max_participants, s.category, l.name as location_name 
          FROM seminars s 
          LEFT JOIN locations l ON s.location_id = l.location_id
          WHERE s.status = 1" .
        (!empty($whereClause) ? " AND " . implode(" AND ", $whereClause) : "") .
        " ORDER BY s.start_time DESC LIMIT :limit OFFSET :offset";
        
$stmt = $conn->prepare($sql);

foreach ($params as $key => $value) {
  $stmt->bindValue($key, $value);
}

$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$seminars = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($seminars as &$seminar) {
  $agendaSql = "SELECT a.agenda_id, a.title, a.start_time, a.end_time, 
                 s.full_name as speaker_name 
                 FROM agenda a 
                 LEFT JOIN speakers s ON a.speaker_id = s.speaker_id 
                 WHERE a.seminar_id = :seminar_id 
                 ORDER BY a.start_time ASC";

  $agendaStmt = $conn->prepare($agendaSql);
  $agendaStmt->bindParam(':seminar_id', $seminar['seminar_id'], PDO::PARAM_INT);
  $agendaStmt->execute();
  $seminar['agendas'] = $agendaStmt->fetchAll(PDO::FETCH_ASSOC);
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
  <title>Quản Lý Hội Thảo</title>
  <style>
    .btn-agenda {
      background-color: #17a2b8;
      color: white;
    }

    .btn-agenda:hover {
      background-color: #138496;
      color: white;
    }

    .modal-dialog {
      max-width: 700px;
    }
  </style>
</head>

<body>
  <?php include_once './admin_sidebar.php'; ?>

  <div class="content">
    <h1 class="mb-4">Quản Lý Hội Thảo</h1>

    <?php if (!empty($message)): ?>
      <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between mb-3">
      <a href="./admin_seminar_create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm Hội Thảo
      </a>

      <form class="form-inline">
        <input class="form-control mr-sm-2" type="search" name="search" placeholder="Tìm kiếm..." aria-label="Search"
          value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-outline-success my-2 my-sm-0" type="submit">
          <i class="fas fa-search"></i> Tìm kiếm
        </button>
      </form>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="thead-dark">
          <tr>
            <th>ID</th>
            <th>Chủ đề</th>
            <th>Thời gian</th>
            <th>Địa điểm</th>
            <th>Người tham gia tối đa</th>
            <th>Danh mục</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (isset($seminars) && !empty($seminars)): ?>
            <?php foreach ($seminars as $seminar): ?>
              <tr>
                <td><?php echo htmlspecialchars($seminar['seminar_id']); ?></td>
                <td><?php echo htmlspecialchars($seminar['topic']); ?></td>
                <td>
                  <?php
                  $start = new DateTime($seminar['start_time']);
                  $end = new DateTime($seminar['end_time']);
                  echo $start->format('d/m/Y, H:i') . ' - ' . $end->format('H:i');
                  ?>
                </td>
                <td><?php echo htmlspecialchars($seminar['location_name'] ?? 'Chưa thiết lập'); ?></td>
                <td><?php echo htmlspecialchars($seminar['max_participants'] ?? 'Không giới hạn'); ?></td>
                <td><?php echo htmlspecialchars($seminar['category']); ?></td>
                <td>
                  <a href="./admin_seminar_edit.php?id=<?php echo $seminar['seminar_id']; ?>" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="?action=delete&id=<?php echo $seminar['seminar_id']; ?>" class="btn btn-sm btn-danger"
                    onclick="return confirm('Bạn có chắc chắn muốn xóa hội thảo này? Tất cả agenda và đăng ký liên quan sẽ bị xóa.')">
                    <i class="fas fa-trash"></i>
                  </a>
                  <a href="./admin_seminar_view.php?id=<?php echo $seminar['seminar_id']; ?>" class="btn btn-sm btn-success">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                  <button type="button" class="btn btn-sm btn-agenda" data-toggle="modal" data-target="#agendaModal<?php echo $seminar['seminar_id']; ?>">
                    <i class="fas fa-calendar-alt"></i> Chương Trình
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center">Không tìm thấy hội thảo nào.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <?php if ($totalPages > 1): ?>
      <nav aria-label="Page navigation">
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

    <!-- Agenda Modals -->
    <?php if (isset($seminars) && !empty($seminars)): ?>
      <?php foreach ($seminars as $seminar): ?>
        <div class="modal fade" id="agendaModal<?php echo $seminar['seminar_id']; ?>" tabindex="-1" aria-labelledby="agendaModalLabel<?php echo $seminar['seminar_id']; ?>" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="agendaModalLabel<?php echo $seminar['seminar_id']; ?>">
                  Chương trình cho hội thảo: <?php echo htmlspecialchars($seminar['topic']); ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <a href="./admin_agenda_create.php?seminar_id=<?php echo $seminar['seminar_id']; ?>" class="btn btn-primary mb-3">
                  <i class="fas fa-plus"></i> Thêm Chương Trình
                </a>
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Tiêu đề</th>
                      <th>Diễn giả</th>
                      <th>Thời gian</th>
                      <th>Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (isset($seminar['agendas']) && !empty($seminar['agendas'])): ?>
                      <?php foreach ($seminar['agendas'] as $agenda): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($agenda['title']); ?></td>
                          <td><?php echo htmlspecialchars($agenda['speaker_name'] ?? 'Chưa thiết lập'); ?></td>
                          <td>
                            <?php
                            $start = new DateTime($agenda['start_time']);
                            $end = new DateTime($agenda['end_time']);
                            echo $start->format('H:i') . ' - ' . $end->format('H:i');
                            ?>
                          </td>
                          <td>
                            <a href="./admin_agenda_edit.php?id=<?php echo $agenda['agenda_id']; ?>&seminar_id=<?php echo $seminar['seminar_id']; ?>" class="btn btn-sm btn-warning">
                              <i class="fas fa-edit"></i>
                            </a>
                            <a href="./admin_agenda_delete.php?id=<?php echo $agenda['agenda_id']; ?>&seminar_id=<?php echo $seminar['seminar_id']; ?>" class="btn btn-sm btn-danger"
                              onclick="return confirm('Bạn có chắc chắn muốn xóa chương trình này?')">
                              <i class="fas fa-trash"></i>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="4" class="text-center">Không có chương trình nào.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Bootstrap JS và jQuery -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>