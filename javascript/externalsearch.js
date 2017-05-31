jQuery(document).ready(function() {

    var obj = {
    mode: "EXTERNALSEARCH",
    q: searchterm,
    };

console.log("performexternalsearch");

  var returned = postAjaxPhp(obj).done(function(result) {
    //jQuery("#externalhits").html(result);

    jQuery('#searchresults > tbody:last-child').append(result);
  });
});
