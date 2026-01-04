// ------------------ course

$('[data-fancybox]').fancybox({
	buttons: [
		"slideShow",
		"fullScreen",
		"thumbs",
		"close"
	],
	transitionEffect : "zoom-in-out",
	transitionDuration : 1000
});
$('.anc a').on("click",function(){
	if ($(this).hasClass("active")) {
		return false;
	}
	let link = $(this).attr("href");
	$('.anc a, section[role="tabpanel"]').removeClass("active");
	$('.anc a').parent("li").attr('aria-selected', 'false');
	$('.anc a[href="' + link + '"]').addClass("active").parent("li").attr('aria-selected', 'true');
	$('section[role="tabpanel"]').attr('aria-hidden', 'true');
	$(link).addClass("active").attr('aria-hidden', 'false');
	let pos = $(".anc").offset().top;
	$("html, body").animate({scrollTop:(pos + 250)}, 500, "swing");
	return false;
});