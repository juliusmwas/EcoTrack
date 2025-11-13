<div class="navbar">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user']['fullname']); ?></h1>
    <form method="POST" action="../../logout.php" style="margin:0;">
        <button type="submit">Logout</button>
    </form>
</div>