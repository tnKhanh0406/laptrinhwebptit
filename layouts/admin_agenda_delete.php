<?php
session_start();
require_once '../config.php';

$agendaId = (int)$_GET['id'];
$seminarId = (int)$_GET['seminar_id'];

if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
  $stmt = $conn->prepare("DELETE FROM agenda WHERE agenda_id = :agenda_id AND seminar_id = :seminar_id");
  $stmt->bindParam(':agenda_id', $agendaId, PDO::PARAM_INT);
  $stmt->bindParam(':seminar_id', $seminarId, PDO::PARAM_INT);
  $stmt->execute();

  $_SESSION['message'] = "Đã xóa chương trình thành công!";
  $_SESSION['message_type'] = "success";

  header('Location: ./admin_seminar_view.php?id=' . $seminarId);
  exit;
} else {
  $stmt = $conn->prepare("
      SELECT a.*, s.topic as seminar_topic, spk.full_name as speaker_name
      FROM agenda a 
      LEFT JOIN seminars s ON a.seminar_id = s.seminar_id
      LEFT JOIN speakers spk ON a.speaker_id = spk.speaker_id
      WHERE a.agenda_id = :agenda_id AND a.seminar_id = :seminar_id
    ");
  $stmt->bindParam(':agenda_id', $agendaId, PDO::PARAM_INT);
  $stmt->bindParam(':seminar_id', $seminarId, PDO::PARAM_INT);
  $stmt->execute();

  $agenda = $stmt->fetch(PDO::FETCH_ASSOC);
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
  <title>Xóa Chương Trình</title>
</head>

<body>
  <?php
  if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user') {
    include_once './header.php';
  } else {
    include_once './admin_sidebar.php';
  } ?>

  <div class="<?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 'user') ? 'container mt-5 mb-5' : 'content'; ?>">
    <h1 class="mb-4 text-danger">Xóa Chương Trình</h1>

    <div class="card border-danger mb-4">
      <div class="card-header bg-danger text-white">
        <i class="fas fa-exclamation-triangle"></i> Xác nhận xóa
      </div>
      <div class="card-body">
        <h5 class="card-title">Bạn có chắc chắn muốn xóa chương trình này?</h5>
        <p class="card-text">Thao tác này không thể hoàn tác và sẽ xóa vĩnh viễn chương trình từ hội thảo.</p>

        <div class="alert alert-info">
          <h6>Thông tin chương trình:</h6>
          <ul class="mb-0">
            <li><strong>Tiêu đề:</strong> <?php echo htmlspecialchars($agenda['title']); ?></li>
            <li><strong>Hội thảo:</strong> <?php echo htmlspecialchars($agenda['seminar_topic']); ?></li>
            <li><strong>Diễn giả:</strong> <?php echo htmlspecialchars($agenda['speaker_name'] ?? 'Không có'); ?></li>
            <li><strong>Thời gian:</strong> <?php echo date('d/m/Y H:i', strtotime($agenda['start_time'])); ?> - <?php echo date('d/m/Y H:i', strtotime($agenda['end_time'])); ?></li>
          </ul>
        </div>

        <div class="mt-4">
          <a href="?id=<?php echo $agendaId; ?>&seminar_id=<?php echo $seminarId; ?>&confirm=yes" class="btn btn-danger">
            <i class="fas fa-trash"></i> Xác nhận xóa
          </a>
          <a href="./admin_seminar_view.php?id=<?php echo $seminarId; ?>" class="btn btn-secondary ml-2">
            <i class="fas fa-arrow-left"></i> Hủy và quay lại
          </a>
        </div>
      </div>
    </div>
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