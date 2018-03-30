<?php
	namespace app\telegramHandler;
	
	class ServicesHandler extends BaseHandler
	{
		public function getMessage(string $chatId, ?string $alias) : array 
		{
			if ($alias) {
           			$smt = $this->db->prepare("SELECT * FROM service WHERE alias = :alias");
				$smt->bindValue(':alias', $alias, SQLITE3_TEXT);
	                        $result = $smt->execute();

        	                return [
	                                'chat_id' => $chatId,
        	                        'text' => $result->fetchArray()['info'],
                	                'reply_markup' => $this->getCommonReplyMarkup($chatId),
                        	];
                	}
	                $inlineKeyboard = [];
        	        $results = $this->db->query('SELECT * FROM service');
                	while ($row = $results->fetchArray()) {
                        	$inlineKeyboard[] = [['text' => $row['name'], 'callback_data' => BaseHandler::COMMAND_SERVICES . ' ' . $row['alias']]];
	                }

        	        return [
                	        'chat_id' => $chatId,
                        	'text' => 'Выберите один из сервисов',
	                        'reply_markup' => ['inline_keyboard' => $inlineKeyboard],
        	        ];
		}
	}
