<?php
	namespace app\telegramHandler;

	class AlertHandler extends BaseHandler
	{
		private $template;
		
		public function setBody(array $body)
		{
			if ($body['status'] == 'firing') {
                                $text = "<b>Хьюстон, у нас проблемы:</b>\n";
                                foreach ($body['alerts'] as $alert) {
                                        $text .= '<code>' . $alert['annotations']['description'] . "</code>\n";
                                }
                        } else { 
                                $text = "<b>Ураа, вернулись в строй:</b>\n";
                                foreach ($body['alerts'] as $alert) {
                                        $text .= '<code>' . $alert['annotations']['summary'] . "</code>\n";
                                }
                        }
			$this->body = $body;		
		}

		public function getMessage(string $chatId, ?string $alias) : array
		{
			if ($body['status'] == 'firing') {
                                $text = "<b>Хьюстон, у нас проблемы:</b>\n";
                                foreach ($body['alerts'] as $alert) {
                                        $text .= '<code>' . $alert['annotations']['description'] . "</code>\n";
                                }
                        } else {   
                                $text = "<b>Ураа, вернулись в строй:</b>\n";
                                foreach ($body['alerts'] as $alert) {
                                        $text .= '<code>' . $alert['annotations']['summary'] . "</code>\n";
                                }
                        }	
		}
	}
