
<?php
session_start();
$errors = $_SESSION["reg_errors"] ?? [];
unset($_SESSION["reg_errors"]);
?>


<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Register</title>
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
  width:360px;
  background:#111a2e;
  padding:60px;
  border-radius:12px
}
h2{margin:0 0 16px}
label{font-size:13px;color:#9bb0d3}
input{
  width:100%;
  padding:10px;
  margin:6px 0 4px;
  border-radius:8px;
  border:1px solid #223155;
  background:#0c1528;
  color:#fff
}
.err{
  font-size:12px;
  color:#ff5a5f;
  min-height:14px;
  margin-bottom:6px
}
button{
  width:100%;
  padding:10px;
  margin-top:10px;
  border:none;
  border-radius:8px;
  background:#f28b00;
  color:#fff;
  font-weight:bold;
  cursor:pointer
}
button:disabled{opacity:.5}
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
  <h2>Create account</h2>


  <form action="register_action.php" method="post" novalidate>
       <?php if ($errors): ?>
         <div class="err"><?= htmlspecialchars(implode(" | ", $errors)) ?></div>
        <?php endif; ?>
    <label>Username</label>
    <input id="user" name="username" placeholder="Min 3 characters">
    <div class="err" id="uErr"></div>

    <label>Email</label>
    <input id="email" name="email" placeholder="name@example.com">
    <div class="err" id="eErr"></div>

    <label>Phone</label>
    <input id="phone" name="phone" placeholder="+9627XXXXXXXX">
    <div class="err" id="pErr"></div>

    <label>Password</label>
    <input id="pass" name="password" type="password"
           placeholder="6+ chars, 1 Capital, 1 Number">
    <div class="err" id="pwErr"></div>

    <label>Confirm password</label>
    <input id="confirm" name="confirmPassword" type="password"
           placeholder="Same password">
    <div class="err" id="cErr"></div>


    <button id="btn" type="submit" disabled>Create account</button>

    <div class="link">
      Already have an account?
      <a href="login.php">Back to login</a>
    </div>

  </form>
</div>

<script>
const user = document.getElementById("user");
const email = document.getElementById("email");
const phone = document.getElementById("phone");
const pass = document.getElementById("pass");
const confirm = document.getElementById("confirm");
const btn = document.getElementById("btn");

const uErr = document.getElementById("uErr");
const eErr = document.getElementById("eErr");
const pErr = document.getElementById("pErr");
const pwErr = document.getElementById("pwErr");
const cErr = document.getElementById("cErr");

const emailR = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
const phoneR = /^\+?\d{8,15}$/;
const passR  = /^(?=.*[A-Z])(?=.*\d).{6,}$/;

function validateUser(){
  if(!user.value){uErr.textContent="";return;}
  uErr.textContent = user.value.length>=3 ? "" : "Min 3 characters";
}
function validateEmail(){
  if(!email.value){eErr.textContent="";return;}
  eErr.textContent = emailR.test(email.value) ? "" : "Invalid email";
}
function validatePhone(){
  if(!phone.value){pErr.textContent="";return;}
  pErr.textContent = phoneR.test(phone.value) ? "" : "Invalid phone";
}
function validatePass(){
  if(!pass.value){pwErr.textContent="";validateConfirm();return;}
  pwErr.textContent = passR.test(pass.value)
    ? "" : "1 capital & 1 number required";
  validateConfirm();
}
function validateConfirm(){
  if(!confirm.value){cErr.textContent="";return;}
  cErr.textContent = confirm.value===pass.value
    ? "" : "Passwords do not match";
}

function updateButton(){
  const allOk =
    user.value && email.value && phone.value && pass.value && confirm.value &&
    !uErr.textContent && !eErr.textContent &&
    !pErr.textContent && !pwErr.textContent && !cErr.textContent;

  btn.disabled = !allOk;
}

[user,email,phone,pass,confirm].forEach(el=>{
  el.addEventListener("input",()=>{
    validateUser();
    validateEmail();
    validatePhone();
    validatePass();
    validateConfirm();
    updateButton();
  });
});
</script>

</body>
</html>
