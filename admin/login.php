
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CEDUMart | Login</title>

  <base href="">
  <link rel="icon" href="/CEDUMART-SE2/cedumart/auth/logo.png">
  <link rel="stylesheet" type="text/css" href="login.css">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <script type="text/javascript">  
    if (window.history.replaceState) {
      window.history.replaceState(null, null, window.location.href);
    }
  </script>
</head>

<body>
  <div class="wrapper">
    <div class="logo-container">
      <img src="/CEDUMART-SE2/cedumart/auth/logo.png" alt="Logo" class="logo">
      <span class="text">Cedumart</span><span class="dot">.</span> 
    </div>

    <div class="form-wrapper">
      <form action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
        <div class="input-group">
          <i class="material-icons left-icon">person</i>
          <input type="text" id="usernameInput" name="usernameInput" required>
          <label>Username</label>
        </div>
        <div class="input-group">
          <i class="material-icons left-icon">lock</i>
          <input id="passwordInput" name="passwordInput" type="password" required>
          <i id="show-password" class="material-symbols-outlined" style="cursor: pointer; margin-left: -40px;">visibility</i>
          <label>Password</label>
        </div>

        <?php if ($showNotFound): ?>
          <div class="not-found">
              <p><i class="material-icons">error_outlined</i>Invalid username or password. <br>Please try again.</p>
          </div>
        <?php endif; ?>

        <?php if ($showLoginBlocked): ?>
          <div class="not-found">
              <p><i class="material-icons">error_outlined</i>Too many login attempts. <br>Please try again after <?php echo $remaining_time; ?> minutes.</p>
          </div>
        <?php endif; ?>

        <button type="submit" name="login">Login</button>
      </form>
    </div>
    <a href="find_account">Forgot Password?</a>
  </div>

  <script>
      document.getElementById('show-password').addEventListener('click', function() {
          var passwordInput = document.getElementById('passwordInput');
          var icon = document.getElementById('show-password');
          
          if (passwordInput.type === 'password') {
              passwordInput.type = 'text';
              icon.textContent = 'visibility_off';
          } else {
              passwordInput.type = 'password';
              icon.textContent = 'visibility';
          }
      });
  </script>

</body>

</html>