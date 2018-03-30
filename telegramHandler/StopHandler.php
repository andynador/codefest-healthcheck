<?php
	namespace app\telegramHandler;
	
	class StopHandler extends BaseHandler
	{
		public function getMessage(string $chatId, ?string $alias, array $additionalParams = []) : array 
		{
			$this->db->deleteChatIdFromSubscriptions($chatId);
	
        	        return [
                	        'chat_id' => $chatId,
                        	'text' => 'Было приятно общаться, возвращайтесь скорее 😉',
	                        'reply_markup' => $this->getCommonReplyMarkup($chatId),
        	        ];
		}
	}
