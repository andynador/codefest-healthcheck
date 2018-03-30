<?php
	namespace app;
	
	class Db
	{
		private $db;
	
		public function __construct(\SQLite3 $db)
		{
			$this->db = $db;
		}

		public function getChatIDsForSendAlert() : array
		{
			$results = $this->db->query('SELECT * FROM subscription');
                      	while ($row = $results->fetchArray()) {
                     		$chatIDs[] = $row['chat_id'];
                 	}
			
			return $chatIDs;
		}
		
		public function isChatIdExistInSubscription(string $chatId) : bool
		{
			$smt = $this->db->prepare("SELECT 1 FROM subscription WHERE chat_id = :chat_id");
                        $smt->bindValue(':chat_id', $chatId, SQLITE3_TEXT);
                        $result = $smt->execute()->fetchArray(SQLITE3_NUM);

                        return isset($result[0]);
		}

		public function updateResponseCodeForService(string $responseCode, string $alias)
		{
			$smt = $this->db->prepare("UPDATE service SET response_code = :response_code WHERE alias = :alias");
                    	$smt->bindValue(':response_code', $responseCode, SQLITE3_TEXT);
                  	$smt->bindValue(':alias', $alias, SQLITE3_TEXT);
                    	$smt->execute();
		}

		public function getAliveServices() : array
		{
			$result = [];
			$results = $this->db->query('SELECT * FROM service WHERE response_code = "200"');
                        while ($row = $results->fetchArray()) {
				$result[] = $row;
			}

			return $result;
		}

		public function getNotAliveServices() : array
                {
                        $result = [];
                        $results = $this->db->query('SELECT * FROM service WHERE response_code != "200"');
                        while ($row = $results->fetchArray()) {
                                $result[] = $row;
                        }

                        return $result;
                }

		public function getInfoForService(string $alias) : string
		{
			$smt = $this->db->prepare("SELECT * FROM service WHERE alias = :alias");
                        $smt->bindValue(':alias', $alias, SQLITE3_TEXT);
                        $result = $smt->execute();
			
			return $result->fetchArray()['info'];
		}

		public function getServices() : array
		{
			$result = [];
			$results =  $this->db->query('SELECT * FROM service');
			while ($row = $results->fetchArray()) {
                                $result[] = $row;
                        }

                        return $result;
		}
		
		public function insertChatIdIntoSubscriptions(string $chatId)
		{
			$smt = $this->db->prepare("INSERT INTO subscription (chat_id) values (:chat_id)");
                        $smt->bindValue(':chat_id', $chatId, SQLITE3_TEXT);
                        $smt->execute();
		}

		public function deleteChatIdFromSubscriptions(string $chatId)
                {
			$smt = $this->db->prepare("DELETE FROM subscription WHERE chat_id = :chat_id");
                        $smt->bindValue(':chat_id', $chatId, SQLITE3_TEXT);
                        $smt->execute();
                }
		
		public function getResponseCodeForService(string $alias) : string
		{
			$stmt = $this->db->prepare('SELECT response_code FROM service WHERE alias = :alias');
			$stmt->bindValue(':alias', $alias, SQLITE3_TEXT);
			
                        return $stmt->execute()->fetchArray()['response_code'];
		}
	}
