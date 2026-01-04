// ------------------ responsible

document.addEventListener("DOMContentLoaded", function() {
	const ua = navigator.userAgent;
	const mql = window.matchMedia("screen and (max-width: 767px)");
	function checkBreakPoint(mql) {
		$("nav, #spNaviBtn").off();
		let wH = $(window).innerHeight();
		$(window).on("load scroll",function(){
			if ($(this).scrollTop() > 300) {
				$("body").addClass("js-nav_out");
			} else {
				$("body").removeClass("js-nav_out");
			}
		});
		if (mql.matches) {
			$("#spNaviBtn").on("click", function() {
				$("body").toggleClass("js-nav_slide");
			});
			$(window).on('orientationchange resize', function() {
				if (Math.abs(window.orientation) === 90) {
					$("body").addClass("js-mob_landscape");
				} else {
					$("body").removeClass("js-mob_landscape");
				}
			});
		} else {
			$("body").removeClass("js-nav_slide");
			$(window).on("load scroll",function(){
				if ($(this).scrollTop() > wH) {
					$("body").addClass("js-nav_in");
				} else {
					$("body").removeClass("js-nav_in");
				}
			});
			if ((ua.indexOf("iPad") > 0 || (ua.indexOf("Android") > 0 && !ua.indexOf("Mobile") > 0))) {
				let orientation = window.matchMedia("(orientation:portrait)");
				let zoomPoint = 1000;
				function orientationChange(orientation) {
					if( orientation.matches ) {
						$('meta[name="viewport"]').attr("content", "width=" + zoomPoint);
					} else {
						$('meta[name="viewport"]').attr("content", "width=device-width");
					}
				}
				orientation.addListener(orientationChange);
				orientationChange(orientation);
			}
		}
	}
	mql.addListener(checkBreakPoint);
	checkBreakPoint(mql);
});



// ------------------ link class and blank

$(function(){
	$("a[href^='http']").not('[href*="'+location.hostname+'"]').attr({
		target:"_blank",
		rel: "noopener noreferrer"
	});
});



// ------------------ form checked

$(function(){
	$("input:checkbox,input:radio").on("click", function(){ labelCheckd(); });
	labelCheckd();
	function labelCheckd() {
		$("label").removeClass("checked");
		$("input:checked").parent("label").addClass("checked");
	}
});



// ------------------ scroll

$('a[href^="#"]').not('.acd').on("click", function(){
	var toplink = "#top";
	var href= $(this).attr("href");
	var target = $(href == toplink || href == "#" || href == "" ? "html" : href);
	var position = target.offset().top;
	$("html, body").animate({scrollTop:position}, 500, "swing");
	target.focus();
	if (target.is(":focus")){
		return false;
	} else {
		target.attr('tabindex','-1');
		target.focus();
	};
	return false;
});



// ------------------ calendar

$(".calendar-wrap li").on("click",function(){
	let key, remove, add;
	let $id = $(this).parent().attr('id');
	if ($id == 'calendarTab') {
		key = 'month';
		remove = $(".calendar, #calendarTab li");
		add = "#";
	} else {
		key = 'part';
		remove = $(".calendar > div, #partTab li");
		add = ".table-";
	}
	let tab = $(this).data();
	let data = tab[key];
	remove.removeClass("active");
	$(this).addClass("active");
	$(add + data).addClass("active");
});
$("#support a").on("click",function(){
	$("body").addClass("js-support_open");
	return false;
});
$("#supportWindow").on("click",function(){
	$("body").removeClass("js-support_open");
});
$('.table-daytime caption').text('小麦パン - コース');
$('.table-nighttime caption').text('米粉パン - 1dayレッスン');
$('.table-1day caption').text('小麦パン - 1dayレッスン');
$('.input-number label[for="client-adult"]').text('');
