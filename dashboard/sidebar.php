<div class="sidebar">
    <h2>EcoTrack</h2>
    <?php
    $role = $_SESSION['user']['role'] ?? '';
    if ($role == 'admin') {
        echo '
      <a href="admin.php" class="active">Dashboard</a>
      <a href="#">Manage Users</a>
      <a href="#">View Reports</a>
      <a href="#">Activity Logs</a>
      ';
    } elseif ($role == 'collector') {
        echo '
      <a href="collector.php" class="active">Dashboard</a>
      <a href="#">My Assigned Reports</a>
      <a href="#">Map View</a>
      ';
    } else {
        echo '
      <a href="resident.php" class="active">Dashboard</a>
      <a href="#">Submit Report</a>
      <a href="#">My Reports</a>
      ';
    }
    ?>
</div>