<?php
// users.php
include "database.php";
// 1. Show all PHP errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Connect to MySQL (mysqli)
$db_server = "localhost";
$db_user   = "root";
$db_pass   = "";
$db_name   = "psabe";

$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
if (!$conn) {
    http_response_code(500);
    exit("DB Connection failed: " . mysqli_connect_error());
}

// 3. Fetch users
$sql    = "
  SELECT 
    id, 
    email, 
    first_name, 
    last_name, 
    sex, 
    university, 
    photo
  FROM users
";
$result = mysqli_query($conn, $sql);
if (!$result) {
    http_response_code(500);
    exit("Query Error: " . mysqli_error($conn));
}
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>User Management</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet"/>

  <style>
    body, html { height:100%; margin:0; font-family:'Segoe UI',sans-serif; }
    .d-flex { display:flex; height:100%; }
    #sidebar { width:250px; background:#343a40; color:#fff; padding:1rem; display:flex; flex-direction:column; }
    #sidebar h4 { text-align:center; margin-bottom:2rem; }
    #sidebar .nav-link { color:#ced4da; margin-bottom:.5rem; padding:.5rem 1rem; border-radius:.25rem; }
    #sidebar .nav-link.active, #sidebar .nav-link:hover { background:#495057; color:#fff; }
    #sidebar .btn-logout { margin-top:auto; background:#ff416c; color:#fff; border:none; padding:.5rem; border-radius:.25rem; }
    #sidebar .btn-logout:hover { background:#ff4b2b; }

    #content { flex-grow:1; background:#f8f9fa; padding:1rem 2rem; overflow-y:auto; }
    .section-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; }
    .table-container { overflow-x:auto; }
    table.dataTable tbody tr.disabled { opacity:0.5; }

    /* Print card styling */
    .print-card { page-break-inside: avoid; text-align:center; margin-bottom:1.5rem; }
    .print-card img { width:320px; margin-bottom:.5rem; }
  </style>
