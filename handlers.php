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
				$data['reply_markup'] = ['inline_keyboard' => getCommonInlineKeyboard()];
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
			$chatId = null;
			if (isset($body['message']['text'])) {
				$type = $body['message']['text'];
				$chatId = $body['message']['chat']['id'];
			} elseif (isset($body['callback_query'])) {
				$items = explode(' ', $body['callback_query']['data']);
				$type = $items[0];
				$alias = $items[1] ?? null;
				$chatId = $body['callback_query']['message']['chat']['id'];
			}
			switch ($type) {
				case '/start':
					Request::sendMessage(getDataForSendStartMessage($db, $chatId, $type, $alias));
					break;
				case '/killservice':
	               	        	Request::sendMessage(getDataForSendKillServiceMessage($db, $chatId, $type, $alias));
					break;
				case '/rebornservice':
	                	        Request::sendMessage(getDataForSendRebornServiceMessage($db, $chatId, $type, $alias));
					break;
				case '/stop':
                               		Request::sendMessage(getDataForSendStopMessage($db, $chatId, $type, $alias));
					break;
				case '/help':
					Request::sendMessage(getDataForSendHelpMessage($db, $chatId, $type, $alias));
					break;
				case '/feedback':
					Request::sendMessage(getDataForSendFeedbackMessage($db, $chatId, $type, $alias));
					break;
				case '/services':
	                         	Request::sendMessage(getDataForSendServicesMessage($db, $chatId, $type, $alias));
					break;
				case '/techinfo':
					Request::sendMessage(getDataForSendTechInfoMessage($db, $chatId, $type, $alias));
					break;
			} 
		};
	}
	function getDataForSendTechInfoMessage(SQLite3 $db, $chatId, $type, $alias) {
		return [
			'chat_id' => $chatId,
			'text' => 'Я живу здесь - https://bitbucket.org/andynador/codefest-healthcheck/
Мой язык - быстрый PHP 7.0
Обрабатывать запросы помогают nginx и мини-фреймворк Slim https://github.com/slimphp/Slim
И собираю информацию и уведомляю подписчиков о событиях при помощи крутого Prometheus https://prometheus.io/',
			'reply_markup' => ['inline_keyboard' => getCommonInlineKeyboard()],
		];
	}
	
	function getDataForSendStartMessage(SQLite3 $db, $chatId, $type, $alias) {
		$smt = $db->prepare("SELECT COUNT(*) AS count FROM subscription WHERE chat_id = :chat_id");
                $smt->bindValue(':chat_id', $chatId, SQLITE3_TEXT);
                $result = $smt->execute();
		if ($result->fetchArray()['count']) {
	                return [
        	        	'chat_id' => $chatId,
                	        'text' => 'Вы уже подписаны на мои обновления 😉',
				'reply_markup' => ['inline_keyboard' => getCommonInlineKeyboard()],
	               	];
		}
	
		$smt = $db->prepare("INSERT INTO subscription (chat_id) values (:chat_id)");
              	$smt->bindValue(':chat_id', $chatId, SQLITE3_TEXT);
                $smt->execute();
                return [
                	'chat_id' => $chatId,
             	        'text' => 'Добро пожаловать в гости! Я - бот родом из Личного кабинета компании 2ГИС. Я умею оповещать о неработоспособности внутренних сервисов  и делиться знаниями. Воспользуйтесь командой /help, чтобы узнать все мои возможности, либо выберите пункт из инлайнового меню 😊',
			'reply_markup' => ['inline_keyboard' => getCommonInlineKeyboard()],
            	];
	}	

	function getDataForSendKillServiceMessage(SQLite3 $db, $chatId, $type, $alias) {
		if ($alias) {   
           		$errorCodes = [400, 401, 403, 404, 500, 502, 503];
                      	shuffle($errorCodes);
                        $smt = $db->prepare("UPDATE service SET response_code = :response_code WHERE alias = :alias");
                        $smt->bindValue(':response_code', $errorCodes[0], SQLITE3_TEXT);
                        $smt->bindValue(':alias', $alias, SQLITE3_TEXT);
                        $smt->execute();
                        return [
                        	'chat_id' => $chatId,
                                'text' => 'Cервис остановлен. Если в течении минуты сервис не поднимется, а такое бывает, если недоступность носит временный характер, то я отправлю уведомление подписчикам.',
				'reply_markup' => ['inline_keyboard' => getCommonInlineKeyboard()],
                        ];
		}
                        
                $inlineKeyboard = [];
                $results = $db->query('SELECT * FROM service WHERE response_code = "200"');
                while ($row = $results->fetchArray()) { 
                     	$inlineKeyboard[] = [['text' => $row['name'], 'callback_data' => $type . ' ' . $row['alias']]];
                }
                if ($inlineKeyboard) {
                      	return [
             			'chat_id' => $chatId,
                                'text' => 'Выберите один из живых сервисов',
                                'reply_markup' => ['inline_keyboard' => $inlineKeyboard],
                   	];
		}
               	return [
               		'chat_id' => $chatId,
               		'text' => 'Чтобы остановить что-нибудь ненужное, нужно сначала завести что-нибудь ненужное, а у нас все сервисы и так лежат. Восстановление доступно с помощью команды /rebornservice',      
			'reply_markup' => ['inline_keyboard' => getCommonInlineKeyboard()],
          	];
	}

	function getDataForSendRebornServiceMessage(SQLite3 $db, $chatId, $type, $alias) {
		if ($alias) {
	       		$smt = $db->prepare("UPDATE service SET response_code = :response_code WHERE alias = :alias");
        	     	$smt->bindValue(':response_code', 200, SQLITE3_TEXT);
                  	$smt->bindValue(':alias', $alias, SQLITE3_TEXT);
                    	$smt->execute();
                        return [
                 		'chat_id' => $chatId,
                         	'text' => 'Скоро все узнают, что сервис в работе. Спасибо 😉',
				'reply_markup' => ['inline_keyboard' => getCommonInlineKeyboard()],
                     	];
           	}

          	$inlineKeyboard = [];
         	$results = $db->query('SELECT * FROM service WHERE response_code != "200"');
                while ($row = $results->fetchArray()) {
           		$inlineKeyboard[] = [['text' => $row['name'], 'callback_data' => $type . ' ' . $row['alias']]];
  		}
          	if ($inlineKeyboard) {
            		return [
                       		'chat_id' => $chatId,
                                'text' => 'Выберите один из потушенных сервисов',
                                'reply_markup' => ['inline_keyboard' => $inlineKeyboard],
                      	];
		}
                        
                return [
        		'chat_id' => $chatId,
              	  	'text' => 'Все сервисы в строю, но можно кого-нибудь потушить командой /killservice',
			'reply_markup' => ['inline_keyboard' => getCommonInlineKeyboard()],
           	];
	}

	function getDataForSendStopMessage(SQLite3 $db, $chatId, $type, $alias) {
		$smt = $db->prepare("DELETE FROM subscription WHERE chat_id = :chat_id");
              	$smt->bindValue(':chat_id', $chatId, SQLITE3_TEXT);
             	$smt->execute();

              	return [
             		'chat_id' => $chatId,
           		'text' => 'Было приятно общаться, возвращайтесь скорее 😉',
			'reply_markup' => ['inline_keyboard' => getCommonInlineKeyboard()],
         	];
	}
	
	function getDataForSendHelpMessage(SQlite3 $db, $chatId, $type, $alias) {
		return [
			'chat_id' => $chatId,
                   	'text' => 'Личным кабинетом пользуются владельцы фирм для внесения актуальной информации о фирме, просмотра рекламы, статистики. Под капотом мы интегрируемся с 20-30 внутренними сервисами, и в случае факапов нужно понимать, на чьей стороне проблема. Моя основная задача - получение оповещений от системы мониторинга и уведомление подписчиков через Telegram. Кроме того, я умею эмулировать поломку сервиса. Для этого нужно воспользоваться командой /killservice и остановить один из сервисов. Никакие боевые машины не страдают 😊',
			'reply_markup' => ['inline_keyboard' => getCommonInlineKeyboard()],
          	];
	}
	
	function getDataForSendFeedbackMessage(SQLite3 $db, $chatId, $type, $alias) {
		return	[
             		'chat_id' => $chatId,
            		'text' => 'Если есть желание оставить обратную связь - напишите пожалуйста вот сюда:
Телеграм: @andynador
email: a.litunenko@2gis.ru',
			'reply_markup' => ['inline_keyboard' => getCommonInlineKeyboard()],
      		];
	}
	
	function getDataForSendServicesMessage(SQLite3 $db, $chatId, $type, $alias) {
		if ($alias) {
			$smt = $db->prepare("SELECT * FROM service WHERE alias = :alias");
	        	$smt->bindValue(':alias', $alias, SQLITE3_TEXT);
        	    	$result = $smt->execute();
                	return [
	               		'chat_id' => $chatId,
        	         	'text' => $result->fetchArray()['info'],
				'reply_markup' => ['inline_keyboard' => getCommonInlineKeyboard()],
          		];
	      	}
   		$inlineKeyboard = [];
	    	$results = $db->query('SELECT * FROM service');
     		while ($row = $results->fetchArray()) {
	      		$inlineKeyboard[] = [['text' => $row['name'], 'callback_data' => $type . ' ' . $row['alias']]];
 		}
	
	        return [
      			'chat_id' => $chatId,
  			'text' => 'Выберите один из сервисов',
	         	'reply_markup' => ['inline_keyboard' => $inlineKeyboard],
   		];
	}

	function getCommonInlineKeyboard() {
		return [
			[
				['text' => 'О сервисах', 'callback_data' => '/services'],
				['text' => 'Стоп сервиса', 'callback_data' => '/killservice'],
				['text' => 'Рестарт сервиса', 'callback_data' => '/rebornservice'],
			],
			[
				['text' => 'Справка', 'callback_data' => '/help'],
                                ['text' => 'О боте', 'callback_data' => '/techinfo'],
                                ['text' => 'Обратная связь', 'callback_data' => '/feedback'],	
			],
                        [
                                ['text' => 'Включить подписку', 'callback_data' => '/start'],
                                ['text' => 'Остановить подписку', 'callback_data' => '/stop'],
                        ]

		];
	}
