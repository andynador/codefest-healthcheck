<?php
	namespace app\telegramHandler;
	
	class TechInfoHandler extends BaseHandler
	{
		public function getMessage(string $chatId, ?string $alias, array $additionalParams = []) : array 
		{
			return [
                  		'chat_id' => $chatId,
                        	'text' => 'Я живу здесь - https://github.com/andynador/codefest-healthcheck
Мой язык - быстрый и современный PHP 7.1
Обрабатывать запросы помогают nginx и мини-фреймворк Slim https://github.com/slimphp/Slim
И собираю информацию и уведомляю подписчиков о событиях при помощи крутого Prometheus https://prometheus.io/',
	                        'reply_markup' => $this->getCommonReplyMarkup($chatId),
        	        ];
		}
	}
