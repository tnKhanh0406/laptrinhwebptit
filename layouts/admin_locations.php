<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../index.php');
  exit;
}

require_once '../config.php';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
  try {
    $locationId = (int)$_GET['id'];
    
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM seminars WHERE location_id = :location_id");
    $checkStmt->bindParam(':location_id', $locationId, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if ($checkStmt->fetchColumn() > 0) {
      $deleteMessage = "Không thể xóa địa điểm này vì đang được sử dụng trong các hội thảo!";
      $deleteStatus = "danger";
    } else {
      $photoStmt = $conn->prepare("SELECT photo FROM locations WHERE location_id = :location_id");
      $photoStmt->bindParam(':location_id', $locationId, PDO::PARAM_INT);
      $photoStmt->execute();
      $photo = $photoStmt->fetchColumn();
      
      $stmt = $conn->prepare("DELETE FROM locations WHERE location_id = :location_id");
      $stmt->bindParam(':location_id', $locationId, PDO::PARAM_INT);
      $stmt->execute();
      
      if ($stmt->rowCount() > 0) {
        if (!empty($photo) && file_exists("../assets/images/" . $photo)) {
          unlink("../assets/images/" . $photo);
        }
        
        $deleteMessage = "Đã xóa địa điểm thành công!";
        $deleteStatus = "success";
      } else {
        $deleteMessage = "Không tìm thấy địa điểm để xóa!";
        $deleteStatus = "warning";
      }
    }
  } catch (PDOException $e) {
    $deleteMessage = "Lỗi khi xóa địa điểm: " . $e->getMessage();
    $deleteStatus = "danger";
  }
}

try {
  // Phân trang
  $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $limit = 10; 
  $offset = ($currentPage - 1) * $limit;
  
  $countStmt = $conn->query("SELECT COUNT(*) FROM locations");
  $totalLocations = $countStmt->fetchColumn();
  $totalPages = ceil($totalLocations / $limit);
  
  $search = isset($_GET['search']) ? trim($_GET['search']) : '';
  $whereClause = "";
  
  if (!empty($search)) {
    $whereClause = "WHERE name LIKE :search OR address LIKE :search";
    $searchTerm = "%{$search}%";
  }
  
  $countSql = "SELECT COUNT(*) FROM locations " . $whereClause;
  
  if (!empty($search)) {
    $countStmt = $conn->prepare($countSql);
    $countStmt->bindParam(':search', $searchTerm);
    $countStmt->execute();
  } else {
    $countStmt = $conn->query($countSql);
  }
  
  $totalLocations = $countStmt->fetchColumn();
  $totalPages = ceil($totalLocations / $limit);
  
  $sql = "SELECT location_id, name, address FROM locations " . $whereClause . " ORDER BY location_id DESC LIMIT :limit OFFSET :offset";
  
  $stmt = $conn->prepare($sql);
  
  if (!empty($search)) {
    $stmt->bindParam(':search', $searchTerm);
  }
  
  $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();
  $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $errorMessage = "Lỗi khi lấy danh sách địa điểm: " . $e->getMessage();
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
  <title>Quản lý Địa điểm</title>
</head>

<body>
  <?php include_once './admin_sidebar.php'; ?>

  <div class="content">
    <h1 class="mb-4">Quản lý Địa điểm</h1>
    
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
      <a href="./admin_location_create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm Địa điểm
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
        
        <input class="form-control mr-sm-2" type="search" name="search" placeholder="Tìm địa điểm..."
          aria-label="Search" value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-outline-success my-2 my-sm-0" type="submit">
          <i class="fas fa-search"></i> Tìm kiếm
        </button>
        
        <?php if (!empty($search)): ?>
          <a href="?<?php echo http_build_query(array_diff_key($_GET, ['search' => ''])); ?>" class="btn btn-outline-secondary ml-2">
            <i class="fas fa-times"></i> Xóa bộ lọc
          </a>
        <?php endif; ?>
      </form>
    </div>
    
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="thead-dark">
          <tr>
            <th width="5%">ID</th>
            <th width="25%">Tên địa điểm</th>
            <th width="50%">Địa chỉ</th>
            <th width="20%">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (isset($locations) && !empty($locations)): ?>
            <?php foreach ($locations as $location): ?>
              <tr>
                <td><?php echo htmlspecialchars($location['location_id']); ?></td>
                <td><?php echo htmlspecialchars($location['name']); ?></td>
                <td>
                  <?php
                    echo htmlspecialchars(
                      !empty($location['address']) ? 
                      (strlen($location['address']) > 150 ? substr($location['address'], 0, 147) . '...' : $location['address']) : 
                      'Chưa cập nhật'
                    ); 
                  ?>
                </td>
                <td>
                  <a href="./admin_location_view.php?id=<?php echo $location['location_id']; ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="./admin_location_edit.php?id=<?php echo $location['location_id']; ?>" class="btn btn-sm btn-warning" title="Chỉnh sửa">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="?action=delete&id=<?php echo $location['location_id']; ?>" class="btn btn-sm btn-danger" title="Xóa"
                    onclick="return confirm('Bạn có chắc chắn muốn xóa địa điểm này?')">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center">Không tìm thấy địa điểm nào.</td>
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