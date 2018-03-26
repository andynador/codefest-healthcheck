<?php
	use Longman\TelegramBot\Request;

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

	function handleAlert(SQLite3 $db) {
		return function ($request, $response) use ($db) {
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
			$results = $db->query('SELECT * FROM subscription');
			while ($row = $results->fetchArray()) {
				$data['chat_id'] = $row['chat_id'];
				$data['text'] = $text;
				$data['parse_mode'] = 'HTML';
				Request::sendMessage($data);				
			}
			
                	return $response->write('ok');
		};
	}

	function handleHook(SQLite3 $db) {
		return function ($request, $response) use ($db) {
			$body = json_decode((string)$request->getBody(), true);
			$type = null;
			$alias = null;
			if (isset($body['message']['text'])) {
				$type = $body['message']['text'];
			} elseif (isset($body['callback_query'])) {
				list($type, $alias) = explode(' ', $body['callback_query']['data']);
			}
			switch ($type) {
				case '/start':
					Request::sendMessage(getDataForSendStartMessage($db, $body, $type, $alias));
					break;
				case '/killservice':
	               	        	Request::sendMessage(getDataForSendKillServiceMessage($db, $body, $type, $alias));
					break;
				case '/rebornservice':
	                	        Request::sendMessage(getDataForSendRebornServiceMessage($db, $body, $type, $alias));
					break;
				case '/stop':
                               		Request::sendMessage(getDataForSendStopMessage($db, $body, $type, $alias));
					break;
				case '/help':
					Request::sendMessage(getDataForSendHelpMessage($db, $body, $type, $alias));
					break;
				case '/feedback':
					Request::sendMessage(getDataForSendFeedbackMessage($db, $body, $type, $alias));
					break;
				case '/services':
	                         	Request::sendMessage(getDataForSendServicesMessage($db, $body, $type, $alias));
					break;
			} 
		};
	}
	function getDataForSendStartMessage(SQLite3 $db, $body, $type, $alias) {
		$smt = $db->prepare("SELECT COUNT(*) AS count FROM subscription WHERE chat_id = :chat_id");
                $smt->bindValue(':chat_id', $body['message']['chat']['id'], SQLITE3_TEXT);
                $result = $smt->execute();
		if ($result->fetchArray()['count']) {
	                return [
        	        	'chat_id' => $body['message']['chat']['id'],
                	        'text' => 'Вы уже подписаны на мои обновления 😉',
	               	];
		}
	
		$smt = $db->prepare("INSERT INTO subscription (chat_id) values (:chat_id)");
              	$smt->bindValue(':chat_id', $body['message']['chat']['id'], SQLITE3_TEXT);
                $smt->execute();
                return [
                	'chat_id' => $body['message']['chat']['id'],
             	        'text' => 'Добро пожаловать в гости! Я - бот родом из Личного кабинета компании 2ГИС. Я умею оповещать о неработоспособности внутренних сервисов  и делиться знаниями. Воспользуйтесь командой /help, чтобы узнать все мои возможности 😊',
            	];
	}	

	function getDataForSendKillServiceMessage(SQLite3 $db, $body, $type, $alias) {
		if ($alias) {   
           		$errorCodes = [400, 401, 403, 404, 500, 502, 503];
                      	shuffle($errorCodes);
                        $smt = $db->prepare("UPDATE service SET response_code = :response_code WHERE alias = :alias");
                        $smt->bindValue(':response_code', $errorCodes[0], SQLITE3_TEXT);
                        $smt->bindValue(':alias', $alias, SQLITE3_TEXT);
                        $smt->execute();
                        return [
                        	'chat_id' => $body['callback_query']['message']['chat']['id'],
                                'text' => 'Cервис остановлен. Через минуту мир об этом узнает.',
                        ];
		}
                        
                $inlineKeyboard = [];
                $results = $db->query('SELECT * FROM service WHERE response_code = "200"');
                while ($row = $results->fetchArray()) { 
                     	$inlineKeyboard[] = [['text' => $row['name'], 'callback_data' => $type . ' ' . $row['alias']]];
                }
                if ($inlineKeyboard) {
                      	return [
             			'chat_id' => $body['message']['chat']['id'],
                                'text' => 'Выберите один из живых сервисов',
                                'reply_markup' => ['inline_keyboard' => $inlineKeyboard],
                   	];
		}
               	return [
               		'chat_id' => $body['message']['chat']['id'],
               		'text' => 'Чтобы остановить что-нибудь ненужное, нужно сначала завести что-нибудь ненужное, а у нас все сервисы и так лежат. Восстановление доступно с помощью команды /rebornservice',      
          	];
	}

	function getDataForSendRebornServiceMessage(SQLite3 $db, $body, $type, $alias) {
		if ($alias) {
	       		$smt = $db->prepare("UPDATE service SET response_code = :response_code WHERE alias = :alias");
        	     	$smt->bindValue(':response_code', 200, SQLITE3_TEXT);
                  	$smt->bindValue(':alias', $alias, SQLITE3_TEXT);
                    	$smt->execute();
                        return [
                 		'chat_id' => $body['callback_query']['message']['chat']['id'],
                         	'text' => 'Скоро все узнают, что сервис в работе. Спасибо 😉',
                     	];
           	}

          	$inlineKeyboard = [];
         	$results = $db->query('SELECT * FROM service WHERE response_code != "200"');
                while ($row = $results->fetchArray()) {
           		$inlineKeyboard[] = [['text' => $row['name'], 'callback_data' => $type . ' ' . $row['alias']]];
  		}
          	if ($inlineKeyboard) {
            		return [
                       		'chat_id' => $body['message']['chat']['id'],
                                'text' => 'Выберите один из потушенных сервисов',
                                'reply_markup' => ['inline_keyboard' => $inlineKeyboard],
                      	];
		}
                        
                return [
        		'chat_id' => $body['message']['chat']['id'],
              	  	'text' => 'Все сервисы в строю, но можно кого-нибудь потушить командой /killservice',
           	];
	}

	function getDataForSendStopMessage(SQLite3 $db, $body, $type, $alias) {
		$smt = $db->prepare("DELETE FROM subscription WHERE chat_id = :chat_id");
              	$smt->bindValue(':chat_id', $body['message']['chat']['id'], SQLITE3_TEXT);
             	$smt->execute();

              	return [
             		'chat_id' => $body['message']['chat']['id'],
           		'text' => 'Было приятно общаться, возвращайтесь скорее 😉',
         	];
	}
	
	function getDataForSendHelpMessage(SQlite3 $db, $body, $type, $alias) {
		return [
			'chat_id' => $body['message']['chat']['id'],
                   	'text' => 'Личным кабинетом пользуются владельцы фирм для внесения актуальной информации о фирме, просмотра рекламы, статистики. Под капотом мы интегрируемся с 20-30 внутренними сервисами, и в случае факапов нужно понимать, на чьей стороне проблема. Моя основная задача - получение оповещений от системы мониторинга и уведомление подписчиков через Telegram. Кроме того, я умею эмулировать поломку сервиса. Для этого нужно воспользоваться командой /killservice и остановить один из сервисов. Никакие боевые машины не страдают 😊'
          	];
	}
	
	function getDataForSendFeedbackMessage(SQLite3 $db, $body, $type, $alias) {
		return	[
             		'chat_id' => $body['message']['chat']['id'],
            		'text' => 'Если есть желание оставить обратную связь - напишите пожалуйста вот сюда:
Телеграм: @andynador
email: a.litunenko@2gis.ru'
      		];
	}
	
	function getDataForSendServicesMessage(SQLite3 $db, $body, $type, $alias) {
		if ($alias) {
			$smt = $db->prepare("SELECT * FROM service WHERE alias = :alias");
	        	$smt->bindValue(':alias', $alias, SQLITE3_TEXT);
        	    	$result = $smt->execute();
                	return [
	               		'chat_id' => $body['callback_query']['message']['chat']['id'],
        	         	'text' => $result->fetchArray()['info'],
          		];
	      	}
   		$inlineKeyboard = [];
	    	$results = $db->query('SELECT * FROM service');
     		while ($row = $results->fetchArray()) {
	      		$inlineKeyboard[] = [['text' => $row['name'], 'callback_data' => $type . ' ' . $row['alias']]];
 		}
	
	        return [
      			'chat_id' => $body['message']['chat']['id'],
  			'text' => 'Выберите один из сервисов',
	         	'reply_markup' => ['inline_keyboard' => $inlineKeyboard],
   		];
	}
