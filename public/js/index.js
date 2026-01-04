// ------------------ Slider
window.addEventListener('load', function () {
	sliderStart();
});
function sliderStart() {
	const slide = document.getElementById('kv');
	const slideItem = slide.querySelectorAll('.vis-box');
	const totalNum = slideItem.length - 1;
	const FadeTime = 2000;
	const IntarvalTime = 6000;
	let actNum = 0;
	let nowSlide;
	let NextSlide;
	slideItem[0].classList.add('show_', 'zoom_');
	setInterval(function () {
		if (actNum < totalNum) {
			let nowSlide = slideItem[actNum];
			let NextSlide = slideItem[++actNum];
			nowSlide.classList.remove('show_');
			NextSlide.classList.add('show_', 'zoom_');
			setTimeout(function () {
				nowSlide.classList.remove('zoom_');
			}, FadeTime);
		} else {
			let _nowSlide = slideItem[actNum];
			let _NextSlide = slideItem[actNum = 0];
			_nowSlide.classList.remove('show_');
			_NextSlide.classList.add('show_', 'zoom_');
			setTimeout(function () {
				_nowSlide.classList.remove('zoom_');
			}, FadeTime);
		};
	}, IntarvalTime);
}

// ------------------ Fade in
$(function(){
	$(".fade, .move").fadeSlideIn();
});
$.fn.fadeSlideIn = function() {
	this.each(function() {
		var obj = $(this);
		$(window).on('load scroll resize',function () {
			let ePos = obj.offset().top;
			let scr = $(window).scrollTop();
			if (obj.hasClass("move") && ($(window).width() > 767 )) {
				if (scr > ePos - ($(window).height())) {
					obj.addClass("in");
				}
			} else {
				if (scr > ePos - ($(window).height() * 0.8)) {
					obj.addClass("in");
				}
			}
		});
	});
};
