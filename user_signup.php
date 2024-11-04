<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true){
  header("location: home.php");
}


$showAlert = false;
$showError = false;
if($_SERVER["REQUEST_METHOD"]=="POST"){
  include 'partials/_dbconnect.php';
  $name= $_POST["name"];
  $email= $_POST["email"];

  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {  
  // Password complexity pattern (at least one lowercase, one uppercase, one digit, one special char, and minimum 8 characters)
  $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';

  $password = $_POST["password"];
  $cpassword = $_POST["cpassword"];

  if ($email == '' || $password == '') {
    $showError = "Enter valid Email/password";
  }
  // Preg_match for the password pattern
  elseif (!preg_match($pattern, $password)) {
    $showError = "Password must be at least 8 characters long, include at least one uppercase letter, one lowercase letter, one digit, and one special character.";
  } 
  else{
    $existSql = "SELECT * FROM `users` WHERE email = '$email' ";
    $result = mysqli_query($conn, $existSql);
    $numExistRows = mysqli_num_rows($result);

    if ($numExistRows > 0){
      $showError = "Email already registered. Try logging in.";
    }
    else {
      if ($password == $cpassword){
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO `users` ( `name`, `email`, `password`, `date`, `account_status`, `resetcode`) 
        VALUES ( '$name', '$email', '$hash', current_timestamp(), 'Not Verified', '0');";
        $result = mysqli_query($conn, $sql);
        

        if($result){
          $otp = random_int(10000000, 99999999);
          $hashotp = password_hash($otp, PASSWORD_DEFAULT);

        $sql = "UPDATE `users`
        SET `resetcode` = '$hashotp'
        WHERE `email` = '$email';";
        $result = mysqli_query($conn, $sql);
        if($result){
          $showAlert = true;
        }
        // PHP mailer
        require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';


//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Server settings
    //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'aftereditofficial@gmail.com';                     //SMTP username
    $mail->Password   = 'asnfswjtqvsqpngu';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('from@example.com', 'ETIFFY');
    $mail->addAddress($email);     //Add a recipient
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Verification code from homemade';
    $mail->Body    = '<h1>'.$email.' </h1><br><h1>'.$otp.' </h1>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}


         //PHP mailer
        session_start();
        $_SESSION['email'] = $email;
      
        header("location: email_verify.php");

          $showAlert = true;
        }
      }
      else {
        $showError = "Passwords do not match.";
      }
    }
  }
} 
else {
  $showError =  "The email address is not valid.";
  }
} 
?>



<!-- Source html-->
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=ABeeZee&display=swap" rel="stylesheet">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=ABeeZee&display=swap');
    </style>
    <link rel="stylesheet" href="./output.css">


    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AfterEdit • Signup</title>
    <link rel="icon" type="image/png" href="css/favicon.png">

</head>

<body>
<div class="gtranslate_wrapper"></div>
      <script>window.gtranslateSettings = {"default_language":"en","detect_browser_language":true,"wrapper_selector":".gtranslate_wrapper"}</script>
      <script src="https://cdn.gtranslate.net/widgets/latest/float.js" defer></script>

    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous">
    </script> -->
    <?php
  if ($showAlert) {
    echo ' <div class="alert alert-success alert-dismissible fade show" role="alert"> 
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
  </svg>
    <strong>Congratulations!</strong> Your account has been created. You can now   <a class="col-lg-10 fs-4 text-center desc py-2 text-dark text-decoration-none" href="login.php"><button type="button"
    class="btn btn-dark me-2">Login</button></a>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
';
  }
  if ($showError) {
    echo ' <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16">
  <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
</svg>
    <strong>Error. </strong> '.$showError.'
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
';
  } ?>

    <div class="w-full max-w-screen-lg mx-auto ">
     
    <nav
      class="hidden  lg:flex sm:max-w-xl md:max-w-2xl lg:max-w-5xl xl:max-w-7xl w-full items-center justify-between max-w-7xl mx-auto font-poppins py-4">
      <a href="index.php"><img src="./images/logo/logo.webp" alt="logo" class="w-36"></a>

      <div class="flex">

        <div class="mx-3">
          <a href="user_login.php" class="bg-gray-900 hover:bg-gray-800 focus:border-white cursor-pointer w-max transition-all ease-in-out duration-75 px-6 py-2 text-white rounded-full">Login</a>
        </div>

    </nav>

      <div class="w-full h-[80vh] py-8 flex">
          <div class="flex flex-col w-1/2 max-h-[600px] h-full justify-center">
              <span class="h-64">
                <img src="./images/signup.png" alt="" class="h-full">
              </span>
          </div>
          <div class="form flex flex-col w-1/2 max-h-[600px] h-full justify-center">
              <div class="w-96 ml-auto flex flex-col gap-8">
                <span class="flex flex-col gap-4">
                  <h1 class="text-3xl font-bold">Hello, Good to see you</h1>
                </span>

                <form action="user_signup.php" method="post" class="flex flex-col gap-4">
                  <div class="form-group flex flex-col">
                    <label for="name" class="mb-2">Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your name" class="p-2 form-control outline-2 border border-black w-full" required>
                  </div>
                  <div class="form-group flex flex-col">
                    <label for="email" class="mb-2">Email</label>
                    <input type="email" id="email-id" name="email" placeholder="example@example.com" class="p-2 form-control outline-2 border border-black w-full" required>
                  </div>
                  <div class="form-group flex flex-col">
                    <label for="password" class="mb-2">Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter your password" class="p-2 form-control outline-2 border border-black w-full" required>
                  </div>
                  <div class="form-group flex flex-col">
                    <label for="cpassword" class="mb-2">Confirm Password</label>
                    <input type="password" name="cpassword" id="cpassword" placeholder="Confirm your password" class="p-2 form-control outline-2 border border-black w-full" required>
                  </div>
                  <button type="submit" class="px-2 py-2 mt-2 rounded-lg text-white bg-blue-500">Create Account</button>
                  <div class="w-full my-1 flex justify-center">
                    <p class="text-center">Already have an account? <a href="user_login.php">Login!</a></p>
                  </div>
              </form>
              </div>
          </div>
      </div>
  </div>

    <script>
      const eyeBtnPassword =document.getElementById("eye-btn-p");
      const eyeBtnConfirmPassword =document.getElementById("eye-btn-cp");
      const passwordField =document.getElementById("password");
      const confirmPasswordField =document.getElementById("cpassword");

      eyeBtnPassword.addEventListener('click', ()=>{
        let attr =passwordField.getAttribute('type')
        if(attr == "password"){
          passwordField.setAttribute('type','text');
          eyeBtnPassword.classList.remove("bi-eye-fill")
          eyeBtnPassword.classList.add("bi-eye-slash-fill")
        }else{
          passwordField.setAttribute('type','password');
          eyeBtnPassword.classList.add("bi-eye-fill")
          eyeBtnPassword.classList.remove("bi-eye-slash-fill")
        }
      })

      eyeBtnConfirmPassword.addEventListener('click', ()=>{
        let attr =confirmPasswordField.getAttribute('type')
        if(attr == "password"){
          confirmPasswordField.setAttribute('type','text');
          eyeBtnConfirmPassword.classList.remove("bi-eye-fill")
          eyeBtnConfirmPassword.classList.add("bi-eye-slash-fill")
        }else{
          confirmPasswordField.setAttribute('type','password');
          eyeBtnConfirmPassword.classList.add("bi-eye-fill")
          eyeBtnConfirmPassword.classList.remove("bi-eye-slash-fill")
        }
      })
    </script>
  
</body>

</html>