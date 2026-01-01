<?php

namespace App\Controllers;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\SeoService;

class ColumnController
{
	private $seoService;

	public function __construct(SeoService $seoService)
	{
		$this->seoService = $seoService;
	}

	/**
	 * SECTION: /column （コラム一覧）
	 * ---------------------------------------------------------------------------
	 */
	public function index(Request $request, Response $response): Response
	{
		// NOTE: Twigインスタンス（Slim推奨の取り方）
		$view = Twig::fromRequest($request);

		// NOTE: JSONデータの読み込み（columns.json）
		$data = loadColumns();
		$columns = $data['columns'];

		// NOTE: ページネーション関数
		$page = max(1, (int)($request->getQueryParams()['page'] ?? 1));

		$pagination = paginateForQuery($columns, $page, 10);
		$pagedColumns = $pagination['data'];
		$currentPage = $pagination['currentPage'];
		$totalPages = $pagination['totalPages'];

		// MEMO: SEO情報の生成
		$seo = $this->seoService->makeSeo($request, [
			'title'       => 'コラム一覧',
			'description' => 'コラムの一覧ページです',
			'path'        => '/column',
			'ogImage'     => 'https://example.com/assets/ogp-column.jpg',
			'structuredData' => [
				'@type' => 'Article',
			],
		]);

		// MEMO: コラム一覧ページのレンダリング
		return $view->render($response, 'column_list.twig', [
			'seo'         => $seo,
			'columns'     => $pagedColumns,
			'currentPage' => $currentPage,
			'totalPages'  => $totalPages,
		]);
	}


	/**
	 * SECTION: /column/category/{slug} （カテゴリー別コラム一覧）
	 * ---------------------------------------------------------------------------
	 */
	public function category(Request $request, Response $response, string $slug): Response
	{
		// NOTE: Twigインスタンス（Slim推奨の取り方）
		$view = Twig::fromRequest($request);

		// カテゴリslugを受け取る（ルートで {slug} にしている前提）
		$categorySlug = $slug;  // $slugはルーティングから渡ってきた値

		// NOTE: JSONデータの読み込み（columns.json）
		$data = loadColumns($slug);
		$columns = $data['columns'];
		$categoryName = $data['categoryName'];

		// NOTE: ページネーション関数
		// クエリパラメータから現在のページを取得
		$page = max(1, (int)($request->getQueryParams()['page'] ?? 1));
		// ページネーション処理
		$pagination = paginateForQuery($columns, $page, 10);
		$pagedColumns = $pagination['data'];
		$currentPage = $pagination['currentPage'];
		$totalPages = $pagination['totalPages'];

		// NOTE: カテゴリ名が見つからなかったら404にする
		if ($categoryName === '') {
			return $view->render($response->withStatus(404), '404.twig', [
				'message' => 'カテゴリーが見つかりません。',
			]);
		}

		// MEMO: SEO情報の生成
		$seo = $this->seoService->makeSeo($request, [
			'title'       => 'コラム一覧 - カテゴリー: ' . $slug,
			'description' => 'カテゴリー ' . $slug . ' のコラム一覧ページです',
			'path'        => '/column/category/' . $slug,
			'ogImage'     => 'https://example.com/assets/ogp-column.jpg',
		]);

		// MEMO: カテゴリ別コラム一覧ページのレンダリング
		return $view->render($response, 'column_category.twig', [
			'seo'          => $seo,
			'slug'         => $slug,
			'columns'      => $pagedColumns,
			'currentPage'  => $currentPage,
			'totalPages'   => $totalPages,
			'categoryName' => $categoryName,
		]);
	}


	/**
	 * SECTION: /column/{year}[/{month}] （コラムアーカイブページ - 年・月別）
	 * ---------------------------------------------------------------------------
	 * NOTE: このルートは "column/2025" や "column/2025/10" にしかマッチしない）
	 * TODO: FIXME: 年月でフィルタリングするロジックは未実装
	 * ---------------------------------------------------------------------------
	 */
	public function archive(Request $request, Response $response, string $year, ?string $month = null): Response
	{
		// NOTE: Twigインスタンス（Slim推奨の取り方）
		$view = Twig::fromRequest($request);

		// NOTE: 年月のデフォルト値設定
		$year = $year ?? 'latest';
		$month = $month ?? null;

		// MEMO: SEO情報の生成
		$seo = $this->seoService->makeSeo($request, [
			'title'       => 'コラムアーカイブ一覧',
			'description' => 'コラムアーカイブページの説明文',
			'path'        => '/column' . ($year !== 'latest' ? "/$year" : '') . ($month ? "/$month" : ''),
			'ogImage'     => 'https://example.com/assets/ogp-column.jpg',
		]);

		// MEMO: コラムアーカイブページのレンダリング
		return $view->render($response, 'column_archive.twig', [
			'seo'   => $seo,
			'message' => "Column for year: $year" . ($month ? ", month: $month" : ""),
		]);
	}


	/**
	 * SECTION: /column/detail/{id} （コラム個別ページ）
	 * ---------------------------------------------------------------------------
	 */
	public function detail(Request $request, Response $response, string $id): Response
	{
		// NOTE: Twigインスタンス（Slim推奨の取り方）
		$view = Twig::fromRequest($request);

		// NOTE: 記事データの読み込み
		$articlePath = BASE_PATH . "/storage/cache/json/columns/{$id}.json";
		$article = null;
		if (file_exists($articlePath)) {
			$json = file_get_contents($articlePath);
			$article = json_decode($json, true);
		}

		// MEMO: SEO情報の生成（仮にタイトルがある場合のみ）
		$seo = $this->seoService->makeSeo($request, [
			'title' => $article['name'] ?? 'コラム記事',
			'description' => isset($article['excerpt']) ? strip_tags($article['excerpt']) : '',
			'path' => "/column/detail/{$id}",
			'ogImage' => $article['thumb'] ?? '',
			'structuredData' => [
				'@context' => 'https://schema.org',
				'@type' => 'Article',
				'name' => $article['name'] ?? 'コラム記事',
				'headline' => $article['name'] ?? 'コラム記事',
				'description' => isset($article['excerpt']) ? strip_tags($article['excerpt']) : '',
				'url' => 'https://example.com/column/detail/' . ($article['id'] ?? ''),
				'datePublished' => $article['publishedAt'] ?? '2025-10-01',
				'dateModified' => $article['modifiedAt'] ?? $article['publishedAt'] ?? '2025-10-01',
				'author' => [
					'@type' => 'Organization',
					'name' => 'サイト名',
				],
				'image' => [
					$article['thumb'] ?? 'https://example.com/assets/ogp-column.jpg'
				],
			],
		]);

		// MEMO: コラム個別ページのレンダリング
		return $view->render($response, 'column_single.twig', [
			'article' => $article,
			'seo' => $seo,
		]);
	}
}