</head>
<body>
  <div class="d-flex">
    <!-- Sidebar -->
    <nav id="sidebar">
      <h4>Teno in Nach</h4>
      <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link" href="admin.html">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="#">Users</a></li>
        <li class="nav-item"><a class="nav-link" href="transactions.php">Transactions</a></li>
        <li class="nav-item"><a class="nav-link" href="tabulation.php">Tabulation</a></li>
      </ul>
      <button class="btn btn-logout">Logout</button>
    </nav>

    <!-- Main Content -->
    <div id="content">
      <h2 class="h4 section-header">
        User Management
        <div class="d-flex gap-2">
          <button id="export-btn" class="btn btn-outline-primary btn-sm">Export CSV</button>
          <button id="print-all-btn" class="btn btn-success btn-sm">Print All IDs</button>
        </div>
      </h2>

      <div class="table-container">
        <table id="users-table" class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>Email</th>
              <th>Name</th>
              <th>Sex</th>
              <th>University</th>
              <th>View ID</th>
              <th>Edit</th>
              <th>Disable</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($users as $u): 
              $fullName = $u['first_name'] . ' ' . $u['last_name'];
            ?>
            <tr 
              data-user-id="<?= $u['id'] ?>"
              data-email="<?= htmlspecialchars($u['email']) ?>"
              data-full-name="<?= htmlspecialchars($fullName) ?>"
              data-sex="<?= htmlspecialchars($u['sex']) ?>"
              data-university="<?= htmlspecialchars($u['university']) ?>"
              data-photo="<?= htmlspecialchars($u['photo']) ?>"
            >
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= htmlspecialchars($fullName) ?></td>
              <td><?= htmlspecialchars($u['sex']) ?></td>
              <td><?= htmlspecialchars($u['university']) ?></td>
              <td>
                <button class="btn btn-sm btn-light btn-view-id" data-user-id="<?= $u['id'] ?>">
                  View ID
                </button>
              </td>
              <td><button class="btn btn-sm btn-warning btn-edit">Edit</button></td>
              <td><button class="btn btn-sm btn-danger btn-toggle">Disable</button></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- View ID Modal -->
  <div class="modal fade" id="viewIdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">ID Card Preview</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="view-modal-body">
          <div class="text-center py-5"><div class="spinner-border" role="status"></div></div>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit User Modal -->
  <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form id="edit-user-form" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" id="edit-email" class="form-control form-control-sm" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" id="edit-full-name" class="form-control form-control-sm" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Sex</label>
            <select id="edit-sex" class="form-select form-select-sm">
              <option value="M">M</option>
              <option value="F">F</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">University</label>
            <input type="text" id="edit-university" class="form-control form-control-sm" required>
          </div>
        </div>
        <div class="modal-footer justify-content-end">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Hidden print area -->
  <div id="print-area" style="display:none;"></div>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
  <script>
  $(function(){
    const table = $('#users-table').DataTable();

    // —— Export CSV ——
    $('#export-btn').click(() => {
      const data = table.rows().data().toArray();
      const csv = ['Email,Name,Sex,University']
        .concat(data.map(r => `${r[0]},${r[1]},${r[2]},${r[3]}`))
        .join('\r\n');
      const url = URL.createObjectURL(new Blob([csv],{type:'text/csv'}));
      $('<a>').attr({ href:url, download:'users.csv' })[0].click();
      URL.revokeObjectURL(url);
    });

    // —— View ID ——
    const viewModal = new bootstrap.Modal($('#viewIdModal'));
    $('#users-table').on('click', '.btn-view-id', function(){
      const tr = $(this).closest('tr');
      const userId = tr.data('user-id');
      const photo  = tr.data('photo') || 'images/default.png';

      $('#view-modal-body').html(`
        <div class="text-center">
          <img src="${photo}" class="img-fluid mb-3" style="max-width:320px;">
          <h5>${tr.data('full-name')}</h5>
          <p>${tr.data('sex')}</p>
          <p>${tr.data('university')}</p>
          <p class="small text-break">${tr.data('email')}</p>
        </div>
      `);
      viewModal.show();
    });

    // —— Edit User ——
    const editModal = new bootstrap.Modal($('#editUserModal'));
    let currentRow;
    $('#users-table').on('click', '.btn-edit', function(){
      currentRow = $(this).closest('tr');
      $('#edit-email').val(currentRow.data('email'));
      $('#edit-full-name').val(currentRow.data('full-name'));
      $('#edit-sex').val(currentRow.data('sex'));
      $('#edit-university').val(currentRow.data('university'));
      editModal.show();
    });
    $('#edit-user-form').submit(function(e){
      e.preventDefault();
      const email    = $('#edit-email').val();
      const fullName = $('#edit-full-name').val();
      const sex      = $('#edit-sex').val();
      const uni      = $('#edit-university').val();

      currentRow
        .data('email', email)
        .data('full-name', fullName)
        .data('sex', sex)
        .data('university', uni);

      table.row(currentRow).data([
        email,
        fullName,
        sex,
        uni,
        currentRow.find('.btn-view-id')[0].outerHTML,
        currentRow.find('.btn-edit')[0].outerHTML,
        currentRow.find('.btn-toggle')[0].outerHTML
      ]).invalidate().draw(false);

      editModal.hide();
    });

    // —— Disable / Enable ——
    $('#users-table').on('click', '.btn-toggle', function(){
      const btn = $(this), tr = btn.closest('tr');
      const disabling = btn.text() === 'Disable';
      btn.text(disabling ? 'Enable' : 'Disable')
         .toggleClass('btn-danger btn-success');
      tr.toggleClass('disabled');
    });

    // —— Print All IDs ——
    $('#print-all-btn').click(() => {
      const tpl = 'images/default.png';
      const data = table.rows().data().toArray();
      const area = $('#print-area').empty().show();

      data.forEach(r => {
        const [email, name, sex, uni] = r;
        area.append(`
          <div class="print-card">
            <img src="${tpl}">
            <div><strong>${name}</strong></div>
            <div>${sex}</div>
            <div>${uni}</div>
            <div class="small">${email}</div>
          </div>
        `);
      });

      window.print();
      area.hide().empty();
    });
  });
  </script>
</body>
</html>
