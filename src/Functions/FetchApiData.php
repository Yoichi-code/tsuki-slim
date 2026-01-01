<?php
/**
 * NOTE: ニュースAPIの取得
 * ニュースAPIを叩いてデータを取得する関数
 * MEMO: ニュースは毎回最新のものを取得したいため、キャッシュは使わない..という想定で練習。
 *
 * @return array ニュースデータの配列。失敗時は空配列。
 *
 */

// NOTE: file_get_contents版
// function fetchApiData(): array {
// 	// 1. APIのURL
// 	// $url = "https://wp-admin.katagirijuku.jp:8443/wp-json/custom/v1/news-next";
// 	// TEST: テスト用（失敗するURL）
// 	// $url = "https://example-does-not-exist.com/api";

// 	// TEST: // ベーシック認証付き その１
// 	// $url = "http://caname-tester:Z3Yc79wQf3JcKgE@dev-1.katagirijuku.jp/wp-json/custom/v1/news-next";

// 	// TEST: // ベーシック認証付き その２
// 	// 1. APIのURL
// 	$url = "https://dev-1.katagirijuku.jp/wp-json/custom/v1/news-next";
// 	// 2. ベーシック認証のユーザー名・パスワード
// 	$username = "caname-tester";
// 	$password = "Z3Yc79wQf3JcKgE";
// 	// 3. 認証ヘッダーを作成
// 	$options = [
// 		"http" => [
// 			"header" => "Authorization: Basic " . base64_encode("$username:$password")
// 		]
// 	];
// 	// 4. コンテキストを作成
// 	$context = stream_context_create($options);
// 	// 5. API叩く（失敗したら空にする）
// 	$json = @file_get_contents($url, false, $context);

// 	// 6. API叩く（失敗したら空にする）
// 	// $json = @file_get_contents($url);
// 	if ($json === false) {
// 		$news = []; // 失敗した場合は空配列
// 	} else {
// 		$news = json_decode($json, true); // 連想配列に
// 		if (!is_array($news)) {
// 			$news = [];
// 		}
// 	}
// 	return $news;
// }

// NOTE: cURL版
function fetchApiData(): array {
	// 1. APIのURL
	$url = $_ENV['NEWS_API_URL'];

	// 2. ベーシック認証のユーザー名・パスワード
	$username = $_ENV['NEWS_API_USER'];
	$password = $_ENV['NEWS_API_PASS'];

	// 3. cURLセッションを初期化
	$ch = curl_init($url);

	// 4. オプションを設定
	curl_setopt_array($ch, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_USERPWD => "$username:$password",
		CURLOPT_TIMEOUT => 10,
	]);

	// 5. APIを叩く
	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	// 6. エラーチェック
	if (curl_errno($ch)) {
		error_log('cURL error: ' . curl_error($ch));
		// FIXME: var_dump('cURL error: ' . curl_error($ch)); // デバッグ用
	}
	if ($response === false || $httpCode !== 200) {
		curl_close($ch);
		return [];
	}

	// 7. セッションを閉じる
	curl_close($ch);

	// 8. レスポンスをデコード
	$data = json_decode($response, true);
	return is_array($data) ? $data : []; // デコード失敗時も空配列
}