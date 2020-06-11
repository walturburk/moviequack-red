function hasTouch() {
  return 'ontouchstart' in document.documentElement
         || navigator.maxTouchPoints > 0
         || navigator.msMaxTouchPoints > 0;
}


//EXPAND START
function toggleExpand(elem) {
  if (elem.is(":visible")) {
    elem.hide();
  } else {
    elem.show();
  }
}

function getGenreNames(movie, callback) {
  obj = {
    mode: "GETGENRES",
    movie: movie
  };
  postAjaxPhp(obj).done(function(result) {
    callback(result);
  });
}

jQuery(document).on("click", function(e) {
  var par = jQuery(e.target).parents(".popup");
  jQuery(".popup").not(e.target).not(par).hide();
});

jQuery(document).on("click", "[data-expand]", function(e) {
  var expand = jQuery(this).attr("data-expand");
  toggleExpand(jQuery("."+expand));
  e.stopPropagation();
});


//EXPAND END


jQuery(document).on("click", ".votestar", function() {


  var movieratingdiv = jQuery(this).parents("#movierating");
  var starnr = jQuery(this).attr("data-starnr");
  var movie = jQuery(this).attr("data-movie");
  var allstars = movieratingdiv.find(".votestar");





    if (jQuery(this).hasClass("actualvote")) {
      movieratingdiv.find(".votestar").removeClass("actualvote");
      rate = "null";

      var myrating = movieratingdiv.find(".notrated");
      allstars.addClass("notrated");

    } else {

      rate = Number(starnr);
      var starclass = ".star"+rate;
      rate = rate*2;

      var myrating = movieratingdiv.find(starclass);

      movieratingdiv.find(".votestar").removeClass("actualvote");
      jQuery(this).addClass("actualvote");

      allstars.removeClass("notrated");
    }


  var obj = {
      mode: "RATEMOVIE",
	    movie: movie,
      q: rate
    };

    var returned = postAjaxPhp(obj).done(function(result) {
      //movieratingdiv.html(result);
      allstars.removeClass("myrating").removeClass("myratingdark");
      movieratingdiv.find(".votestar:not(.notrated)").addClass("myratingdark");
      myrating.addClass("myrating").removeClass("myratingdark");
    });
});


jQuery(document).on("click", ".gotologin", function() {
	window.location.href = "/login";
});

jQuery(document).on("click", ".newtagbtn", function() {
	jQuery("form#addtag input#tag").focus();
});

jQuery(document).on("click", ".replybutton", function() {
	var expand = jQuery(this).attr("data-expand");
	jQuery("."+expand).find("input#message").focus();
	console.log(expand);
});

function postAjaxPhp(message, page, callback = function(x) {}) {

	page = typeof page !== 'undefined' ? page : "/ajax.php";

	return $.ajax({
    url: page,
    type: "POST",
    data: message,
    success: function(result){
      callback(result);
    },
    error: function(){
        console.log('error');
    }
});

}

////////////////////////
//DOCUMENT READY START//
////////////////////////
jQuery(document).ready(function() {

  if (hasTouch()) { // remove all the :hover stylesheets
    try { // prevent exception on browsers not supporting DOM styleSheets properly
        for (var si in document.styleSheets) {
            var styleSheet = document.styleSheets[si];
            if (!styleSheet.rules) continue;
  
            for (var ri = styleSheet.rules.length - 1; ri >= 0; ri--) {
                if (!styleSheet.rules[ri].selectorText) continue;
  
                if (styleSheet.rules[ri].selectorText.match(':hover')) {
                    styleSheet.deleteRule(ri);
                }
            }
        }
    } catch (ex) {}
  }

var bestPictures = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace("title"),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  remote: {
    url: "/searchmovie.php?q=%QUERY",
    wildcard: "%QUERY"
  }
});


jQuery('.searchfield.typeahead').typeahead(null, {
  minLength: 0,
  name: "searchresults",
  display: "originaltitle",
  source: bestPictures,
	templates: {
    empty: [
      ''
    ].join('\n'),
		suggestion: function(data) {

    return '<div><a href="/movie/'+data.id+'"><strong>' + data.originaltitle + '</strong> (' + data.year + ')</a></div>';
	}
},
});

