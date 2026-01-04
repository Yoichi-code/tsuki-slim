<?php
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Exception\HttpNotFoundException;
use Middlewares\TrailingSlash;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DI\Container;
use Dotenv\Dotenv;

// ============================================================
// SECTION: Initial Setup
// ============================================================

// ベースパスの定義
define('BASE_PATH', __DIR__ . '/..');

// Composerのオートローダーを読み込み
require BASE_PATH . '/vendor/autoload.php';

// 環境変数の読み込み
$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->load();


// ============================================================
// SECTION: Container
// ============================================================

$container = new Container();
AppFactory::setContainer($container);


// ============================================================
// SECTION: App
// ============================================================

$app = AppFactory::create();


// ============================================================
// SECTION: Middleware
// ============================================================

// MEMO: ルーティングミドルウェアの追加
$app->addRoutingMiddleware();

// MEMO: 404 への対応を追加
// 404 などのエラーミドルウェア
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
// 404 のときに Twig で返すハンドラ
$customNotFoundHandler = function (
	Request $request,
	\Throwable $exception,
	bool $displayErrorDetails,
	bool $logErrors,
	bool $logErrorDetails
) use ($app): Response {
	$view = Twig::fromRequest($request);
	$response = $app->getResponseFactory()->createResponse(404);
	// 最小でOK。必要になったら make_seo($request, [...]) など渡せば拡張可
	return $view->render($response, '404.twig', [
		'title_main' => 'Error 404',
		'title_sub' => 'File Not Found.',
	]);
};
// 404 を差し替え
$errorMiddleware->setErrorHandler(HttpNotFoundException::class, $customNotFoundHandler);

// MEMO: スラッシュがついた時のミドルウェアの追加
$app->add((new TrailingSlash(false))->redirect());


// ============================================================
// SECTION: Routes
// ============================================================

// NOTE: ルーティングの設定
// ここでroutes/web.phpを読み込むことで、ルート定義をアプリケーションに追加します。
// これにより、トップページ、会社概要ページ、お問い合わせページのルートが設定されます。
// ルート定義は、routes/web.phpファイル内で行われています。
(require BASE_PATH . '/routes/web.php')($app);


// ============================================================
// SECTION: Run
// ============================================================

$app->run();
