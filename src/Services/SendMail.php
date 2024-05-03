<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use App\Services\Env\LoadEnv;

class SendMail {
  private $content;
  private $mail;
  
  /**
   * Constructor that setups the mailer configuration.
   */
  public function __construct() {
    $mail = new PHPMailer(TRUE);
    $mail->isSMTP();
    // Setting the sender mail configuration.
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = TRUE;
    // Accessing key values from .env file.
    LoadEnv::loadDotEnv();
    $mail->Username = $_ENV['USER_NAME'];
    $mail->Password = $_ENV['PASSWORD'];
    // SMTP port.
    $mail->Port = 465;
    // Standard TLS encryption.
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    // Setting Mail content type and sender info.
    $mail->isHTML(TRUE);
    $mail->setFrom($mail->Username);
    $this->mail = $mail;
  }
  /**
   * Function to set email message and other contents to be sent.
   *
   * @param string $message
   *  Message or Link to be sent.
   * 
   * @return void
   */
  public function setContent(string $message) {
    $this->content = $message;
  }
  
  /**
   * Function to send the Reset Password mail.
   *
   * @param string $email
   *  Email id to which the email is to be sent to.
   * 
   * @return void
   */
  public function sendResetMail($email) {
    $mail = $this->mail;
    $mail->addAddress($email);
    $mail->Subject = ("Reset Your Password !!");
    $mail->Body = "<h1>Reset Password Link</h1><p>Click this <a href='$this->content'>link</a> to reset the password.</p>";
    $mail->send();
  }
  /**
   * Function to send the OTP mail.
   * 
   * @param string $email
   *  Email id to which the email is to be sent to.
   * 
   * @return void
   */
  public function sendOTPMail($email) {
    $mail = $this->mail;
    $mail->addAddress($email);
    $mail->Subject = ("OTP for Email Verification !!");
    $mail->Body = "<h1>Thank you for Registering! </h1> <h3>This is the last step for completing your Registration.</h3> <p>Use this OTP to verify your email on our website:</p> <h3>$this->content</h3>";
    $mail->send();
  }
}
