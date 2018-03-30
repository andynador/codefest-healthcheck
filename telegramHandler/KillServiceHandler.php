<?php
	namespace app\telegramHandler;
	
	class KillServiceHandler extends BaseHandler
	{
		public function getMessage(string $chatId, ?string $alias, array $additionalParams = []) : array 
		{
			if ($alias) {
	                        $errorCodes = [400, 401, 403, 404, 500, 502, 503];
        	                shuffle($errorCodes);
				$this->db->updateResponseCodeForService($errorCodes[0], $alias);
                	        return [
                        	        'chat_id' => $chatId,
                                	'text' => 'Cервис остановлен. Если в течении минуты подписчики не включат сервис, то я отправлю уведомление о неработоспособности.',
	                                'reply_markup' => $this->getCommonReplyMarkup($chatId),
        	                ];
                	}

	                $inlineKeyboard = [];
                	foreach ($this->db->getAliveServices() as $row) {
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
