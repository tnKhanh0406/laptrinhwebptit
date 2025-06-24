<!-- Sidebar -->
<div class="sidebar">
  <div class="nav-title">Admin Menu</div>
  <a href="../index.php"><i class="fa-solid fa-house"></i> Trang chủ</a>
  <a href="../layouts/admin.php"><i class="fa-solid fa-user-tie"></i> Trang chủ quản lý</a>
  <a href="./admin_users.php"><i class="fas fa-users me-2"></i> Quản lý người dùng</a>
  <a href="./admin_seminars.php"><i class="fas fa-chalkboard-teacher me-2"></i> Quản lý hội thảo</a>
  <a href="./admin_speakers.php"><i class="fas fa-microphone me-2"></i> Quản lý diễn giả</a>
  <a href="./admin_locations.php"><i class="fas fa-map-marker-alt me-2"></i> Quản lý địa điểm</a>
  <a href="./admin_messages.php"><i class="fas fa-envelope me-2"></i> Quản lý tin nhắn</a>
  <a href="./admin_registrations.php"><i class="fa-solid fa-registered"></i> Quản lý đăng ký</a>
  <a class="nav-link" href="./pending_seminars.php">
    <i class="fas fa-clipboard-check"></i>
    <span>Hội thảo chờ duyệt</span>
    <?php
    // Đếm số lượng hội thảo chờ duyệt
    $countStmt = $conn->query("SELECT COUNT(*) FROM seminars WHERE status = 0");
    $pendingCount = $countStmt->fetchColumn();
    if ($pendingCount > 0):
    ?>
      <span class="badge badge-warning"><?php echo $pendingCount; ?></span>
    <?php endif; ?>
  </a>
</div>