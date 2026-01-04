// ------------------ logo

$(window).on("load resize scroll",function(){
	let vh = $(window).height();
	let scr = $(window).scrollTop();
	if ((vh/2) < scr) {
		$("body").addClass("js-logo_out");
	} else {
		$("body").removeClass("js-logo_out");
	}
});
