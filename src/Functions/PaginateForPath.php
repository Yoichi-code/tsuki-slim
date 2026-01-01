<?php
/**
 * NOTE: 配列のページネーション（パス型 [/page/<page>/]）
 *
 * @param array $items ページネーション対象の配列
 * @param int $page 現在のページ番号（1始まり）
 * @param int $perPage 1ページあたりのアイテム数
 * @return array ['data' => [], 'totalPages' => int, 'currentPage' => int]
 */
function paginateForPath(array $items, int $page = 1, int $perPage = 10): array {
	$totalItems = count($items);
	$totalPages = (int) ceil($totalItems / $perPage);

	// 不正なページ番号の補正（上限超え→最終ページ、0以下→1ページ目）
	if ($page < 1) $page = 1;
	if ($page > $totalPages) $page = $totalPages;

	$offset = ($page - 1) * $perPage;
	$pagedItems = array_slice($items, $offset, $perPage);

	return [
		'data'        => $pagedItems,
		'currentPage' => $page,
		'totalPages'  => $totalPages,
	];
}
