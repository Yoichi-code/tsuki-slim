<?php
/**
 * NOTE: 配列のページネーション（クエリ型 [/?page=<page>]）
 *
 * @param array $items ページネーション対象の配列
 * @param int $page 現在のページ番号（1始まり）
 * @param int $perPage 1ページあたりのアイテム数
 * @return array ['data' => [], 'totalPages' => int, 'currentPage' => int]
 */
function paginateForQuery(array $items, int $page, int $perPage = 10): array {
	$totalItems = count($items);				// 総アイテム数を取得
	$totalPages = ceil($totalItems / $perPage);	// 総ページ数を計算
	$offset = ($page - 1) * $perPage;			// 表示する開始インデックスを計算
	return [
		'data'        => array_slice($items, $offset, $perPage),
		'totalPages'  => $totalPages,
		'currentPage' => $page,
	];
}
