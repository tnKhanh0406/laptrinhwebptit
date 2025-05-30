<?php
session_start();

require_once '../config.php';

// xóa người dùng
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
  try {
    $userId = (int)$_GET['id'];

    if ($_SESSION['user_id'] == $userId) {
      $deleteMessage = "Không thể xóa tài khoản của chính bạn!";
      $deleteStatus = "danger";
    } else {
      $stmt = $conn->prepare("DELETE FROM users WHERE user_id = :user_id");
      $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
      $stmt->execute();

      if ($stmt->rowCount() > 0) {
        $deleteMessage = "Đã xóa người dùng thành công!";
        $deleteStatus = "success";
      } else {
        $deleteMessage = "Không tìm thấy người dùng để xóa!";
        $deleteStatus = "warning";
      }
    }
  } catch (PDOException $e) {
    $deleteMessage = "Lỗi khi xóa người dùng: " . $e->getMessage();
  }
}

try {
  // Phân trang
  $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $limit = 10; 
  $offset = ($currentPage - 1) * $limit;

  $countStmt = $conn->query("SELECT COUNT(*) FROM users");
  $totalUsers = $countStmt->fetchColumn();
  $totalPages = ceil($totalUsers / $limit);

  $search = isset($_GET['search']) ? trim($_GET['search']) : '';
  $params = [];
  $whereClause = "";

  if (!empty($search)) {
    $whereClause = "WHERE username LIKE :search OR full_name LIKE :search";
    $searchTerm = "%{$search}%";
    $params[':search'] = $searchTerm;
  }

  $countSql = "SELECT COUNT(*) FROM users " . $whereClause;

  if (!empty($search)) {
    $countStmt = $conn->prepare($countSql);
    $countStmt->bindParam(':search', $searchTerm);
    $countStmt->execute();
  } else {
    $countStmt = $conn->query($countSql);
  }

  $totalUsers = $countStmt->fetchColumn();
  $totalPages = ceil($totalUsers / $limit);

  $sql = "SELECT user_id, username, full_name, role, created_at FROM users " . $whereClause . " ORDER BY user_id LIMIT :limit OFFSET :offset";

  $stmt = $conn->prepare($sql);

  if (!empty($search)) {
    $stmt->bindParam(':search', $searchTerm);
  }

  $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $errorMessage = "Lỗi khi lấy danh sách người dùng: " . $e->getMessage();
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
  <title>Quản lý Người Dùng</title>
</head>

<body>
  <?php include_once './admin_sidebar.php'; ?>

  <div class="content">
    <h1 class="mb-4">Quản lý Người Dùng</h1>

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
      <a href="./admin_user_create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm Người Dùng
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

        <input class="form-control mr-sm-2" type="search" name="search" placeholder="Tìm người dùng..."
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
            <th>ID</th>
            <th>Username</th>
            <th>Họ Tên</th>
            <th>Vai trò</th>
            <th>Ngày tạo</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (isset($users) && !empty($users)): ?>
            <?php foreach ($users as $user): ?>
              <tr>
                <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['full_name'] ?? 'Chưa cập nhật'); ?></td>
                <td>
                  <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'danger' : 'success'; ?>">
                    <?php echo htmlspecialchars($user['role']); ?>
                  </span>
                </td>
                <td>
                  <?php
                  $date = new DateTime($user['created_at']);
                  echo $date->format('d/m/Y H:i');
                  ?>
                </td>
                <td>
                  <a href="./admin_user_edit.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="?action=delete&id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger"
                    onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center">Không tìm thấy người dùng nào.</td>
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

  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>