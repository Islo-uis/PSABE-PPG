<?php
include "../database.php";

// 1. Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Connect to MySQL
$db_server = "localhost";
$db_user   = "root";
$db_pass   = "";
$db_name   = "psabe";

$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
if (!$conn) {
    http_response_code(500);
    exit("DB Connection failed: " . mysqli_connect_error());
}

// 3. Fetch transactions
$sql = "
  SELECT
    id,
    order_number,
    name,
    email,
    reference_no,
    amount,
    products,
    confirmed,
    status
  FROM transactions
  ORDER BY id DESC
";
$res = mysqli_query($conn, $sql);
if (!$res) {
    http_response_code(500);
    exit("Query Error: " . mysqli_error($conn));
}
$txns = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_free_result($res);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Transactions</title>

  <!-- Bootstrap & DataTables CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet"/>

  <style>
    body, html { height:100%; margin:0; font-family:'Segoe UI',sans-serif; }
    .d-flex { display:flex; height:100%; }
    #sidebar {
      width:250px; background:#343a40; color:#fff;
      display:flex; flex-direction:column; padding:1rem;
    }
    #sidebar h4 { text-align:center; margin-bottom:2rem; }
    #sidebar .nav-link {
      color:#ced4da; margin-bottom:.5rem;
      padding:.5rem 1rem; border-radius:.25rem;
    }
    #sidebar .nav-link.active,
    #sidebar .nav-link:hover { background:#495057; color:#fff; }
    #sidebar .btn-logout {
      margin-top:auto; background:#ff416c; color:#fff;
      border:none; padding:.5rem; border-radius:.25rem;
    }
    #sidebar .btn-logout:hover { background:#ff4b2b; }

    #content { flex-grow:1; background:#f8f9fa; padding:1rem 2rem; overflow-y:auto; }
    .section-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; }
    .table-container { overflow-x:auto; }

    /* status badges */
    .badge-status {
      padding:.4em .7em;
      font-size:.85em;
      color:#fff;
      border-radius:.25rem;
    }
    .status-not { background:#dc3545; }      /* red */
    .status-proc { background:#ffc107; }     /* yellow */
    .status-claimed { background:#28a745; }  /* green */
  </style>
</head>
<body>
  <div class="d-flex">
    <!-- Sidebar (same as users.php) -->
    <nav id="sidebar">
      <h4>Teno in Nach</h4>
      <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link" href="admin.html">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
        <li class="nav-item"><a class="nav-link active" href="#">Transactions</a></li>
        <li class="nav-item"><a class="nav-link" href="tabulation.html">Tabulation</a></li>
      </ul>
      <button class="btn btn-logout">Logout</button>
    </nav>

    <!-- Main Content -->
    <div id="content">
      <h2 class="h4 section-header">Transactions</h2>

      <div class="table-container">
        <table id="txns-table" class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Name</th>
              <th>Email</th>
              <th>Reference No.</th>
              <th>Amount</th>
              <th>Products</th>
              <th>View Receipt</th>
              <th>Confirmation</th>
              <th>Order Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($txns as $t): ?>
            <tr data-id="<?= $t['id'] ?>"
                data-receipt="<?= htmlspecialchars($t['receipt_url']) ?>"
                data-status="<?= htmlspecialchars($t['status']) ?>">
              <td><?= htmlspecialchars($t['order_number']) ?></td>
              <td><?= htmlspecialchars($t['name']) ?></td>
              <td><?= htmlspecialchars($t['email']) ?></td>
              <td><?= htmlspecialchars($t['reference_no']) ?></td>
              <td><?= number_format($t['amount'],2) ?></td>
              <td><?= htmlspecialchars($t['products']) ?></td>
              <td class="text-center">
                <button class="btn btn-sm btn-info btn-view-receipt">
                  View Receipt
                </button>
              </td>
              <td class="text-center">
                <?php if(!$t['confirmed']): ?>
                <button class="btn btn-sm btn-primary btn-confirm">
                  Confirm
                </button>
                <?php else: ?>
                <span class="text-success">✔</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <?php
                  $st = strtolower($t['status']);
                  $cls = $st==='claimed' ? 'status-claimed'
                       : ($st==='processing' ? 'status-proc'
                       : 'status-not');
                ?>
                <span class="badge-status <?= $cls ?>">
                  <?= htmlspecialchars(ucfirst($st)) ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Receipt Modal -->
  <div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Receipt Preview</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center" id="receipt-body">
          <div class="spinner-border" role="status"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <script>
  $(function(){
    // 1) Init DataTable
    $('#txns-table').DataTable();

    // 2) View Receipt
    const receiptModal = new bootstrap.Modal($('#receiptModal'));
    $('#txns-table').on('click', '.btn-view-receipt', function(){
      const tr = $(this).closest('tr');
      const url = tr.data('receipt');
      $('#receipt-body').html(`<img src="${url}" class="img-fluid">`);
      receiptModal.show();
    });

    // 3) Confirm button
    $('#txns-table').on('click', '.btn-confirm', function(){
      const btn = $(this);
      const tr  = btn.closest('tr');
      const id  = tr.data('id');
      // here you’d fire an AJAX to update confirmed in DB…
      // For demo, we just switch UI:
      btn.replaceWith('<span class="text-success">✔</span>');
      // also change status badge if you like
      tr.find('.badge-status')
        .removeClass('status-not status-proc status-claimed')
        .addClass('status-proc')
        .text('Processing');
    });
  });
  </script>
</body>
</html>
