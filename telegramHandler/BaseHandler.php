<?php
	namespace app\telegramHandler;
	
	use app\Db;

	abstract class BaseHandler
	{
		const COMMAND_START = '/start';
		const COMMAND_KILL_SERVICE = '/killservice';
		const COMMAND_REBORN_SERVICE = '/rebornservice';
		const COMMAND_STOP = '/stop';
		const COMMAND_HELP = '/help';
		const COMMAND_FEEDBACK = '/feedback';
		const COMMAND_SERVICES = '/services';
		const COMMAND_TECHINFO = '/techinfo';

		const TEXT_START = 'включить подписку';
		const TEXT_KILL_SERVICE = 'стоп сервиса';
		const TEXT_REBORN_SERVICE = 'рестарт сервиса';
		const TEXT_STOP = 'остановить подписку';
		const TEXT_HELP = 'справка';
		const TEXT_FEEDBACK = 'обратная связь';
		const TEXT_SERVICES = 'о сервисах';
		const TEXT_TECHINFO = 'о боте';
		const TEXT_MAKE_ALERT = 'make alert';
		
		protected $db;

		abstract public function getMessage(string $chatId, ?string $alias, array $additionalParams = []) : array;

		public function __construct(Db $db)
		{
			$this->db = $db;
		}
		
		final public static function create(Db $db, string $text) : ?BaseHandler 
		{
			switch (mb_strtolower($text)) {
				case self::COMMAND_START:
				case self::TEXT_START:
					return new StartHandler($db);
				case self::COMMAND_KILL_SERVICE:
				case self::TEXT_KILL_SERVICE:
					return new KillServiceHandler($db);
				case self::COMMAND_REBORN_SERVICE:
				case self::TEXT_REBORN_SERVICE:
					return new RebornServiceHandler($db);
				case self::COMMAND_STOP:
				case self::TEXT_STOP:
					return new StopHandler($db);
				case self::COMMAND_HELP:
				case self::TEXT_HELP:
					return new HelpHandler($db);
				case self::COMMAND_FEEDBACK:
				case self::TEXT_FEEDBACK:
					return new FeedbackHandler($db);
				case self::COMMAND_SERVICES:
				case self::TEXT_SERVICES:
					return new ServicesHandler($db);
				case self::COMMAND_TECHINFO:
				case self::TEXT_TECHINFO:
					return new TechInfoHandler($db);
				case self::TEXT_MAKE_ALERT:
					return new AlertHandler($db);
				default:
					return null;				
			}
		}

		protected function isChatSubscribed($chatId)
		{
			return $this->db->isChatIdExistInSubscription($chatId);
        	}
		
		protected function getCommonReplyMarkup($chatId) {
                	return ['keyboard' => [
                        	[
                                	['text' => 'О сервисах'],
	                                ['text' => 'Стоп сервиса'],
        	                        ['text' => 'Рестарт сервиса'],
                	        ],
                        	[
                                	['text' => 'Справка'],
	                                ['text' => 'О боте'],
        	                        ['text' => 'Обратная связь'],
                	        ],
                        	[
	                                $this->isChatSubscribed($chatId) ? ['text' => 'Остановить подписку'] : ['text' => 'Включить подписку'],
        	                ]
                	]];
        }

	}
