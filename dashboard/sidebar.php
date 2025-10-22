<div class="sidebar">
  <h2>EcoTrack</h2>

  <?php
  $role = $_SESSION['user']['role'] ?? '';
  $current_page = basename($_SERVER['PHP_SELF']); // Detect current page

  if ($role == 'admin') {
    echo '
      <a href="admin.php" class="' . ($current_page == 'admin.php' ? 'active' : '') . '">Dashboard</a>
      <a href="manage_users.php" class="' . ($current_page == 'manage_users.php' ? 'active' : '') . '">Manage Users</a>
      <a href="view_reports.php" class="' . ($current_page == 'view_reports.php' ? 'active' : '') . '">View Reports</a>
      <a href="activity_logs.php" class="' . ($current_page == 'activity_logs.php' ? 'active' : '') . '">Activity Logs</a>
      ';
  } elseif ($role == 'collector') {
    echo '
      <a href="collector.php" class="' . ($current_page == 'collector.php' ? 'active' : '') . '">Dashboard</a>
      <a href="assigned_reports.php" class="' . ($current_page == 'assigned_reports.php' ? 'active' : '') . '">My Assigned Reports</a>
      <a href="map_view.php" class="' . ($current_page == 'map_view.php' ? 'active' : '') . '">Map View</a>
      ';
  } else {
    echo '
      <a href="resident.php" class="' . ($current_page == 'resident.php' ? 'active' : '') . '">Dashboard</a>
      <a href="report.php" class="' . ($current_page == 'report.php' ? 'active' : '') . '">Submit Report</a>
      <a href="my_reports.php" class="' . ($current_page == 'my_reports.php' ? 'active' : '') . '">My Reports</a>
      ';
  }
  ?>
</div>