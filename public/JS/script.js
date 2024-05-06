$(document).ready(function() {
  $(document).on('click', '#getotpcon', function() {
    $.ajax({
      // PHP file handling the request
      url: '/otp',
      // Request method
      type: 'POST',
      // Data to be sent to the server       
      data: { 
        message: 'Hello from AJAX!',
        email: $('#email').val()
      },
      // Callback function to handle successful AJAX response
      success: function(response) { 
        // Update HTML content with response
        if (response.valid) {
          $('.hidden').attr('id','otpcon');
          $('#otpcon').hide();
          $('#otpcon').slideDown(200);
          $('#check').val('Resend OTP');
        }
        // $('#message').html('<h3>OTP sent! Check your mail.</h3>');
        $('#message').html(response.message);
      },
      // Callback function to handle error  
      error: function(xhr, status, error) { 
        console.error(xhr.responseText);  
      }
    });
  });
});
