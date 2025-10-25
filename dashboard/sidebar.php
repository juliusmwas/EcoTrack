<!-- sidebar.php -->
<div class="menu-toggle" id="menuToggle">
  <i class="ri-menu-line"></i>
</div>

<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h2>EcoTrack</h2>
    <span id="closeSidebar" class="close-btn">&times;</span>
  </div>

  <?php
  $role = $_SESSION['user']['role'] ?? '';
  $current_page = basename($_SERVER['PHP_SELF']); // Detect current page

  if ($role == 'admin') {
    echo '
      <a href="admin.php" class="' . ($current_page == 'admin.php' ? 'active' : '') . '">Dashboard</a>
      <a href="manage_users.php" class="' . ($current_page == 'manage_users.php' ? 'active' : '') . '">Manage Users</a>
      <a href="view_reports.php" class="' . ($current_page == 'view_reports.php' ? 'active' : '') . '">View Reports</a>
      <a href="manage_bins.php" class="' . ($current_page == 'manage_bins.php' ? 'active' : '') . '">Manage Bins</a>
      <a href="activity_logs.php" class="' . ($current_page == 'activity_logs.php' ? 'active' : '') . '">Activity Logs</a>
      ';
  } elseif ($role == 'collector') {
    echo '
      <a href="collector.php" class="' . ($current_page == 'collector.php' ? 'active' : '') . '">Dashboard</a>
      <a href="assigned_reports.php" class="' . ($current_page == 'assigned_reports.php' ? 'active' : '') . '">My Assigned Reports</a>
      <a href="map_view.php" class="' . ($current_page == 'map_view.php' ? 'active' : '') . '">Map View</a>
      <a href="bins.php" class="' . ($current_page == 'bins.php' ? 'active' : '') . '">View Bins</a>
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

<div class="overlay" id="overlay"></div>

<!-- Sidebar Script -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menuToggle');
    const overlay = document.getElementById('overlay');
    const closeSidebar = document.getElementById('closeSidebar');

    if (menuToggle && sidebar && overlay && closeSidebar) {
      menuToggle.addEventListener('click', () => {
        sidebar.classList.add('show');
        overlay.classList.add('show');
      });

      closeSidebar.addEventListener('click', () => {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
      });

      overlay.addEventListener('click', () => {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
      });
    }
  });
</script>