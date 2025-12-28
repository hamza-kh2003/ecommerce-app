
<?php
session_start();
$errors = $_SESSION["login_errors"] ?? [];
$success = $_SESSION["login_success"] ?? "";
unset($_SESSION["login_errors"], $_SESSION["login_success"]);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body{
  min-height:100vh;
  display:grid;
  place-items:center;
    background-image: url("./img/background.png");
  background-size: cover;       
  background-position: center;  
  background-repeat: no-repeat;
  background-attachment: fixed;  

  color:#fff;
  font-family:Arial;
  margin:0
}
.box{
  width:340px;
  background:#111a2e;
  padding:60px;
  border-radius:12px
}
h2{margin:0 0 16px}
label{font-size:13px;color:#9bb0d3}
input{
  width:100%;
  padding:10px;
  margin:6px 0 12px;
  border-radius:8px;
  border:1px solid #223155;
  background:#0c1528;
  color:#fff
}
button{
  width:100%;
  padding:10px;
  border:none;
  border-radius:8px;
  background:#f28b00;
  color:#fff;
  font-weight:bold;
  cursor:pointer
}
button:disabled{opacity:.5;cursor:not-allowed}
.link{
  margin-top:12px;
  font-size:13px;
  text-align:center
}
a{color:#9bb0d3;text-decoration:none}

 .back{
  text-decoration:underline;
   display:inline-block;
   margin-bottom:12px;
 }
</style>
</head>

<body>

<div class="box">
   <a href="./index.php" class="back"> Back To Shop</a>
  <h2>Login</h2>


  <form action="login_action.php" method="post">
    <?php if ($success): ?>
  <div class="link" style="color:#64d97b"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($errors): ?>
  <div class="link" style="color:#ff5a5f"><?= htmlspecialchars(implode(" | ", $errors)) ?></div>
<?php endif; ?>


    <label>Email</label>
    <input id="email" name="email" type="email" placeholder="name@example.com">

    <label>Password</label>
    <input id="password" name="password" type="password" placeholder="••••••••">

  
    <button id="btn" type="submit" disabled>Login</button>

    <div class="link">
      Don’t have an account?
      <a href="register.php">Create one</a>
    </div>

  </form>
</div>

<script>
const email = document.getElementById("email");
const password = document.getElementById("password");
const btn = document.getElementById("btn");

function updateButton(){

  btn.disabled = !(email.value.trim() && password.value.trim());
}

email.addEventListener("input", updateButton);
password.addEventListener("input", updateButton);
</script>

</body>
</html>

