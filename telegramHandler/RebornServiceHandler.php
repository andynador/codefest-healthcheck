<?php
	namespace app\telegramHandler;
	
	class RebornServiceHandler extends BaseHandler
	{
		public function getMessage(string $chatId, ?string $alias) : array 
		{
			if ($alias) {
                        	$smt = $this->db->prepare("UPDATE service SET response_code = :response_code WHERE alias = :alias");
	                        $smt->bindValue(':response_code', 200, SQLITE3_TEXT);
        	                $smt->bindValue(':alias', $alias, SQLITE3_TEXT);
                	        $smt->execute();

                        	return [
	                                'chat_id' => $chatId,
        	                        'text' => 'Скоро я отправлю нотификацию подписчикам, что сервис в работе. Спасибо 😉',
                	                'reply_markup' => $this->getCommonReplyMarkup($chatId),
                        	];
	                }

	                $inlineKeyboard = [];
        	        $results = $this->db->query('SELECT * FROM service WHERE response_code != "200"');
	                while ($row = $results->fetchArray()) {
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
