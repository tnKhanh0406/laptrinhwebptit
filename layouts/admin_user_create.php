<?php
session_start();

require_once '../config.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $fullName = trim($_POST['full_name'] ?? '');
  $role = $_POST['role'] ?? 'user';
  
  $errors = [];
  
  if (empty($username)) {
    $errors[] = "Username không được để trống";
  }
  
  if (empty($password)) {
    $errors[] = "Mật khẩu không được để trống";
  } elseif (strlen($password) < 6) {
    $errors[] = "Mật khẩu phải có ít nhất 6 ký tự";
  }
  
  try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
      $errors[] = "Username đã tồn tại, vui lòng chọn username khác";
    }
  } catch (PDOException $e) {
    $errors[] = "Lỗi khi kiểm tra dữ liệu: " . $e->getMessage();
  }
  
  if (empty($errors)) {
    try {
      $stmt = $conn->prepare("
        INSERT INTO users (username, password, full_name, role, created_at) 
        VALUES (:username, :password, :full_name, :role, NOW())
      ");
      
      $stmt->bindParam(':username', $username);
      $stmt->bindParam(':password', $password);
      $stmt->bindParam(':full_name', $fullName);
      $stmt->bindParam(':role', $role);
      
      $stmt->execute();
      
      $message = "Người dùng đã được tạo thành công!";
      $messageType = "success";
      
      // Reset form
      $username = $fullName = '';
      $role = 'user';
      
    } catch (PDOException $e) {
      $message = "Lỗi khi tạo người dùng: " . $e->getMessage();
      $messageType = "danger";
    }
  } else {
    $message = implode("<br>", $errors);
    $messageType = "danger";
  }
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
  <title>Thêm Người Dùng - Admin</title>
</head>

<body>
  <?php include_once './admin_sidebar.php'; ?>

  <div class="content">
    <h1 class="mb-4">Thêm Người Dùng Mới</h1>
    
    <?php if (!empty($message)) : ?>
      <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    <?php endif; ?>
    
    <div class="card">
      <div class="card-body">
        <form method="POST" action="">
          <div class="form-group">
            <label for="username">
              <i class="fas fa-user"></i> Username <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="username" name="username" required
                  value="">
            <small class="form-text text-muted">Username phải là duy nhất và không chứa ký tự đặc biệt.</small>
          </div>
          
          <div class="form-group">
            <label for="password">
              <i class="fas fa-lock"></i> Mật khẩu <span class="text-danger">*</span>
            </label>
            <input type="password" class="form-control" id="password" name="password" required>
            <small class="form-text text-muted">Mật khẩu phải có ít nhất 6 ký tự.</small>
          </div>
          
          <div class="form-group">
            <label for="full_name">
              <i class="fas fa-address-card"></i> Họ và tên
            </label>
            <input type="text" class="form-control" id="full_name" name="full_name"
                  value="<?php echo htmlspecialchars($fullName ?? ''); ?>">
          </div>
          
          <div class="form-group">
            <label for="role">
              <i class="fas fa-user-tag"></i> Vai trò <span class="text-danger">*</span>
            </label>
            <select class="form-control" id="role" name="role" required>
              <option value="user" <?php echo (isset($role) && $role === 'user') ? 'selected' : ''; ?>>
                Người dùng
              </option>
              <option value="admin" <?php echo (isset($role) && $role === 'admin') ? 'selected' : ''; ?>>
                Quản trị viên
              </option>
            </select>
          </div>
          
          <div class="form-group">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Lưu
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS và jQuery -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>