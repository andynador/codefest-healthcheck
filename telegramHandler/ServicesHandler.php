<?php
	namespace app\telegramHandler;
	
	class ServicesHandler extends BaseHandler
	{
		public function getMessage(string $chatId, ?string $alias, array $additionalParams = []) : array 
		{
			if ($alias) {
        	                return [
	                                'chat_id' => $chatId,
        	                        'text' => $this->db->getInfoForService($alias),
                	                'reply_markup' => $this->getCommonReplyMarkup($chatId),
                        	];
                	}
	                $inlineKeyboard = [];
                	foreach ($this->db->getServices() as $row) {
                        	$inlineKeyboard[] = [['text' => $row['name'], 'callback_data' => BaseHandler::COMMAND_SERVICES . ' ' . $row['alias']]];
	                }

        	        return [
                	        'chat_id' => $chatId,
                        	'text' => 'Выберите один из сервисов',
	                        'reply_markup' => ['inline_keyboard' => $inlineKeyboard],
        	        ];
		}
	}
