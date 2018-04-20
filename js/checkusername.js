jQuery(document).on("change", "#username", function() {
  var username = jQuery(this).val();
  console.log(username);
  var obj = {
    mode: "GETUSERNAME",
    q: username
  };

  var returned = postAjaxPhp(obj).done(function(result) {
    if (result == username) {
      jQuery("#usernamewarning").show();
    } else {
      jQuery("#usernamewarning").hide();
    }
  });

});
