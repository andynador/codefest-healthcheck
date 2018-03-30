<?php
	namespace app\telegramHandler;
	
	class StartHandler extends BaseHandler
	{
		public function getMessage(string $chatId, ?string $alias) : array 
		{
			if ($this->isChatSubscribed($chatId)) {
	                        return [
        	                        'chat_id' => $chatId,
                	                'text' => 'Вы уже подписаны на мои обновления 😉',
                        	        'reply_markup' => $this->getCommonReplyMarkup($chatId),
                        	];
                	}

                	$smt = $this->db->prepare("INSERT INTO subscription (chat_id) values (:chat_id)");
	                $smt->bindValue(':chat_id', $chatId, SQLITE3_TEXT);
        	        $smt->execute();
		
                	return [
                        	'chat_id' => $chatId,
	                        'text' => 'Добро пожаловать в гости! Я - бот родом из Личного кабинета компании 2ГИС. Я умею оповещать о неработоспособности внутренних сервисов и делиться знаниями. Выберите пункт "Справка", чтобы узнать все мои возможности 😊',
        	                'reply_markup' => $this->getCommonReplyMarkup($chatId),
                	];			
		}
	}
