<?php
/**
 * NOTE: コラム情報の取得
 * columns.json を読み込み、必要に応じてカテゴリでフィルタする関数
 *
 * @param string|null $categorySlug カテゴリslug。nullなら全件取得。
 * @return array [ 'columns' => [], 'categoryName' => '（任意）' ]
 */
function loadColumns(?string $categorySlug = null): array {
	// MEMO: JSONデータの読み込み（columns.json）
	$columnJsonPath = BASE_PATH . '/storage/cache/json/columns.json';
	$columns = [];

	// MEMO: JSONファイルが存在しなければ空で返す
	if (!file_exists($columnJsonPath)) {
		return ['columns' => [], 'categoryName' => ''];
	}

	// MEMO: JSON読み込み＆デコード
	$json = file_get_contents($columnJsonPath);
	$allColumns = json_decode($json, true);
	if (!is_array($allColumns)) {
		return ['columns' => [], 'categoryName' => ''];
	}

	// MEMO: カテゴリ指定がなければ全件返す
	if ($categorySlug === null) {
		return ['columns' => $allColumns, 'categoryName' => ''];
	}

	// MEMO: カテゴリでフィルタ（アロー関数でスッキリ）
	$filtered = array_filter($allColumns, fn($item) =>
		isset($item['categories']) &&
		array_filter($item['categories'], fn($cat) => $cat['slug'] === $categorySlug)
	);

	// MEMO: カテゴリ名の取得（最初に見つかったやつ）
	$categoryName = array_reduce($filtered, function ($carry, $item) use ($categorySlug) {
		foreach ($item['categories'] as $cat) {
			if ($cat['slug'] === $categorySlug) {
				return $cat['name'];
			}
		}
		return $carry;
	}, '');

	return ['columns' => $filtered, 'categoryName' => $categoryName];
}
