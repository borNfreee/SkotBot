<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Mpociot\BotMan\BotManFactory;

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app->get('/test', function () use ($app) {
    return $app['twig']->render('index.html.twig', array());
})
->bind('homepage')
;

$app->get('/test', function () use ($app) {
    return $app->json(['get test' => 'ok']);
})
    ->bind('test_get')
;

$app->post('/', function (Request $request) use ($app) {
    $botman = BotManFactory::create([
        'microsoft_bot_handle' => 'skot_bot',
        'microsoft_app_id' => '3176e6ca-8dad-4c6d-97f1-e2a86548acc8',
        'microsoft_app_key' => '9HYZnkhBMqvS5dVjYqjfH28',
    ]);

    $botman->hears('test', function (\Mpociot\BotMan\BotMan $bot) {
        return $bot->reply('(facepalm) привет!');
    });

    $botman->hears('подтверди', function (\Mpociot\BotMan\BotMan $bot) {
        return $bot->reply('базарю ёпт');
    });

    $botman->hears('улыбнись', function (\Mpociot\BotMan\BotMan $bot) {
        return $bot->reply('( ͡° ͜ʖ ͡°)');
    });

    $botman->fallback(function(\Mpociot\BotMan\BotMan $bot) {
        return $bot->reply('Не знаю такой команды');
    });

    $botman->listen();

    return $app->json(['status' => 'ok', 'method' => 'post', 'text' => $request->get('text')]);
})
;


$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
