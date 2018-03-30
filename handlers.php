<?php
	use Longman\TelegramBot\Request;
	use app\telegramHandler\BaseHandler;
	use app\Db;

	function handleMetrics($checks) {
		return function ($request, $response) use ($checks) {
        	        $registry = new Prometheus\CollectorRegistry(new Prometheus\Storage\InMemory());
	                foreach ($checks as $check) {
                        	$gauge = $registry->getOrRegisterGauge('bizaccount', 'status', $check->getName(), ['name']);
                	        $gauge->set($check->call(), [$check->getName()]);
        	        }

	                $renderer = new Prometheus\RenderTextFormat();

                	return $response->withHeader('Content-Type', Prometheus\RenderTextFormat::MIME_TYPE)->write(trim($renderer->render($registry->getMetricFamilySamples())));
        	};
	}

	function handleAlert(Db $db, $customNotifications)
	{
		return function ($request, $response) use ($db, $customNotifications) {
	                $result = json_decode((string)$request->getBody(), true);
			if (isset($customNotifications[$result['groupLabels']['alertname']])) {
				$chatIDs = $customNotifications[$result['groupLabels']['alertname']];
			} else {
	             		$chatIDs = $db->getChatIDsForSendAlert();
			}
			$handler = BaseHandler::create($db, BaseHandler::TEXT_MAKE_ALERT);
			foreach ($chatIDs as $chatId) {
				Request::sendMessage($handler->getMessage($chatId, null, $result));
			}
		};
	}

	function handleHook(Db $db)
	{
		return function ($request, $response) use ($db) {
			$body = json_decode((string)$request->getBody(), true);
			$params = getParamsFromBody($body);

			$handler = BaseHandler::create($db, $params['type']);
			if (empty($handler)) {
				return;
			}
			Request::sendMessage($handler->getMessage($params['chatId'], $params['alias']));	
		};
	}

	function getParamsFromBody(array $body) : array
	{
		if (isset($body['callback_query'])) {
               		$items = explode(' ', $body['callback_query']['data'], 2);
			
			return [
				'type' => $items[0],
				'alias' => $items[1] ?? null,
				'chatId' => $body['callback_query']['message']['chat']['id'],
			];
		}
		
		return [
			'type' => $body['message']['text'],
			'alias' => null,
			'chatId' => $body['message']['chat']['id'],
		];
	}
