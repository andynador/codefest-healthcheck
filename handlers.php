<?php
	use Longman\TelegramBot\Request;
	use app\telegramHandler\BaseHandler;	

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

	function handleAlert(SQLite3 $db, $customNotifications) {
		return function ($request, $response) use ($db, $customNotifications) {
	                $result = json_decode((string)$request->getBody(), true);
        	        if ($result['status'] == 'firing') {
                	        $text = "<b>Хьюстон, у нас проблемы:</b>\n";
                        	foreach ($result['alerts'] as $alert) {
                                	$text .= '<code>' . $alert['annotations']['description'] . "</code>\n";
                        	}
                	} else {
                        	$text = "<b>Ураа, вернулись в строй:</b>\n";
                        	foreach ($result['alerts'] as $alert) {
                                	$text .= '<code>' . $alert['annotations']['summary'] . "</code>\n";
                        	}
                	}
			if (isset($customNotifications[$result['groupLabels']['alertname']])) {
				$chatIDs = $customNotifications[$result['groupLabels']['alertname']];
			} else {
				$results = $db->query('SELECT * FROM subscription');
				while ($row = $results->fetchArray()) {
	                                $chatIDs[] = $row['chat_id'];
				}
			}
			foreach ($chatIDs as $chatId) {
				$data['chat_id'] = $chatId;
				$data['text'] = $text;
				$data['parse_mode'] = 'HTML';
				$data['reply_markup'] = getCommonReplyMarkup($db, $chatId);
				Request::sendMessage($data);				
			}
		};
	}

	function handleHook(SQLite3 $db) {
		return function ($request, $response) use ($db) {
			$body = json_decode((string)$request->getBody(), true);
			$alias = null;
			if (isset($body['callback_query'])) {
				$items = explode(' ', $body['callback_query']['data'], 2);
				$type = $items[0];
				$alias = $items[1] ?? null;
				$chatId = $body['callback_query']['message']['chat']['id'];
			} else {
				$type = $body['message']['text'];
                                $chatId = $body['message']['chat']['id'];
			}

			$handler = BaseHandler::create($db, $type);
			if (empty($handler)) {
				return;
			}
			Request::sendMessage($handler->getMessage($chatId, $alias));	
		};
	}
