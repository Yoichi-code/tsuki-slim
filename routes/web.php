<?php

use Slim\App;
use Slim\Handlers\Strategies\RequestResponseArgs;
// use Slim\Routing\RouteCollectorProxy;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\SeoService;
// use App\Controllers\NewsController;
// use App\Controllers\ColumnController;

// NOTE: 関数群の読み込み
// require_once BASE_PATH . '/src/Functions/FetchApiData.php';		// ニュースAPI取得
// require_once BASE_PATH . '/src/Functions/LoadColumns.php';		// コラムデータ読み込み
// require_once BASE_PATH . '/src/Functions/PaginateForQuery.php';	// クエリ用ページネーション
// require_once BASE_PATH . '/src/Functions/PaginateForPath.php';	// パス用ページネーション

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
    // $container->set(NewsController::class, function($c) {
    //     return new NewsController(
    //         $c->get(SeoService::class)
    //     );
    // });

	// NOTE: コラムコントローラーの登録
    // $container->set(ColumnController::class, function($c) {
    //     return new ColumnController(
    //         $c->get(SeoService::class)
    //     );
    // });


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

	// NOTE: トップページ
	// ---------------------------------------------------------------------------
	$app->get('/', function (Request $request, Response $response) use ($seoService) {
		$view = Twig::fromRequest($request);

		// MEMO: SEO情報の生成
		$seo = $seoService->makeSeo($request, [
			'ogImage'     => 'https://example.com/assets/ogp-top.jpg',
		]);

		// MEMO: トップページのレンダリング
		return $view->render($response, 'top.twig', [
			'seo' => $seo,
			'message' => 'トップページだよ！Tshkipab！',
		]);
	});

	// NOTE: About us - TSUKIパン教室のご紹介
	// ---------------------------------------------------------------------------
	$app->get('/about', function (Request $request, Response $response) use ($seoService) {
		$view = Twig::fromRequest($request);

		// MEMO: SEO情報の生成
		$seo = $seoService->makeSeo($request, [
			'title'       => 'About us - TSUKIパン教室のご紹介',
			'description' => '国産小麦使用の手ごねパンを焼いてみませんか？TSUKIパン教室の紹介ページです。初心者の方も安心して学べます。',
		]);

		// MEMO: Twigサンプルページのレンダリング
		return $view->render($response, 'about.twig', [
			'seo' => $seo,
			'message' => 'アバウトページ！',
		]);
	});

	// NOTE: Profile - TSUKIパン教室の講師紹介
	// ---------------------------------------------------------------------------
	$app->get('/profile', function (Request $request, Response $response) use ($seoService) {
		$view = Twig::fromRequest($request);

		// MEMO: SEO情報の生成
		$seo = $seoService->makeSeo($request, [
			'title'       => 'Profile - TSUKIパン教室の講師紹介',
			'description' => '国産小麦の手ごねパン、TSUKIパン教室の講師紹介ページです。',
		]);

		// MEMO: Twigサンプルページのレンダリング
		return $view->render($response, 'profile.twig', [
			'seo' => $seo,
			'message' => 'プロフィールページ！',
		]);
	});

	// NOTE: Course - 小麦パンのコースについて
	// ---------------------------------------------------------------------------
	$app->get('/course', function (Request $request, Response $response) use ($seoService) {
		$view = Twig::fromRequest($request);

		// MEMO: SEO情報の生成
		$seo = $seoService->makeSeo($request, [
			'title'       => 'Course - 小麦パンのコースについて',
			'description' => '小麦パンのコース制は上達を目指しながらパン作りを楽しめます。TSUKIパン教室のレッスンコース紹介ページです。',
		]);

		// MEMO: Twigサンプルページのレンダリング
		return $view->render($response, 'course.twig', [
			'seo' => $seo,
			'message' => 'コースページ！',
		]);
	});

	// NOTE: Lesson movie - 小麦パンの動画レッスン
	// ---------------------------------------------------------------------------
	$app->get('/lesson_movie/{slug}', function (Request $request, Response $response) use ($seoService) {
		$view = Twig::fromRequest($request);

		// MEMO: SEO情報の生成
		$seo = $seoService->makeSeo($request, [
			'title'       => 'Movie Lesson - 小麦パンの動画レッスンについて',
			'description' => '小麦パンの動画レッスンで自宅でもパン作りを楽しめます。TSUKIパン教室の動画レッスン紹介ページです。',
		]);

		// MEMO: Twigサンプルページのレンダリング
		return $view->render($response, 'lesson_movie.twig', [
			'seo' => $seo,
			'message' => '動画レッスンページ！',
		]);
	});

	// NOTE: Rice flour bread - 米粉パン Lesson
	// ---------------------------------------------------------------------------
	$app->group('/rice', function (Slim\Routing\RouteCollectorProxy $group) use ($seoService) {
		// MEMO: /rice
		$group->get('', function (Request $request, Response $response) use ($seoService) {
			$view = Twig::fromRequest($request);

			// MEMO: SEO情報の生成
			$seo = $seoService->makeSeo($request, [
				'title'       => 'Rice flour bread - 米粉パン Lesson',
				'description' => 'グルテンフリーの米粉パンが学べます。TSUKIパン教室の米粉パンレッスンの紹介ページです。',
			]);

			// MEMO: Twigサンプルページのレンダリング
			return $view->render($response, 'rice.twig', [
				'seo' => $seo,
				'message' => '米粉パンページ！',
			]);
		});
		// MEMO: /rice/{slug}
		$group->get('/{slug}', function (Request $request, Response $response) use ($seoService) {
			$view = Twig::fromRequest($request);

			// MEMO: SEO情報の生成
			$seo = $seoService->makeSeo($request, [
				'title'       => 'Rice flour bread video - 米粉パン動画 Lesson',
				'description' => 'グルテンフリーの米粉パンが学べます。TSUKIパン教室の米粉パンレッスンの紹介ページです。',
			]);

			// MEMO: Twigサンプルページのレンダリング
			return $view->render($response, 'rice_movie.twig', [
				'seo' => $seo,
				'message' => '米粉パン動画ページ！',
			]);
		});
	});

	// NOTE: Information - TSUKIパン教室の情報
	// ---------------------------------------------------------------------------
	$app->get('/info', function (Request $request, Response $response) use ($seoService) {
		$view = Twig::fromRequest($request);

		// MEMO: SEO情報の生成
		$seo = $seoService->makeSeo($request, [
			'title'       => 'Information - TSUKIパン教室の情報',
			'description' => 'ご不明点があれば何でもお問い合わせください。TSUKIパン教室のレッスン料やよくある質問のページです。',
		]);

		// MEMO: Twigサンプルページのレンダリング
		return $view->render($response, 'info.twig', [
			'seo' => $seo,
			'message' => '情報ページ！',
		]);
	});

	// NOTE: Contact - お問い合わせ
	// ---------------------------------------------------------------------------
	$app->get('/contact', function (Request $request, Response $response) use ($seoService) {
		$view = Twig::fromRequest($request);

		// MEMO: SEO情報の生成
		$seo = $seoService->makeSeo($request, [
			'title'       => 'Contact - お問い合わせ',
			'description' => 'ご不明点があれば何でもお問い合わせください。TSUKIパン教室の問い合わせフォームページです。',
		]);

		// MEMO: Twigサンプルページのレンダリング
		return $view->render($response, 'contact.twig', [
			'seo' => $seo,
			'message' => 'お問い合わせページ！',
		]);
	});

	// NOTE: Lesson - レッスン日程・ご予約
	// ---------------------------------------------------------------------------
	$app->get('/lesson', function (Request $request, Response $response) use ($seoService) {
		$view = Twig::fromRequest($request);

		// MEMO: SEO情報の生成
		$seo = $seoService->makeSeo($request, [
			'title'       => 'Lesson - レッスン日程・ご予約',
			'description' => '国産小麦の手ごねパンを焼いてみませんか？TSUKIパン教室のレッスン予約のページです。',
		]);

		// MEMO: Twigサンプルページのレンダリング
		return $view->render($response, 'lesson.twig', [
			'seo' => $seo,
			'message' => 'レッスン日程・ご予約ページ！',
		]);
	});

	// NOTE: Dear Studens - 生徒さんへ
	// ---------------------------------------------------------------------------
	$app->get('/students', function (Request $request, Response $response) use ($seoService) {
		$view = Twig::fromRequest($request);

		// MEMO: SEO情報の生成
		$seo = $seoService->makeSeo($request, [
			'title'       => 'Students - 生徒さんへ',
			'description' => '皆さん楽しんで学んでいただいてます。TSUKIパン教室のコース制生徒さん向けページです。',
		]);

		// MEMO: Twigサンプルページのレンダリング
		return $view->render($response, 'students.twig', [
			'seo' => $seo,
			'message' => '生徒さん向けページ！',
		]);
	});

	// NOTE: Privacy policy - 個人情報保護方針
	// ---------------------------------------------------------------------------
	$app->get('/privacy', function (Request $request, Response $response) use ($seoService) {
		$view = Twig::fromRequest($request);

		// MEMO: SEO情報の生成
		$seo = $seoService->makeSeo($request, [
			'title'       => 'Privacy Policy - 個人情報保護方針',
			'description' => 'TSUKIパン教室の個人情報保護方針ページです。お客様のプライバシーを尊重し、適切に保護します。',
		]);

		// MEMO: Twigサンプルページのレンダリング
		return $view->render($response, 'privacy.twig', [
			'seo' => $seo,
			'message' => '個人情報保護方針ページ！',
		]);
	});
};