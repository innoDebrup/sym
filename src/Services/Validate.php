<?php
namespace App\Services;

use App\Services\Env\LoadEnv;
/**
 * Class for performing various Validations.
 */
class Validate {

  /**
   * Variable to store email error message.
   *
   * @var string
   */
  private $emailError = '';

  /**
   * Variable to store password error message.
   *
   * @var string
   */
  private $passwordError = '';

  /**
   * Function to validate email.
   *
   * @param string $email
   * 
   * @return bool
   *  Returns FALSE incase of incorrect format/Undeliverable/Temporary Email
   *  OR
   *  Returns TRUE incase all checks are passed.
   */
  public function validEmail(string $email) {
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
      $this->emailError = 'Invalid Email address format!';
      return FALSE;
    }
    // LoadEnv::loadDotEnv();
    // $client = new Client();
    // $access_key = $_ENV['ACCESS_KEY'];
    // $response = $client->request('GET', 'https://emailvalidation.abstractapi.com/v1/?api_key=' . $access_key . '&email=' . $email);
    // // Stores the response received in the form of an array.
    // $data = json_decode($response->getBody(), TRUE);
    // if ($data["is_disposable_email"]["value"]) {
    //   $this->emailError = 'Cannot use temporary Email address!';
    //   return FALSE;
    // } 
    // elseif ($data['deliverability'] === 'UNDELIVERABLE') {
    //   $this->emailError = 'Email address does not exists!';
    //   return FALSE;
    // } 
    // else {
      return TRUE;
    // }
  }

  /**
   * Function to validate password format.
   *
   * @param string $password
   *  Password to be validated.
   * 
   * @return bool
   *  Returns FALSE if password mismatched OR TRUE if password length is okay.
   */
  public function validPassword(string $password) {
    if (strlen($password) < 6 ) {
      $this->passwordError = 'Password cannot be less than 6 characters!';
      return FALSE;
    }
    else if (strlen($password) > 20) {
      $this->passwordError = 'Password cannot be more than 20 characters!';
      return FALSE;
    }
    else {
      return TRUE;
    }
  }

  /**
   * Getter function to return Email Error Message.
   *
   * @return string
   */
  public function getEmailErr() {
    return $this->emailError;
  }

  /**
   * Getter function to return Password Error Message.
   *
   * @return string
   */
  public function getPasswordErr() {
    return $this->passwordError;
  }
}
