$(document).ready(function() {
  $('#otpcon').hide();
  $("#check").click(function() {
    alert('Hello');
    // $.ajax({
    //   // PHP file handling the request
    //   url: 'AJAX/OTPProcess.php',
    //   // Request method
    //   type: 'POST',
    //   // Data to be sent to the server       
    //   data: { 
    //     message: 'Hello from AJAX!',
    //     email: $('#email').val()
    //   },
    //   // Callback function to handle successful AJAX response
    //   success: function(response) { 
    //     // Update HTML content with response
    //     $("#response").html(response);
    //     $('#otpcon').slideDown(200);
    //     $('#check').val('Resend OTP');
    //   },
    //   // Callback function to handle error
    //   error: function(xhr, status, error) { 
    //     console.error(xhr.responseText);  
    //   }
    // });
  });
});
