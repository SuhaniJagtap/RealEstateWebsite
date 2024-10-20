<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

if(isset($_POST['submit'])){

   $id = create_unique_id();
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING); 
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_VALIDATE_EMAIL);
   $pass = $_POST['pass'];
   $c_pass = $_POST['c_pass'];

   if(!$email){
      $warning_msg[] = 'Invalid email format!';
   } else {
       $select_users = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
       $select_users->execute([$email]);

       if($select_users->rowCount() > 0){
          $warning_msg[] = 'Email already taken!';
       }else{
          if($pass != $c_pass){
             $warning_msg[] = 'Passwords do not match!';
          }else{
             // Hash the password securely
             $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

             $insert_user = $conn->prepare("INSERT INTO `users`(id, name, number, email, password) VALUES(?,?,?,?,?)");
             $insert_success = $insert_user->execute([$id, $name, $number, $email, $hashed_pass]);
             
             if($insert_success){
                // Send the email to the user
                $to = $email;
                $subject = "Registration Successful";
                $message = "Dear $name,\n\nThank you for registering at our website.\n\nBest regards,\nThe Team";
                $headers = "From: no-reply@yourdomain.com\r\n";
                // Note: The mail() function may not work in all environments.
                // For production use, consider using PHPMailer or a similar library.
                mail($to, $subject, $message, $headers);

                // Set the user_id cookie and redirect to home page
                setcookie('user_id', $id, time() + 60*60*24*30, '/');
                header('location:home.php');

             } else {
                $error_msg[] = 'Failed to register user!';
             }

          }
       }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>
   <link type="image/png" sizes="16x16" rel="icon" href="images/hoe.png">

   <!-- Font Awesome CDN Link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- Custom CSS File Link -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- Register Section Starts -->

<section class="form-container">

   <form action="" method="post">
      <h3>Create an account!</h3>
      <input type="text" name="name" required maxlength="50" placeholder="Enter your name" class="box">
      <input type="email" name="email" required maxlength="50" placeholder="Enter your email" class="box">
      <input type="tel" name="number" required pattern="[0-9]{10}" maxlength="10" placeholder="Enter your number" class="box">
      <input type="password" name="pass" required maxlength="20" placeholder="Enter your password" class="box">
      <input type="password" name="c_pass" required maxlength="20" placeholder="Confirm your password" class="box">
      <p>Already have an account? <a href="login.php">Login now</a></p>
      <input type="submit" value="Register now" name="submit" class="btn">
   </form>

</section>

<!-- Register Section Ends -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<?php include 'components/footer.php'; ?>

<!-- Custom JS File Link -->
<script src="js/script.js"></script>

<?php include 'components/message.php'; ?>

</body>
</html>
