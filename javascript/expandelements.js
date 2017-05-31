//EXPAND START


//Hides element with respective assigned animation
function hideElement(expchild) {
	var speed = 150;
expchild.filter(".slide").slideUp(speed);
expchild.filter(".pop").fadeOut(speed);
expchild.filter(".noanimation").hide();
expchild.filter(":not('.slide'), :not('.pop'), :not('.noanimation')").slideUp(speed);
}

//Shows element with respective assigned animation
function showElement(expchild) {
	var speed = 150;
expchild.filter(".slide").slideDown(speed);
expchild.filter(".pop").fadeIn(speed);
expchild.filter(".noanimation").show();
expchild.filter(":not('.slide'), :not('.pop'), :not('.noanimation')").slideDown(speed);
}

//Calls to hide selected element (along with "backdrop" if any)
function expHide(expchild) {
var backdrop = expchild.siblings(".backdrop");
hideElement(expchild);
if (backdrop.length) {
  hideElement(backdrop);
}
expchild.parents(".expandparent").find(".foldedin").show();
expchild.parents(".expandparent").find(".foldedout").hide();
}

//Calls to show selected element (along with "backdrop" if any)
function expShow(expchild) {
var backdrop = expchild.siblings(".backdrop");
showElement(expchild);
if (backdrop.length) {
  showElement(backdrop);
}
expchild.parents(".expandparent").find(".foldedin").hide();
expchild.parents(".expandparent").find(".foldedout").show();
}


//Event is fired on clicking an element inside "expandparent" (which is not "expandchild"),
//this would be any button you place inside "expandparent"
jQuery(document).on("click", ".expandparent .expandbtn", function() {
jthis = jQuery(this);
var expchild = jthis.closest(".expandparent").find(".expandchild").first();
if (expchild.length && expchild.is(":visible")) {
  expHide(expchild);
} else if (expchild.length) {
  expShow(expchild);
}
});

//Hides all "expandchildren" outside of your point of click
jQuery(document).on("click", function(event) {
//Define closest "expandchild" of your point of click
clickedtarget = jQuery(event.target).parents('.expandparent').find(".expandchild");
//Calls to hide all "expandchilren" (which are not set as "individual") except for the one corresponding to your point of click
expHide(jQuery(".expandchild:not('.independent')").not(clickedtarget));
});

//Hides expandchild on clicking "closebtn" or "backdrop"
jQuery(document).on("click", ".expandchild .closebtn, .expandparent .expandchild.backdrop, .expandchild.closeonclick", function() {
expHide(jQuery(this).parents(".expandparent").find(".expandchild"));
});

//EXPAND END
