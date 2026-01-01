<?php
namespace App\Services;
use Psr\Http\Message\ServerRequestInterface as Request;

class SeoService
{
	private string $baseUrl;
	private array $defaults;

	// MEMO: コンストラクタで基本設定を確定
	public function __construct()
	{
		$this->baseUrl = 'https://example.com';	// ← あとで .env などに逃がす
		$this->defaults = [
			'siteName'    => 'サイト名',
			'title'       => '',
			'description' => '基本の説明文',
			'path'        => null,				// 明示されなければ現在リクエストから拾う
			'ogImage'     => $this->baseUrl . '/assets/ogp-default.jpg',
			'type'        => 'website',
		];
	}

	public function makeSeo(Request $req, array $overrides = []): array
	{
		$seo = array_merge($this->defaults, $overrides);

		// MEMO: 現在パスを既定に（/foo/bar など）
		$path = $seo['path'] ?? $req->getUri()->getPath();

		// MEMO: 正規化：先頭は必ず1個の /、末尾スラッシュは除去（ただし、トップは空文字として扱う）
		$norm = trim((string)$path);
		$norm = '/' . ltrim($norm, '/');	// 先頭 1 個だけ確保

		if ($norm !== '/') {
			$norm = rtrim($norm, '/');		// トップ以外は末尾 / を除去
		} else {
			$norm = '';						// トップは空（= baseUrl 単体）
		}

		// MEMO: canonical と og:url を確定
		$base = rtrim($this->baseUrl, '/');
		$seo['canonical'] = $norm === '' ? $base : ($base . $norm);
		$seo['ogUrl']     = $seo['canonical'];

		// MEMO: タイトル整形（空ならサイト名のみ／入ってたら「タイトル | サイト名」）
		if (strlen(trim($seo['title'])) === 0) {
			$seo['title'] = $this->defaults['siteName'];
		} elseif (!str_contains($seo['title'], $this->defaults['siteName'])) {
			$seo['title'] .= ' | ' . $this->defaults['siteName'];
		}

		// MEMO: 👇 structuredData が渡されていなかったら、デフォルトで WebPage を出す
		// if (!isset($seo['structuredData'])) {
		// 	$seo['structuredData'] = [
		// 		'@context' => 'https://schema.org',
		// 		'@type'    => 'WebPage',
		// 		'name'     => $seo['title'], // 整形後のタイトル
		// 	];
		// }
		// MEMO: --- structuredData のデフォルト設定と上書き処理 ---
		$defaultStructuredData = [
			'@context' => 'https://schema.org',
			'@type'    => 'WebPage',
			'name'     => $seo['title'], // 整形後タイトル
		];

		// MEMO: structuredDataが渡されていたらマージ（上書き＆追加）
		// var_dump($overrides); // ★デバッグ用
		if (isset($overrides['structuredData']) && is_array($overrides['structuredData'])) {
			$seo['structuredData'] = array_replace_recursive(
				$defaultStructuredData,
				$overrides['structuredData']
			);
		} else {
			$seo['structuredData'] = $defaultStructuredData;
		}

		return $seo;
	}
}
