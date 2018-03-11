<?php
	const CHECK_ERM_COMMERCIALS_HTTP_CODE = 'commercials_http_code';
	const CHECK_STATISTIC_HTTP_CODE = 'statistic_http_code';
	const CHECK_MAILER_HTTP_CODE = 'mailer_http_code';
	const CHECK_AUTH_HTTP_CODE = 'auth_http_code';

	function initChecks(SQLite3 $db)
	{
		$stmt = $db->prepare('SELECT response_code FROM service WHERE alias = :alias');
		$checks[] = new app\Check(CHECK_ERM_COMMERCIALS_HTTP_CODE, function() use ($stmt) {
			$stmt->bindValue(':alias', 'commercials', SQLITE3_TEXT);
			return $stmt->execute()->fetchArray()['response_code'];
        	});

		$checks[] = new app\Check(CHECK_STATISTIC_HTTP_CODE, function () use ($stmt) {
			$stmt->bindValue(':alias', 'statistic');
                        return $stmt->execute()->fetchArray()['response_code'];
		});
		
		$checks[] = new app\Check(CHECK_MAILER_HTTP_CODE, function () use ($stmt) {
			$stmt->bindValue(':alias', 'mailer');
                        return $stmt->execute()->fetchArray()['response_code'];
                });

		$checks[] = new app\Check(CHECK_AUTH_HTTP_CODE, function () use ($stmt) {
			$stmt->bindValue(':alias', 'auth');
                        return $stmt->execute()->fetchArray()['response_code'];
                });
				
		return $checks;
	}
	
	
