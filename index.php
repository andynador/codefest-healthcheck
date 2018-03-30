<?php
	require_once 'vendor/autoload.php';
	
	$db = new app\Db(new \SQLite3('.codefest-healthcheck'));
	$params = require_once 'params.php';

	$telegram = new Longman\TelegramBot\Telegram($params['telegram']['apiKey'], $params['telegram']['botName']);
	Longman\TelegramBot\Request::initialize($telegram);	

	$app = new Slim\App();
	$app->get('/metrics', handleMetrics(initChecks($db)));
	$app->post('/alert', handleAlert($db, $params['telegram']['customNotifications'] ?? []));
	$app->post('/hook', handleHook($db));

	$app->run();
