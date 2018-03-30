<?php
	namespace app\telegramHandler;
	
	class FeedbackHandler extends BaseHandler
	{
		public function getMessage(string $chatId, ?string $alias) : array 
		{
			return  [
                  	      'chat_id' => $chatId,
                        	'text' => 'Если есть желание оставить обратную связь - напишите пожалуйста вот сюда:
Телеграм: @andynador
email: a.litunenko@2gis.ru',
	                        'reply_markup' => $this->getCommonReplyMarkup($chatId),
        	        ];
		}
	}
