<?php
	namespace app\telegramHandler;

	class AlertHandler extends BaseHandler
	{
		public function getMessage(string $chatId, ?string $alias, array $additionalParams = []) : array
		{
			if (!$this->isAlertValid($additionalParams)) {
				return [
					'chat_id' => $chatId,
					'text' => 'Невалидный алерт',
					'reply_market' => $this->getCommonReplyMarkup($chatId),
				];
			}
			
			return [
				'chat_id' => $chatId,
				'text' => $this->getAlertText($additionalParams['status'], $additionalParams['alerts']),
				'parse_mode' => 'HTML',
				'reply_market' => $this->getCommonReplyMarkup($chatId),
			];	
		}

		private function isAlertValid(array $params) : bool
		{
			return isset($params['status'], $params['alerts']);
		}

		private function getAlertText(string $status, array $alerts) : string
		{
			if ($status == 'firing') {
				$text = "<b>Хьюстон, у нас проблемы:</b>\n";
        	        	foreach ($alerts as $alert) {
             				$text .= '<code>' . $alert['annotations']['description'] . "</code>\n";
                     		}
				
				return $text;
			}

			$text = "<b>Ураа, вернулись в строй:</b>\n";
                    	foreach ($alerts as $alert) {
                   		$text .= '<code>' . $alert['annotations']['summary'] . "</code>\n";
                  	}
			
			return $text;
		}
	}
