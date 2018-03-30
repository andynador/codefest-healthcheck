<?php
	namespace app\telegramHandler;
	
	class RebornServiceHandler extends BaseHandler
	{
		public function getMessage(string $chatId, ?string $alias, array $additionalParams = []) : array 
		{
			if ($alias) {
				$this->db->updateResponseCodeForService(200, $alias);				
                        	return [
	                                'chat_id' => $chatId,
        	                        'text' => 'Скоро я отправлю нотификацию подписчикам, что сервис в работе. Спасибо 😉',
                	                'reply_markup' => $this->getCommonReplyMarkup($chatId),
                        	];
	                }

	                $inlineKeyboard = [];
	                foreach ($this->db->getNotAliveServices() as $row) {
        			$inlineKeyboard[] = [['text' => $row['name'], 'callback_data' => BaseHandler::COMMAND_REBORN_SERVICE . ' ' . $row['alias']]];
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
                	        'text' => 'Все сервисы в строю, но можно кого-нибудь потушить командой "Стоп сервиса"',
                        	'reply_markup' => $this->getCommonReplyMarkup($chatId),
	                ];
		}
	}
