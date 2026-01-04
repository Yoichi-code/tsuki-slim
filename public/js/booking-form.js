// ------------------ booking-form

$('legend, td[colspan="2"]').addClass('title-sub2');
$('#booking-reservation-fieldset').parents('#form').addClass('input').find('#action-button').prepend('<div class="caution">まだ予約決定ではありません。<br>「予約確認」ボタンを押し、次に進む確認画面でご確認のうえ、<br class="pc">「予約する」ボタンを押してご予約完了となります。</div>');
$('#booking-confirm-fieldset').parents('#form').addClass('check');
$('#booking-confirm-fieldset').next('#action-button').prepend('<button type="button" name="submitBack" onClick="history.back(); return false;">戻る</button>');

// 値をクリア
$('#booking_client-fieldset input[type="text"]').each(function() {
	$(this).removeAttr('value');
});

// フォームの確認ボタンを押したらオブジェクトに保存
$('section.input button[name="reserve_action"').on("click",function(){
	var obj = {};
	$('#booking_client-fieldset input[id^="client-"]').each(function() {
		let idName = $(this).attr("id").replace("client-","");
		//idName = idName.replace("client-","");
		obj[idName] = $(this).val();
	});
	var obj = JSON.stringify(obj);
	localStorage.setItem('Key', obj);
});

// ストレージがあったら取得
var jsObj = {};
var jsonObj = localStorage.getItem('Key');
if (jsonObj) {
	jsObj = JSON.parse(jsonObj);
}

// フォームの入力欄を加工
$('#client-name').attr({
	placeholder : '例：月森みよ子',
	value : jsObj.name
});
$('#client-furigana').attr({
	placeholder : '例：ツキモリミヨコ',
	value : jsObj.furigana
});
$('#client-email').attr({
	placeholder : 'メールアドレス',
	value : jsObj.email
});
$('#client-email2').attr({
	placeholder : '確認のため、もう一度ご記入ください',
	value : jsObj.email2
});
$('#client-address1').attr({
	placeholder : 'ご住所',
	value : jsObj.address1
});
$('#client-address2').attr({
	placeholder : 'アパート・マンション名・部屋番号',
	value : jsObj.address2
});
$('#client-tel').attr({
	placeholder : 'ハイフンなしにて記入',
	value : jsObj.tel
});
$('#booking-note').attr({
	placeholder : '例：「新規です」「体験レッスン希望」「基礎コース３回目のメープルシュガーナッツでお願いします」'
});