jQuery(".searchfield.typeahead").bind("typeahead:selected", function(obj, data, name) {
  window.location.href = "/movie/"+data.id+"";
});


var tags = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace("tag"),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  remote: {
    url: "/typeaheadtags.php?q=%QUERY",
    wildcard: "%QUERY"
  }
});


jQuery('form#addtag input#tag').typeahead(null, {
  name: "tag",
  display: "tag",
  source: tags,
	templates: {
    empty: [
      ''
    ].join('\n'),
		suggestion: function(data) {
console.log(data);
    return '<div>'+data.tag+'</div>';
	}
},
});


var users = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace("username"),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  remote: {
    url: "/usersearch.php?q=%QUERY",
    wildcard: "%QUERY"
  }
});


jQuery('form#usersearch input#usersearchfield').typeahead(null, {
  name: "username",
  display: "username",
  source: users,
	templates: {
    empty: [
      ''
    ].join('\n'),
		suggestion: function(data) {
console.log(data);
    return '<div>'+data.username+'</div>';
	}
},
});

jQuery(".usersearchfield").bind("typeahead:selected", function(obj, data, name) {
  jQuery("form#usersearch .submit").submit();
});

jQuery(document).on("click", "form#usersearch .submit", function(e) {
  e.preventDefault();
  var form = jQuery(this).parents("form#usersearch");
  var movie = form.find("input#movieid").val();
  var user = form.find("input#usersearchfield").val();
  obj = {
    mode: "ADDUSERTAG",
    movie: movie,
    q: user
  };
  postAjaxPhp(obj).done(function(result) {
    form.find("input#usersearchfield").val("");
    jQuery("span#tagscontent").html(result);
  });
});

/*jQuery(document).on("click", ".quack .msgpart", function() {
  var the = jQuery(this).parents(".quack");
  if (the.hasClass("quackslim")) {
    the.removeClass("quackslim");
  } else {
    the.addClass("quackslim");
  }
});*/

