<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="">Production App</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="index.php">Home</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Dashboard
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
            <li>
              <a class="dropdown-item" href="dashboard.php">Work Order Dashboard</a>
            </li>
            <li>
              <a class="dropdown-item" href="wo_dash.php">Work Order Ready</a>
            </li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="submit_data.php">Submit Data</a>

        <li class="nav-item">
          <a class="nav-link" href="material_usage.php">Fabric Usage Entry</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="Roll_usage_dashboard.php">Fabric Usage Dashboard</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Work Orders
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
            <li>
              <a class="dropdown-item" href="upload_wo.php">Upload Work Order</a>
            </li>
            <li>
              <a class="dropdown-item" href="add_work_order.php">Add Work Order</a>
            </li>
            <li>
              <a class="dropdown-item" href="manage_work_orders.php">Manage Work Order</a>
            </li>
            <li>
              <a class="dropdown-item" href="calendar.php">Calendar</a>
            </li>
          </ul>

        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Admin
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
            <li>
              <a class="dropdown-item" href="users.php">User Management</a>
            </li>
          </ul>

        </li>



        <li class="nav-item">
          <a class="nav-link" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>