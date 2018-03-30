<?php
	namespace app\telegramHandler;
	
	class StopHandler extends BaseHandler
	{
		public function getMessage(string $chatId, ?string $alias) : array 
		{
			$smt = $this->db->prepare("DELETE FROM subscription WHERE chat_id = :chat_id");
                	$smt->bindValue(':chat_id', $chatId, SQLITE3_TEXT);
	                $smt->execute();

        	        return [
                	        'chat_id' => $chatId,
                        	'text' => 'Было приятно общаться, возвращайтесь скорее 😉',
	                        'reply_markup' => $this->getCommonReplyMarkup($chatId),
        	        ];
		}
	}
