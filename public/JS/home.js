$(document).ready(function () {
  let count = 2;
  let initial = 0;
  const $nav = $(".nav"),
    $searchIcon = $("#searchIcon"),
    $navOpenBtn = $(".navOpenBtn"),
    $navCloseBtn = $(".navCloseBtn");

  function Loader() {
    $.ajax({
      // PHP file handling the request
      url: "/load",
      // Request method
      type: "POST",
      // Data to be sent to the server
      data: {
        offset: 0,
      },
      // Callback function to handle successful AJAX response
      success: function (response) {
        if ($.trim(response.html)) {
          $("#default").append(response.html);
          // count = count + 2;
        } else {
          // $("#load-message").html("All content loaded. Nothing to Load !!!");
        }
      },
      // Callback function to handle error
      error: function (xhr, status, error) {
        console.error(xhr.responseText);
      },
    });
    initial = 1;
  }

  $searchIcon.click(function () {
    $nav.toggleClass("openSearch");
    $nav.removeClass("openNav");
    if ($nav.hasClass("openSearch")) {
      $searchIcon.removeClass("uil-search");
      $searchIcon.addClass("uil-times cross");
    } else {
      $searchIcon.removeClass("uil-times cross");
      $searchIcon.addClass("uil-search");
    }
  });

  $navOpenBtn.click(function () {
    $nav.addClass("openNav");
    $nav.removeClass("openSearch");
    $searchIcon.removeClass("uil-times");
    $searchIcon.addClass("uil-search");
  });

  $navCloseBtn.click(function () {
    $nav.removeClass("openNav");
  });

  $(document).on("submit", "#post-form", function(e) {
    e.preventDefault();
    let form_data = new FormData($('#post-form')[0]);
    $.ajax({
      // PHP file handling the request
      url: "/home",
      // Request method
      type: "POST",
      // Data to be sent to the server
      data: form_data,
      contentType: false,
      processData: false,
      // Callback function to handle successful AJAX response
      success: function (response) {
        if ($.trim(response.posterror)) {
          $('#post-error').append(response.posterror);
          console.log(form_data);
        }
        else{
          location.reload();
        }
      },
      // Callback function to handle error
      error: function (xhr, status, error) {
        console.error(xhr.responseText);
      },
    });
  });

  $(document).on("click", "#more", function () {
    $.ajax({
      // PHP file handling the request
      url: "/load",
      // Request method
      type: "POST",
      // Data to be sent to the server
      data: {
        offset: count,
      },
      // Callback function to handle successful AJAX response
      success: function (response) {
        if ($.trim(response.html)) {
          $("#loaded-content").append(response.html);
          count = count + 2;
        } 
        else {
          $("#load-message").html("All content loaded. Nothing to Load !!!");
        }
      },
      // Callback function to handle error
      error: function (xhr, status, error) {
        console.error(xhr.responseText);
      },
    });
  });

  if (!initial) {
    Loader();
  }

  // Like feature (Testing only).
  $(document).on("click", "#like-con", function(){
    let postid = $(this).data('post-id');
    let username = $("#user-name").val();
    let $this = $(this);
    $.ajax({
      // PHP file handling the request
      url: '/like',
      // Request method
      type: 'POST',
      // Data to be sent to the server
      data: {
        post_id: postid,
        user_name: username
      },
      dataType: 'json',
      // Callback function to handle successful AJAX response
      success: function(response) {
        if (response.status == "added"){
          $this.find('#like-count').text(response.total);
          $this.find('#thumbs').html('<i class="fa-solid fa-thumbs-up"></i>');
        }
        else {
          $this.find('#like-count').text(response.total);
          $this.find('#thumbs').html('<i class="uil uil-thumbs-up"></i>');
        }
        console.log(response.total);
        console.log(response.user_name);
        console.log(response.status);
      },
      // Callback function to handle error
      error: function(xhr, status, error) {
        console.error(xhr.responseText);
      }
    });
  });

  // $(document).on("click", "#comment-btn", function(){
  //   let postid = $(this).data('post-id');
  //   let $this = $(this).parent();
  //   $this.find('#comments-display').slideToggle(300);
  //   $.ajax({
  //     // PHP file handling the request
  //     url: 'AJAX/CommentLoad.php',
  //     // Request method
  //     type: 'POST',
  //     // Data to be sent to the server
  //     data: {
  //       'post_id': postid
  //     },
  //     // Callback function to handle successful AJAX response
  //     success: function(response) {
  //       if (response){
  //         $this.find('#comments').html(response);
  //       }
  //     },
  //     // Callback function to handle error
  //     error: function(xhr, status, error) {
  //       console.error(xhr.responseText);
  //     }
  //   });
  // });

  // $(document).on( 'submit', '#commentForm', function(event) {
  //   // Prevent the form from submitting via the browser's default method
  //   event.preventDefault();
  //   $this = $(this);
  //   // Serialize the form data
  //   let formData = {
  //     'post_id': $(this).parent().data('post-id'),
  //     'comment': $(this).find('#post_comm').val(),
  //     'user_id': $("#user-id").val()
  //   };
  //   let value = $this.parent().parent().parent().find('#comm-count');

  //   // Send an AJAX request
  //   $.ajax({
  //     type: 'POST',
  //     url: 'AJAX/CommentAdd.php', // Change 'your-server-url.php' to the URL where you want to handle the form submission
  //     data: formData,
  //     success: function(response) {
  //       // Handle the successful response here
  //       value.text(parseInt(value.text(), 10)+1);
  //       $this.parent().siblings('#comments').html(response);
  //     },
  //     error: function(xhr, status, error) {
  //       // Handle errors here
  //       console.error('Error submitting form:', error);
  //     }
  //   });
  // });
});
