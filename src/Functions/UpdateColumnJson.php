<?php
/**
 * NOTE: コラムAPIの取得・JSON更新
 * コラムAPIを叩いてデータを取得する関数
 * MEMO: コラムは頻繁に更新されないため、取得したデータをJSONファイルとして保存・キャッシュする...という想定で練習。
 *
 * @return array レスポンス配列。成功時は'status'がtrue、失敗時はfalse。
 *
 */

function updateColumnJson() {
	// JSON保存用ディレクトリのパス（必要に応じて調整）
	$jsonDir = BASE_PATH . '/storage/cache/json/columns';

	// ディレクトリが無ければ作成
	if (!file_exists($jsonDir)) {
		mkdir($jsonDir, 0777, true);
	}

	// APIのURL
	$apiUrl = $_ENV['COLUMN_API_URL'];

	// ベーシック認証のユーザー名・パスワード
	$username = $_ENV['COLUMN_API_USER'];
	$password = $_ENV['COLUMN_API_PASS'];

	// NOTE: file_get_contents版
	// // 認証ヘッダーを作成
	// $context = stream_context_create([
	// 	'http' => [
	// 		'header' => 'Authorization: Basic ' . base64_encode("$username:$password"),
	// 		'timeout' => 10,
	// 	]
	// ]);

	// // APIからデータ取得
	// // $json = file_get_contents($apiUrl);
	// $json = file_get_contents($apiUrl, false, $context);

	// NOTE: cURL版
	$ch = curl_init($apiUrl);
	curl_setopt_array($ch, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_USERPWD => "$username:$password",
		CURLOPT_TIMEOUT => 10,
	]);

	// APIを叩く
	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	// エラーチェック
	if (curl_errno($ch)) {
		error_log(sprintf(
			'[COLUMN API ERROR] url=%s error=%s',
			$apiUrl, // APIのURL
			curl_error($ch)
		));
	}
	if ($response === false || $httpCode !== 200) {
		curl_close($ch);
		http_response_code(500);
		echo 'API取得に失敗しました'; // エラーレスポンス
		exit;
		// FIXME: WP更新フックになったらこっち
		// return [
		// 	'status' => false,
		// 	'message' => '❌ API取得に失敗しました'
		// ];
	}


	// セッションを閉じる
	curl_close($ch);

	// JSONデコード
	$data = json_decode($response, true);

	// エラーチェック
	if (!is_array($data)) {
		http_response_code(500);
		echo 'JSON取得またはデコードに失敗しました';
		exit;
	}

	// 一覧用データを構築（必要な情報だけ抜粋）
	$listData = [];

	foreach ($data as $item) {
		$id = $item['id'];

		// 個別記事ファイルとして保存（そのまま丸ごと）
		file_put_contents(
			"$jsonDir/$id.json",
			json_encode($item, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
		);

		// カテゴリ情報の抽出（必要に応じて編集）
		$categories = [];
		if (!empty($item['terms']) && is_array($item['terms'])) {
			foreach ($item['terms'] as $term) {
				$categories[] = [
				'name' => $term['name'] ?? '',
				'slug' => $term['slug'] ?? '',
				];
			}
		}

		// 一覧用の軽量データを抽出（必要に応じて編集）
		$listData[] = [
			'id'         => $item['id'],
			'name'       => $item['name'] ?? '',
			'slug'       => $item['slug'] ?? '',
			'publishedAt'=> $item['publishedAt'] ?? '',
			'excerpt'    => $item['excerpt'] ?? '',
			'thumb'      => $item['thumb'] ?? '',
			'categories' => $categories,
		];
	}

	// 一覧ファイル保存
	file_put_contents(
		BASE_PATH . '/storage/cache/json/columns.json',
		json_encode($listData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
	);

	// 成功レスポンス
	return [
		'status' => true,
		'message' => '✅ JSONファイルを保存しました'
	];
}