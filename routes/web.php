<?php

use Slim\App;
use Slim\Handlers\Strategies\RequestResponseArgs;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\SeoService;
use App\Controllers\NewsController;
use App\Controllers\ColumnController;

// NOTE: 関数群の読み込み
require_once BASE_PATH . '/src/Functions/FetchApiData.php';		// ニュースAPI取得
require_once BASE_PATH . '/src/Functions/LoadColumns.php';		// コラムデータ読み込み
require_once BASE_PATH . '/src/Functions/PaginateForQuery.php';	// クエリ用ページネーション
require_once BASE_PATH . '/src/Functions/PaginateForPath.php';	// パス用ページネーション

# MEMO:
# 勉強用なので、NEWSはその都度API叩くけど、コラムは事前にJSON保存しておいてそこから読む形にする
#

return function (App $app) {
// ============================================================
// SECTION: コンテナの取得
// ============================================================

	$container = $app->getContainer();


// ============================================================
// SECTION: DI（サービスの登録）
// ============================================================

	// NOTE: SEOサービスの登録
	$container->set(SeoService::class, function() {
		return new SeoService();
	});

	// NOTE: ニュースコントローラーの登録
    $container->set(NewsController::class, function($c) {
        return new NewsController(
            $c->get(SeoService::class)
        );
    });

	// NOTE: コラムコントローラーの登録
    $container->set(ColumnController::class, function($c) {
        return new ColumnController(
            $c->get(SeoService::class)
        );
    });


// ============================================================
// SECTION: Twig や Middleware の設定
// ============================================================

	// NOTE: Twig ミドルウェアの設定
	$twig = Twig::create(BASE_PATH . '/views', [
		'cache' => false,	// FIXME: 勉強用だからオフでOK
	]);
	$app->add(TwigMiddleware::create($app, $twig));

	// NOTE: ルーティングの設定
	$routeCollector = $app->getRouteCollector();
	$routeCollector->setDefaultInvocationStrategy(new RequestResponseArgs());

	// NOTE: ここで SeoService のインスタンスを作成しておく
	$seoService = new SeoService();


// ============================================================
// SECTION: ルーティング
// ============================================================

	// SECTION: トップページ
	// ---------------------------------------------------------------------------
	$app->get('/', function (Request $request, Response $response) use ($seoService) {
		$view = Twig::fromRequest($request);

		// MEMO: SEO情報の生成
		$seo = $seoService->makeSeo($request, [
			'ogImage'     => 'https://example.com/assets/ogp-top.jpg',
		]);

		// MEMO: API取得を関数で取得
		$news = fetchApiData();
		// var_dump($news); exit; // 動作確認用

		// MEMO: トップページのレンダリング
		return $view->render($response, 'top.twig', [
			'seo' => $seo,
			'message' => 'トップページだよ！Win 11 でやってるよ！Twig勉強中！',
			'news' => $news,
		]);
	});


	// SECTION: Twig参考用ページ（基本）
	// ---------------------------------------------------------------------------
	$app->get('/twig-sample', function (Request $request, Response $response) use ($seoService) {
		$view = Twig::fromRequest($request);

		// MEMO: SEO情報の生成
		$seo = $seoService->makeSeo($request, [
			'title'       => 'Twigサンプルページ',
			'description' => 'Twigサンプルページの説明文',
			'ogImage'     => 'https://example.com/assets/ogp-twig-sample.jpg',
		]);

		// MEMO: Twigサンプルページのレンダリング
		return $view->render($response, 'twig_sample.twig', [
			'seo' => $seo,
			'message' => 'ルーターから受け渡されたテキスト！Slim × Twig 勉強中！',
		]);
	});


	// SECTION: Twig参考用ページ（マクロ）
	// ---------------------------------------------------------------------------
	$app->get('/twig-macro', function (Request $request, Response $response) use ($seoService) {
		$view = Twig::fromRequest($request);

		// MEMO: SEO情報の生成
		$seo = $seoService->makeSeo($request, [
			'title'       => 'Twigマクロサンプルページ',
			'description' => 'Twigマクロサンプルページの説明文',
			'ogImage'     => 'https://example.com/assets/ogp-twig-sample.jpg',
		]);

		// MEMO: Twigマクロサンプルページのレンダリング
		return $view->render($response, 'twig_macro.twig', [
			'seo' => $seo,
			'message' => 'ルーターから受け渡されたテキスト！Slim × Twig 勉強中！',
		]);
	});


	// SECTION: embed参考用ページ
	// ---------------------------------------------------------------------------
	$app->get('/embed-sample', function (Request $request, Response $response) use ($seoService) {
		$view = Twig::fromRequest($request);

		// MEMO: SEO情報の生成
		$seo = $seoService->makeSeo($request, [
			'title'       => 'Embedサンプルページ',
			'description' => 'Embedサンプルページの説明文',
			'ogImage'     => 'https://example.com/assets/ogp-embed-sample.jpg',
		]);

		// MEMO: Embedサンプルページのレンダリング
		return $view->render($response, 'embed_sample.twig', [
			'seo' => $seo,
			'message' => 'ルーターから受け渡されたテキスト！Slim × Twig（今回は"embed"！） 勉強中！',
		]);
	});


	// SECTION: 会社概要ページ
	// ---------------------------------------------------------------------------
	$app->get('/about', function (Request $request, Response $response) use ($seoService) {
		$view = Twig::fromRequest($request);

		// MEMO: SEO情報の生成
		$seo = $seoService->makeSeo($request, [
			'title'       => '会社概要',
			'description' => '会社概要ページの説明文',
			// 'path'        => '/about',	// COMP: 渡さなければ現在のURLから自動で作るようにした
			'ogImage'     => 'https://example.com/assets/ogp-about.jpg',
		]);

		// MEMO: 会社概要ページのレンダリング
		return $view->render($response, 'about.twig', [
			'seo' => $seo,
			'message' => '会社概要ページです！Slim × Twig 勉強中！',
		]);
	});


	// SECTION: お問い合わせページ
	// ---------------------------------------------------------------------------
	$app->get('/contact', function (Request $request, Response $response) use ($seoService) {
		$view = Twig::fromRequest($request);

		// MEMO: SEO情報の生成
		$seo = $seoService->makeSeo($request, [
			'title'       => 'お問い合わせ',
			'description' => 'お問い合わせページの説明文',
			'ogImage'     => 'https://example.com/assets/ogp-contact.jpg',
		]);

		// MEMO: お問い合わせページのレンダリング
		return $view->render($response, 'contact.twig', [
			'seo' => $seo,
			'message' => 'お問い合わせページです！<br>Slim × Twig 勉強中！',
		]);
	});


	// SECTION: スラッグの例
	// ---------------------------------------------------------------------------
	$app->get('/hello[/{name}]', function (Request $request, Response $response, $name = null) use ($seoService) {
		$view = Twig::fromRequest($request);
		$name = $name ?? 'Guest';

		// COMP: スラッグの挨拶ページ（キャッシュ対応） TEST: HTMLファイルを保存する想定：キャッシュ削除機能付き
		$slugSafe = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);				// スラッグ安全化
		$cacheFile = BASE_PATH . "/storage/cache/pages/hello-{$slugSafe}.html";	// キャッシュファイルパス

		// NOTE: キャッシュの削除
		// ✅ flush_cache=1 がクエリについてたら削除（WordPress側からの命令）
		if ($request->getQueryParams()['flush_cache'] ?? false) {
			if (file_exists($cacheFile)) {
				unlink($cacheFile); // キャッシュ削除
				// ✒️ ログ記録（追記）
				$log = date('Y-m-d H:i:s') . " - キャッシュ削除: $cacheFile\n";
				file_put_contents(BASE_PATH . '/storage/logs/cache_log.txt', $log, FILE_APPEND);
			}
		}

		// 🔁 キャッシュファイルがあればそれを返す
		if (file_exists($cacheFile)) {
			$html = file_get_contents($cacheFile);
			$response->getBody()->write($html);
			return $response;
		}

		// MEMO: SEO情報の生成
		$seo = $seoService->makeSeo($request, [
			'title'       => $name . 'さんの挨拶ページ',
			'description' => $name . 'さんの挨拶ページの説明文',
			'path'        => '/hello/' . $slugSafe,
			'ogImage'     => 'https://example.com/assets/ogp-hello.jpg',
		]);

		// MEMO: Twigで描画 → 保存
		$html = $view->fetch('hello_single.twig', [
			'seo' => $seo,
			'message' => "Hello, $name!",
		]);
		file_put_contents($cacheFile, $html);

		$response->getBody()->write($html);
		return $response;
	});


	// SECTION: コラム関連のルーティング群
	// ---------------------------------------------------------------------------
	$app->group('/column', function (RouteCollectorProxy $group) {
		// // MEMO: コラム一覧ページ
		$group->get('', [ColumnController::class, 'index']);
		// // MEMO: カテゴリー別コラム一覧ページ
		$group->get('/category/{slug}', [ColumnController::class, 'category']);
		// // MEMO: コラムアーカイブページ（年・月別）
		$group->get('/{year:[0-9]{4}}[/{month:[0-9]{2}}]', [ColumnController::class, 'archive']);
		// // MEMO: コラム個別ページ
		$group->get('/detail/{id}', [ColumnController::class, 'detail']);
	});

	// SECTION: コラムアップデート用
	// ---------------------------------------------------------------------------
	$app->get('/update-column-json', function ($request, $response) {
		require_once BASE_PATH . '/src/Functions/UpdateColumnJson.php';

		$result = updateColumnJson(); // ← ここ！！

		$response->getBody()->write($result['message']);
		return $response;
	});

	// MEMO: コラム一覧ページ（/column）
	// $app->get('/column', function (Request $request, Response $response) use ($seoService) {
	// 	$view = Twig::fromRequest($request);

	// 	// NOTE: JSONデータの読み込み（columns.json）
	// 	$data = loadColumns();
	// 	$columns = $data['columns'];

	// 	// NOTE: ページネーション関数
	// 	// $page = max(1, (int)$request->getQueryParams()['page'] ?? 1); // FIXME: コラムトップでエラーが出る
	// 	$page = max(1, (int)($request->getQueryParams()['page'] ?? 1));

	// 	$pagination = paginateForQuery($columns, $page, 10);
	// 	$pagedColumns = $pagination['data'];
	// 	$currentPage = $pagination['currentPage'];
	// 	$totalPages = $pagination['totalPages'];

	// 	// MEMO: SEO情報の生成
	// 	$seo = $seoService->makeSeo($request, [
	// 		'title'       => 'コラム一覧',
	// 		'description' => 'コラムの一覧ページです',
	// 		'path'        => '/column',
	// 		'ogImage'     => 'https://example.com/assets/ogp-column.jpg',
	// 		'structuredData' => [
	// 			'@type' => 'Article',
	// 		],
	// 	]);

	// 	// MEMO: コラム一覧ページのレンダリング
	// 	return $view->render($response, 'column_list.twig', [
	// 		'seo'         => $seo,
	// 		'columns'     => $pagedColumns,
	// 		'currentPage' => $currentPage,
	// 		'totalPages'  => $totalPages,
	// 	]);
	// });

	// MEMO: カテゴリー別コラム一覧ページ（例: /column/category/lifestyle）
	// $app->get('/column/category/{slug}', function (Request $request, Response $response, $slug = null) use ($seoService) {
	// 	$view = Twig::fromRequest($request);

	// 	// カテゴリslugを受け取る（ルートで {slug} にしている前提）
	// 	$categorySlug = $slug;  // $slugはルーティングから渡ってきた値

	// 	// NOTE: JSONデータの読み込み（columns.json）
	// 	// $columnJsonPath = __DIR__ . '/../storage/cache/json/columns.json';
	// 	// $columns = [];

	// 	// if (file_exists($columnJsonPath)) {
	// 	// 	$json = file_get_contents($columnJsonPath);
	// 	// 	$allColumns = json_decode($json, true);
	// 	// 	// カテゴリに基づいてフィルタリング
	// 	// 	if (is_array($allColumns)) {
	// 	// 		// 特定カテゴリに属する記事だけ抽出
	// 	// 		// $columns = array_filter($allColumns, function ($item) use ($categorySlug) {
	// 	// 		// 	if (!isset($item['categories']) || !is_array($item['categories'])) {
	// 	// 		// 		return false;
	// 	// 		// 	}
	// 	// 		// 	foreach ($item['categories'] as $cat) {
	// 	// 		// 		if ($cat['slug'] === $categorySlug) {
	// 	// 		// 			return true;
	// 	// 		// 		}
	// 	// 		// 	}
	// 	// 		// 	return false;
	// 	// 		// });
	// 	// 		// MEMO: 上記をアロー関数で書き換え
	// 	// 		$columns = array_filter($allColumns, fn($item) =>
	// 	// 			isset($item['categories']) &&
	// 	// 			array_filter($item['categories'], fn($cat) => $cat['slug'] === $categorySlug)
	// 	// 		);

	// 	// 		// MEMO: カテゴリ名を取得（最初に見つかったものを使う）
	// 	// 		$categoryName = array_reduce($columns, function ($carry, $item) use ($categorySlug) {
	// 	// 			foreach ($item['categories'] as $category) {
	// 	// 				if ($category['slug'] === $categorySlug) {
	// 	// 					return $category['name'];
	// 	// 				}
	// 	// 			}
	// 	// 			return $carry;
	// 	// 		}, '');

	// 	// 		// MEMO: カテゴリ名が見つからなかったら404にする
	// 	// 		if ($categoryName === '') {
	// 	// 			return $view->render($response->withStatus(404), '404.twig', [
	// 	// 				'message' => 'カテゴリーが見つかりません。',
	// 	// 			]);
	// 	// 		}
	// 	// 	}
	// 	// }
	// 	// COMP: 上記を関数化してみた
	// 	$data = loadColumns($slug);
	// 	$columns = $data['columns'];
	// 	$categoryName = $data['categoryName'];

	// 	// NOTE: ページネーション関数
	// 	// クエリパラメータから現在のページを取得
	// 	$page = max(1, (int)($request->getQueryParams()['page'] ?? 1));
	// 	// ページネーション処理
	// 	$pagination = paginateForQuery($columns, $page, 10);
	// 	$pagedColumns = $pagination['data'];
	// 	$currentPage = $pagination['currentPage'];
	// 	$totalPages = $pagination['totalPages'];

	// 	// NOTE: カテゴリ名が見つからなかったら404にする
	// 	if ($categoryName === '') {
	// 		return $view->render($response->withStatus(404), '404.twig', [
	// 			'message' => 'カテゴリーが見つかりません。',
	// 		]);
	// 	}

	// 	// MEMO: SEO情報の生成
	// 	$seo = $seoService->makeSeo($request, [
	// 		'title'       => 'コラム一覧 - カテゴリー: ' . $slug,
	// 		'description' => 'カテゴリー ' . $slug . ' のコラム一覧ページです',
	// 		'path'        => '/column/category/' . $slug,
	// 		'ogImage'     => 'https://example.com/assets/ogp-column.jpg',
	// 	]);

	// 	// MEMO: カテゴリ別コラム一覧ページのレンダリング
	// 	return $view->render($response, 'column_category.twig', [
	// 		'seo'          => $seo,
	// 		'slug'         => $slug,
	// 		'columns'      => $pagedColumns,
	// 		'currentPage'  => $currentPage,
	// 		'totalPages'   => $totalPages,
	// 		'categoryName' => $categoryName,
	// 	]);
	// });

	// MEMO: NOTE: このルートは "column/2025" や "column/2025/10" にしかマッチしない）
	// TODO: FIXME: 年月でフィルタリングするロジックは未実装
	// $app->get('/column/{year:[0-9]{4}}[/{month:[0-9]{2}}]', function (Request $request, Response $response, $year = null, $month = null) use ($seoService) {
	// 	$view = Twig::fromRequest($request);

	// 	// NOTE: 年月のデフォルト値設定
	// 	$year = $year ?? 'latest';
	// 	$month = $month ?? null;

	// 	// MEMO: SEO情報の生成
	// 	$seo = $seoService->makeSeo($request, [
	// 		'title'       => 'コラム一覧',
	// 		'description' => 'コラム一覧ページの説明文',
	// 		'path'        => '/column' . ($year !== 'latest' ? "/$year" : '') . ($month ? "/$month" : ''),
	// 		'ogImage'     => 'https://example.com/assets/ogp-column.jpg',
	// 	]);

	// 	// MEMO: コラムアーカイブページのレンダリング
	// 	return $view->render($response, 'column_archive.twig', [
	// 		'seo'   => $seo,
	// 		'message' => "Column for year: $year" . ($month ? ", month: $month" : ""),
	// 	]);
	// });

	// MEMO: コラム個別ページ（/column/detail/{id}）
	// $app->get('/column/detail/{id}', function (Request $request, Response $response, $id = null) use ($seoService) {
	// 	$view = Twig::fromRequest($request);

	// 	// NOTE: 記事データの読み込み
	// 	$articlePath = BASE_PATH . "/storage/cache/json/columns/{$id}.json";
	// 	$article = null;
	// 	if (file_exists($articlePath)) {
	// 		$json = file_get_contents($articlePath);
	// 		$article = json_decode($json, true);
	// 	}

	// 	// MEMO: SEO情報の生成（仮にタイトルがある場合のみ）
	// 	$seo = $seoService->makeSeo($request, [
	// 		'title' => $article['name'] ?? 'コラム記事',
	// 		'description' => isset($article['excerpt']) ? strip_tags($article['excerpt']) : '',
	// 		'path' => "/column/detail/{$id}",
	// 		'ogImage' => $article['thumb'] ?? '',
	// 		'structuredData' => [
	// 			'@context' => 'https://schema.org',
	// 			'@type' => 'Article',
	// 			'name' => $article['name'] ?? 'コラム記事',
	// 			'headline' => $article['name'] ?? 'コラム記事',
	// 			'description' => isset($article['excerpt']) ? strip_tags($article['excerpt']) : '',
	// 			'url' => 'https://example.com/column/detail/' . ($article['id'] ?? ''),
	// 			'datePublished' => $article['publishedAt'] ?? '2025-10-01',
	// 			'dateModified' => $article['modifiedAt'] ?? $article['publishedAt'] ?? '2025-10-01',
	// 			'author' => [
	// 				'@type' => 'Organization',
	// 				'name' => 'サイト名',
	// 			],
	// 			'image' => [
	// 				$article['thumb'] ?? 'https://example.com/assets/ogp-column.jpg'
	// 			],
	// 		],
	// 	]);

	// 	// MEMO: コラム個別ページのレンダリング
	// 	return $view->render($response, 'column_single.twig', [
	// 		'article' => $article,
	// 		'seo' => $seo,
	// 	]);
	// });


	// SECTION: ニュース関連のルーティング群
	// ---------------------------------------------------------------------------
	$app->group('/news', function (RouteCollectorProxy $group) {
		// MEMO: ニュース一覧ページ
		$group->get('', [NewsController::class, 'index']);
		// $group->get('', function (Request $request, Response $response, string $page = '1') use ($seoService) {
		// 	$view = Twig::fromRequest($request);

		// 	// NOTE: ニュース記事の取得
		// 	$items = fetchApiData();
		// 	$pagination = paginateForPath($items, (int)$page);

		// 	// MEMO: SEO情報の生成
		// 	$seo = $seoService->makeSeo($request, [
		// 		'title'       => 'ニュース一覧',
		// 		'description' => 'ニュース一覧ページの説明文',
		// 		'ogImage'     => 'https://example.com/assets/ogp-news.jpg',
		// 	]);

		// 	// MEMO: ニュース一覧ページのレンダリング
		// 	return $view->render($response, 'news_list.twig', [
		// 		'seo'  => $seo,
		// 		'news' => is_array($items) ? $items : [],
		// 		'currentPage' => $pagination['currentPage'],
		// 		'totalPages'  => $pagination['totalPages'],
		// 	]);
		// });

		// MEMO: ニュース一覧ページ（ページネーション）
		$group->get('/page/{page}', [NewsController::class, 'page']);
		// $group->get('/page/{page}', function (Request $request, Response $response, string $page = '1') use ($seoService) {
		// 	$view = Twig::fromRequest($request);

		// 	// NOTE: ニュース記事の取得
		// 	$items = fetchApiData();
		// 	$pagination = paginateForPath($items, (int)$page);

		// // MEMO: SEO情報の生成
		// 	$seo = $seoService->makeSeo($request, [
		// 		'title'       => "ニュース一覧（{$page}ページ目）",
		// 		'description' => "ニュース一覧のページ {$page}",
		// 		'ogImage'     => 'https://example.com/assets/ogp-news.jpg',
		// 	]);

		// 	// MEMO: ニュース一覧ページのレンダリング
		// 	return $view->render($response, 'news_list.twig', [
		// 		'seo'         => $seo,
		// 		'page'        => $page,
		// 		'news'        => $pagination['data'],
		// 		'currentPage' => $pagination['currentPage'],
		// 		'totalPages'  => $pagination['totalPages'],
		// 	]);
		// });

		// MEMO: ニュース個別ページ
		$group->get('/{slug}', [NewsController::class, 'detail']);
		// $group->get('/{slug}', function (Request $request, Response $response, string $slug = null) use ($seoService) {
		// 	$view = Twig::fromRequest($request);

		// 	// COMP: キャッシュ機能付きでニュース記事を取得する例 TEST: JSONファイルを保存する想定：キャッシュ期限付き
		// 	$cacheFile = __DIR__ . '/../storage/cache/news/' . $slug . '.json';
		// 	// $cacheExpire = 3600; // 1時間（秒）
		// 	$cacheExpire = 60; // TEST: 1分（秒）← 動作確認用に短くしているだけ。本番はもっと長くしてOK。

		// 	// NOTE: flush_cache=1 がクエリについてたら削除（WordPress側からの命令）
		// 	if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheExpire) {
		// 		// ✅ キャッシュが新しい → そのまま読み込む
		// 		$json = file_get_contents($cacheFile);
		// 		$article = json_decode($json, true);
		// 	} else {
		// 		// ⏳ キャッシュが古い or 存在しない → 再取得して保存
		// 		$items = fetchApiData(); // ← 全記事取得
		// 		$article = null;
		// 		// スラッグに一致する記事を探す
		// 		if ($slug && is_array($items)) {
		// 			foreach ($items as $it) {
		// 				if (isset($it['slug']) && $it['slug'] === $slug) {
		// 					$article = $it;
		// 					break;
		// 				}
		// 			}
		// 		}
		// 		// キャッシュ保存
		// 		if ($article !== null) {
		// 			file_put_contents($cacheFile, json_encode($article, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		// 		}
		// 	}

		// 	// NOTE: 記事が見つからなかった場合の404処理
		// 	if ($article === null) {
		// 		return $view->render($response->withStatus(404), 'news_single.twig', [
		// 			'title'   => '記事が見つかりません',
		// 			'article' => null,
		// 		]);
		// 	}

		// 	// MEMO: SEO情報の生成
		// 	$seo = $seoService->makeSeo($request, [
		// 		'title'       => $article['name'] ?? 'ニュース詳細',
		// 		'description' => $article['description'] ?? 'ニュース詳細ページの説明文',
		// 		'path'        => '/news/' . $slug,
		// 		'ogImage'     => $article['ogImage'] ?? 'https://example.com/assets/ogp-news.jpg',
		// 		'structuredData' => [
		// 			'@context' => 'https://schema.org',
		// 			'@type' => 'NewsArticle',
		// 			'name' => $article['name'] ?? 'ニュース詳細',
		// 			'headline' => $article['name'] ?? 'ニュース詳細',
		// 			'description' => isset($article['description']) ? strip_tags($article['description']) : '',
		// 			'url' => 'https://example.com/news/' . $slug,
		// 			'datePublished' => $article['publishedAt'] ?? '2025-10-01',
		// 			'dateModified' => $article['modifiedAt'] ?? $article['publishedAt'] ?? '2025-10-01',
		// 			'author' => [
		// 				'@type' => 'Organization',
		// 				'name' => 'サイト名',
		// 			],
		// 			'image' => [
		// 				$article['ogImage'] ?? 'https://example.com/assets/ogp-news.jpg'
		// 			],
		// 		],
		// 	]);

		// 	// MEMO: ニュース個別ページのレンダリング
		// 	return $view->render($response, 'news_single.twig', [
		// 		'seo'     => $seo,
		// 		'article' => $article,
		// 	]);
		// });
	});
};