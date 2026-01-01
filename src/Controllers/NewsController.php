<?php

namespace App\Controllers;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\SeoService;

class NewsController
{
	private $seoService;

	public function __construct(SeoService $seoService)
	{
		$this->seoService = $seoService;
	}

	/**
	 * SECTION: /news （ニュース一覧）
	 * ---------------------------------------------------------------------------
	 */
	public function index(Request $request, Response $response, string $page = '1'): Response
	{
		// NOTE: Twigインスタンス（Slim推奨の取り方）
		$view = Twig::fromRequest($request);

		// NOTE: データ関係
		// ニュース記事の取得（オジサンの関数はそのまま使う）
		$items = fetchApiData();
		// ページ番号（/news?page=2 対応用）
		$page = max(1, (int)($request->getQueryParams()['page'] ?? 1));
		// ページネーション
		$pagination = paginateForPath($items, (int)$page);

		// MEMO: SEO情報の生成
		$seo = $this->seoService->makeSeo($request, [
			'title'       => 'ニュース一覧',
			'description' => 'ニュース一覧ページの説明文',
			'ogImage'     => 'https://example.com/assets/ogp-news.jpg',
		]);

		// MEMO: ページのレンダリング
		return $view->render($response, 'news_list.twig', [
			'seo'         => $seo,
			'news'        => is_array($items) ? $items : [],
			'currentPage' => $pagination['currentPage'],
			'totalPages'  => $pagination['totalPages'],
		]);
	}


	/**
	 * SECTION: /news/page/{page} （ニュース一覧ページ - ページネーション）
	 * ---------------------------------------------------------------------------
	 */
	public function page(Request $request, Response $response, string $page = '1'): Response
	{
		// NOTE: Twigインスタンス（Slim推奨の取り方）
		$view = Twig::fromRequest($request);

		// NOTE: データ関係
		// ニュース記事の取得（オジサンの関数はそのまま使う）
		$items = fetchApiData();
		// ページネーション
		$pagination = paginateForPath($items, (int)$page);

		// MEMO: SEO情報の生成
		$seo = $this->seoService->makeSeo($request, [
			'title'       => "ニュース一覧（{$page}ページ目）",
			'description' => "ニュース一覧のページ {$page}",
			'ogImage'     => 'https://example.com/assets/ogp-news.jpg',
		]);

		// MEMO: ページのレンダリング
		return $view->render($response, 'news_list.twig', [
			'seo'         => $seo,
			'page'        => $page,
			'news'        => $pagination['data'],
			'currentPage' => $pagination['currentPage'],
			'totalPages'  => $pagination['totalPages'],
		]);
	}


	/**
	 * SECTION: /news/{slug} （ニュース個別ページ）
	 * ---------------------------------------------------------------------------
	 */
	public function detail(Request $request, Response $response, ?string $slug = null): Response
	{
		// NOTE: Twigインスタンス（Slim推奨の取り方）
		$view = Twig::fromRequest($request);

		// NOTE: データ関係
		// キャッシュ機能付きでニュース記事を取得する例 TEST: JSONファイルを保存する想定：キャッシュ期限付き
		$cacheFile = BASE_PATH . '/storage/cache/news/' . $slug . '.json';
		$cacheExpire = 3600 * 24;	// 24時間（秒）
		// $cacheExpire = 60; 		// TEST: 1分（秒）← 動作確認用に短くしているだけ。本番はもっと長くしてOK。

		// flush_cache=1 がクエリについてたら削除（WordPress側からの命令）
		if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheExpire) {
			// ✅ キャッシュが新しい → そのまま読み込む
			$json = file_get_contents($cacheFile);
			$article = json_decode($json, true);
		} else {
			// ⏳ キャッシュが古い or 存在しない → 再取得して保存
			$items = fetchApiData(); // ← 全記事取得
			$article = null;
			// スラッグに一致する記事を探す
			if ($slug && is_array($items)) {
				foreach ($items as $it) {
					if (isset($it['slug']) && $it['slug'] === $slug) {
						$article = $it;
						break;
					}
				}
			}
			// キャッシュ保存
			if ($article !== null) {
				file_put_contents($cacheFile, json_encode($article, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
			}
		}

		// MEMO: 記事が見つからなかった場合の404処理
		if ($article === null) {
			return $view->render($response->withStatus(404), 'news_single.twig', [
				'title'   => '記事が見つかりません',
				'article' => null,
			]);
		}

		// MEMO: SEO情報の生成
		$seo = $this->seoService->makeSeo($request, [
			'title'       => $article['name'] ?? 'ニュース詳細',
			'description' => $article['description'] ?? 'ニュース詳細ページの説明文',
			'path'        => '/news/' . $slug,
			'ogImage'     => $article['ogImage'] ?? 'https://example.com/assets/ogp-news.jpg',
			'structuredData' => [
				'@context' => 'https://schema.org',
				'@type' => 'NewsArticle',
				'name' => $article['name'] ?? 'ニュース詳細',
				'headline' => $article['name'] ?? 'ニュース詳細',
				'description' => isset($article['description']) ? strip_tags($article['description']) : '',
				'url' => 'https://example.com/news/' . $slug,
				'datePublished' => $article['publishedAt'] ?? '2025-10-01',
				'dateModified' => $article['modifiedAt'] ?? $article['publishedAt'] ?? '2025-10-01',
				'author' => [
					'@type' => 'Organization',
					'name' => 'サイト名',
				],
				'image' => [
					$article['ogImage'] ?? 'https://example.com/assets/ogp-news.jpg'
				],
			],
		]);

		// MEMO: ページのレンダリング
		return $view->render($response, 'news_single.twig', [
			'seo'     => $seo,
			'article' => $article,
		]);
	}
}
