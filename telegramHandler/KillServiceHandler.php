<?php
	namespace app\telegramHandler;
	
	class KillServiceHandler extends BaseHandler
	{
		public function getMessage(string $chatId, ?string $alias) : array 
		{
			if ($alias) {
	                        $errorCodes = [400, 401, 403, 404, 500, 502, 503];
        	                shuffle($errorCodes);
                	        $smt = $this->db->prepare("UPDATE service SET response_code = :response_code WHERE alias = :alias");
                        	$smt->bindValue(':response_code', $errorCodes[0], SQLITE3_TEXT);
	                        $smt->bindValue(':alias', $alias, SQLITE3_TEXT);
        	                $smt->execute();
                	        return [
                        	        'chat_id' => $chatId,
                                	'text' => 'Cервис остановлен. Если в течении минуты подписчики не включат сервис, то я отправлю уведомление о неработоспособности.',
	                                'reply_markup' => $this->getCommonReplyMarkup($chatId),
        	                ];
                	}

	                $inlineKeyboard = [];
        	        $results = $this->db->query('SELECT * FROM service WHERE response_code = "200"');
                	while ($row = $results->fetchArray()) {
                        	$inlineKeyboard[] = [['text' => $row['name'], 'callback_data' => BaseHandler::COMMAND_KILL_SERVICE . ' ' . $row['alias']]];
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
                	        'text' => 'Чтобы остановить что-нибудь ненужное, нужно сначала завести что-нибудь ненужное, а у нас все сервисы и так лежат. Восстановление доступно с помощью команды "Стоп сервиса"',
                        	'reply_markup' => $this->getCommonReplyMarkup($chatId),
	                ];

		}
	}
