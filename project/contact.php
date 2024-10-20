<?php  

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Include PHPMailer autoloader if using Composer
// If manually installed, include the required files:
// require 'path/to/PHPMailer/src/Exception.php';
// require 'path/to/PHPMailer/src/PHPMailer.php';
// require 'path/to/PHPMailer/src/SMTP.php';

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

if(isset($_POST['send'])){

   $msg_id = create_unique_id();
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_EMAIL);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $message = $_POST['message'];
   $message = filter_var($message, FILTER_SANITIZE_STRING);

   $verify_contact = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
   $verify_contact->execute([$name, $email, $number, $message]);

   if($verify_contact->rowCount() > 0){
      $warning_msg[] = 'Message already sent!';
   }else{
      $send_message = $conn->prepare("INSERT INTO `messages`(id, name, email, number, message) VALUES(?,?,?,?,?)");
      $send_message->execute([$msg_id, $name, $email, $number, $message]);

      // Send email after successful message submission
      $mail = new PHPMailer(true);

      try {
          //Server settings
          $mail->SMTPDebug = 0;                                       // Disable verbose debug output
          $mail->isSMTP();                                            // Send using SMTP
          $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server to send through
          $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
          $mail->Username   = 'your_email@gmail.com';                 // SMTP username
          $mail->Password   = 'your_email_password';                  // SMTP password
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption
          $mail->Port       = 587;                                    // TCP port to connect to

          //Recipients
          $mail->setFrom('your_email@gmail.com', 'Your Name');
          $mail->addAddress($email, $name);                           // Add a recipient

          // Content
          $mail->isHTML(true);                                        // Set email format to HTML
          $mail->Subject = 'Thank you for contacting us';
          $mail->Body    = '<p>Dear ' . $name . ',</p><p>Thank you for reaching out. We have received your message and will get back to you shortly.</p><p>Best regards,<br>Your Company</p>';
          $mail->AltBody = 'Dear ' . $name . ',\n\nThank you for reaching out. We have received your message and will get back to you shortly.\n\nBest regards,\nYour Company';

          $mail->send();
          $success_msg[] = 'Message sent successfully! A confirmation email has been sent to your email address.';
      } catch (Exception $e) {
          $warning_msg[] = 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
      }
   }

}
?>