jQuery(document).on("click", ".postmessage .submit", function(e) {

		e.preventDefault();

    var forme = jQuery(this).parents(".postmessage");
		var emoji = jQuery(".selectedemoji").attr("data-emoji");
		var message = forme.find("#message").val();
		var movie = forme.find("#movieid").val();
    var replyto = forme.attr("data-postid");
    if (forme.hasClass("smileymessage")) {
      var smileymessage = true;
    }

    if ((emoji == ":bust_in_silhouette:" || emoji == "") && smileymessage) {

      showemojiselector();

    } else {


		forme.find("#message").val("");
    console.log("emoji");




		var obj = {
			mode: "POSTMESSAGE",
			emoji: emoji,
			q: message,
			movie: movie,
      replyto: replyto
			};

		var returned = postAjaxPhp(obj).done(function(result) {

      if (replyto) {
        obj = {
  			mode: "PRINTREPLIES",
  			formsg: replyto
  			};
  			postAjaxPhp(obj).done(function(result) {
  				forme.parents(".replyzone").find(".replies").html(result);
  			});
      } else {
        console.log(returned);
  			obj = {
  			mode: "PRINTMESSAGES",
  			movie: movie
  			};
  			postAjaxPhp(obj).done(function(result) {
  				jQuery(".movieposts").html(result);
  			});

      }

		});

    jQuery("#messageid").html(returned);
  }


	});


	jQuery(document).on("click", "form#addtag .submit", function(e) {
		e.preventDefault();
		var form = jQuery(this).parents("form#addtag");
		var movie = form.find("input#movieid").val();
		var tag = form.find("input#tag").val();
		obj = {
			mode: "ADDTAG",
			movie: movie,
			q: tag
    };
    console.log(obj);
		postAjaxPhp(obj).done(function(result) {
			form.find("input#tag").val("");
			jQuery("span#tagscontent").html(result);
		});
  });
  
  

	jQuery(document).on("click", ".tag", function(e) {
		var movie = jQuery(this).attr("data-movie");
		var tag = jQuery(this).attr("data-tag");
		var active = jQuery(this).hasClass("activebtn");

		if (active) {
			var mode = "REMOVETAG";
			jQuery("[data-tag='"+tag+"'][data-movie='"+movie+"']").removeClass("activebtn");
		} else {
			var mode = "ADDTAG";
			jQuery("[data-tag='"+tag+"'][data-movie='"+movie+"']").addClass("activebtn");
		}
		obj = {
			mode: mode,
			movie: movie,
			q: tag
		};
		postAjaxPhp(obj).done(function(result) {
			jQuery("span#tagscontent").html(result);
		});
	});

	jQuery(document).on("click", ".voteparent .votebtn", function() {

    var voteparent = jQuery(this).parents(".voteparent");
		var votedisplay = jQuery(this).find(".votedisplay");
		var post = voteparent.attr("data-postid");
    var upvotebtn = voteparent.find(".votebtn.upvote");
    var downvotebtn = voteparent.find(".votebtn.downvote");
    var voteamount = Number(votedisplay.text());
    var isupvote = jQuery(this).hasClass("upvote");
    var upvoteactive = upvotebtn.hasClass("activebtn");
    var downvoteactive = downvotebtn.hasClass("activebtn");

		if (jQuery(this).hasClass("upvote")) {
			upvote = 1;
			downvote = 0;
		} else {
			upvote = 0;
			downvote = 1;
		}

		var obj = {
			mode: "VOTE",
			post: post,
			upvote: upvote,
			downvote: downvote
			};

      if (isupvote == true) {
        if (downvoteactive) {
         // votedisplay.text(voteamount+2);
        } else {
          //votedisplay.text(voteamount+1);
        }
        downvotebtn.removeClass("activebtn");
        if (upvoteactive) {
          upvotebtn.removeClass("activebtn");
        } else {
          upvotebtn.addClass("activebtn");
        }


      } else {
        if (upvoteactive) {
          //votedisplay.text(voteamount-2);
        } else {
          //votedisplay.text(voteamount-1);
        }
        upvotebtn.removeClass("activebtn");
        if (downvoteactive) {
          downvotebtn.removeClass("activebtn");
        } else {
          downvotebtn.addClass("activebtn");
        }

      }

    var returned = postAjaxPhp(obj).done(function(result) {

		});



	});




  jQuery(document).on("click", ".followbtn", function() {

		var follows = jQuery(this).attr("data-followedid");
    var isactive = jQuery(this).hasClass("activebtn");
    var allfollowbuttons = jQuery(".followbtn[data-followedid='"+follows+"']");

		var obj = {
			mode: "FOLLOW",
			follows: follows
			};

      if (isactive) {
        allfollowbuttons.removeClass("activebtn");
      } else {
        allfollowbuttons.addClass("activebtn");
      }
		var returned = postAjaxPhp(obj).done(function(result) {
      console.log(result);
		});

	});





  jQuery(document).on("click", ".removepost", function() {
    var addremparent = jQuery(this).parents(".addremparent");
    var item = jQuery(this).attr("data-post");
    var obj = {
			mode: "REMOVEPOST",
      q: item
			};

		var returned = postAjaxPhp(obj).done(function(result) {
      addremparent.hide();
    });
  });







});

//////////////////////
//DOCUMENT READY END//
//////////////////////

$(function() {
  var sortableitems = $("#listitems tbody, ul.sortablelist");
  $(sortableitems).sortable({
    handle: ".handle",
    stop: function () {
      var listid = jQuery("select.selectedlist option:selected").val();
      var listOrderData = $(sortableitems).sortable('toArray');

      var obj = {
  			mode: "SORTLIST",
  			listid: listid,
			listorder: listOrderData
  		};
		console.log(listid+" "+listOrderData);

      	var returned = postAjaxPhp(obj).done(function(result) {
          console.log(result);
        });

    }
  });

});
