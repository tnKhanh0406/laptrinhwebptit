<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../index.php');
  exit;
}

require_once '../config.php';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
  $speakerId = (int)$_GET['id'];

  $checkStmt = $conn->prepare("SELECT COUNT(*) FROM agenda WHERE speaker_id = :speaker_id");
  $checkStmt->bindParam(':speaker_id', $speakerId, PDO::PARAM_INT);
  $checkStmt->execute();

  if ($checkStmt->fetchColumn() > 0) {
    $deleteMessage = "Không thể xóa speaker này vì đang được sử dụng trong các hội thảo!";
    $deleteStatus = "danger";
  } else {
    $photoStmt = $conn->prepare("SELECT photo FROM speakers WHERE speaker_id = :speaker_id");
    $photoStmt->bindParam(':speaker_id', $speakerId, PDO::PARAM_INT);
    $photoStmt->execute();
    $photo = $photoStmt->fetchColumn();

    $stmt = $conn->prepare("DELETE FROM speakers WHERE speaker_id = :speaker_id");
    $stmt->bindParam(':speaker_id', $speakerId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
      if (!empty($photo) && file_exists("../assets/images/" . $photo)) {
        unlink("../assets/images/" . $photo);
      }

      $deleteMessage = "Đã xóa speaker thành công!";
      $deleteStatus = "success";
    } else {
      $deleteMessage = "Không tìm thấy speaker để xóa!";
      $deleteStatus = "warning";
    }
  }
}

// Phân trang
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; 
$offset = ($currentPage - 1) * $limit;

$countStmt = $conn->query("SELECT COUNT(*) FROM speakers");
$totalSpeakers = $countStmt->fetchColumn();
$totalPages = ceil($totalSpeakers / $limit);

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = "";

if (!empty($search)) {
  $whereClause = "WHERE full_name LIKE :search OR bio LIKE :search";
  $searchTerm = "%{$search}%";
}

$countSql = "SELECT COUNT(*) FROM speakers " . $whereClause;

if (!empty($search)) {
  $countStmt = $conn->prepare($countSql);
  $countStmt->bindParam(':search', $searchTerm);
  $countStmt->execute();
} else {
  $countStmt = $conn->query($countSql);
}

$totalSpeakers = $countStmt->fetchColumn();
$totalPages = ceil($totalSpeakers / $limit);

$sql = "SELECT speaker_id, full_name, bio, description FROM speakers " . $whereClause . " ORDER BY speaker_id LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($sql);

if (!empty($search)) {
  $stmt->bindParam(':search', $searchTerm);
}

$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$speakers = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
  <title>Quản lý Diễn giả</title>
</head>

<body>
  <?php include_once './admin_sidebar.php'; ?>

  <div class="content">
    <h1 class="mb-4">Quản lý Diễn giả</h1>

    <?php if (isset($deleteMessage)): ?>
      <div class="alert alert-<?php echo $deleteStatus; ?> alert-dismissible fade show" role="alert">
        <?php echo $deleteMessage; ?>
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

    <div class="d-flex justify-content-between align-items-center mb-3">
      <a href="./admin_speaker_create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm Diễn giả
      </a>

      <form class="form-inline">
        <?php if (isset($_GET['page'])): ?>
          <input type="hidden" name="page" value="<?php echo (int)$_GET['page']; ?>">
        <?php endif; ?>

        <?php
        foreach ($_GET as $key => $value) {
          if ($key !== 'search' && $key !== 'page') {
            echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
          }
        }
        ?>

        <input class="form-control mr-sm-2" type="search" name="search" placeholder="Tìm diễn giả..."
          aria-label="Search" value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-outline-success my-2 my-sm-0" type="submit">
          <i class="fas fa-search"></i> Tìm kiếm
        </button>
      </form>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="thead-dark">
          <tr>
            <th width="5%">ID</th>
            <th width="20%">Họ tên</th>
            <th width="35%">Tiểu sử</th>
            <th width="25%">Mô tả</th>
            <th width="15%">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (isset($speakers) && !empty($speakers)): ?>
            <?php foreach ($speakers as $speaker): ?>
              <tr>
                <td><?php echo htmlspecialchars($speaker['speaker_id']); ?></td>
                <td><?php echo htmlspecialchars($speaker['full_name']); ?></td>
                <td>
                  <?php
                  echo htmlspecialchars(
                    !empty($speaker['bio']) ?
                      (strlen($speaker['bio']) > 150 ? substr($speaker['bio'], 0, 147) . '...' : $speaker['bio']) :
                      'Chưa cập nhật'
                  );
                  ?>
                </td>
                <td>
                  <?php
                  echo htmlspecialchars(
                    !empty($speaker['description']) ?
                      (strlen($speaker['description']) > 150 ? substr($speaker['description'], 0, 147) . '...' : $speaker['description']) :
                      'Chưa cập nhật'
                  );
                  ?>
                </td>
                <td>
                  <a href="./admin_speaker_edit.php?id=<?php echo $speaker['speaker_id']; ?>" class="btn btn-sm btn-warning" title="Chỉnh sửa">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="?action=delete&id=<?php echo $speaker['speaker_id']; ?>" class="btn btn-sm btn-danger" title="Xóa"
                    onclick="return confirm('Bạn có chắc chắn muốn xóa diễn giả này?')">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center">Không tìm thấy diễn giả nào.</td>
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

  </div>

  <!-- Bootstrap JS và jQuery -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>