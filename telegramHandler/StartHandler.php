<?php
	namespace app\telegramHandler;
	
	class StartHandler extends BaseHandler
	{
		public function getMessage(string $chatId, ?string $alias, array $additionalParams = []) : array 
		{
			if ($this->isChatSubscribed($chatId)) {
	                        return [
        	                        'chat_id' => $chatId,
                	                'text' => 'Вы уже подписаны на мои обновления 😉',
                        	        'reply_markup' => $this->getCommonReplyMarkup($chatId),
                        	];
                	}
			$this->db->insertChatIdIntoSubscriptions($chatId);
		
                	return [
                        	'chat_id' => $chatId,
	                        'text' => 'Добро пожаловать в гости! Я - бот родом из Личного кабинета компании 2ГИС. Я умею оповещать о неработоспособности внутренних сервисов и делиться знаниями. Выберите пункт "Справка", чтобы узнать все мои возможности 😊',
        	                'reply_markup' => $this->getCommonReplyMarkup($chatId),
                	];			
		}
	}
