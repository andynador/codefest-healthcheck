<?php
	const CHECK_ERM_COMMERCIALS_HTTP_CODE = 'commercials_http_code';
	const CHECK_STATISTIC_HTTP_CODE = 'statistic_http_code';
	const CHECK_MAILER_HTTP_CODE = 'mailer_http_code';
	const CHECK_AUTH_HTTP_CODE = 'auth_http_code';

	function initChecks(app\Db $db)
	{
		$checks[] = new app\Check(CHECK_ERM_COMMERCIALS_HTTP_CODE, function() use ($db) {
			return $db->getResponseCodeForService('commercials');
        	});

		$checks[] = new app\Check(CHECK_STATISTIC_HTTP_CODE, function () use ($db) {
                        return $db->getResponseCodeForService('statistic');
		});
		
		$checks[] = new app\Check(CHECK_MAILER_HTTP_CODE, function () use ($db) {
			return $db->getResponseCodeForService('mailer');
                });

		$checks[] = new app\Check(CHECK_AUTH_HTTP_CODE, function () use ($db) {
			return $db->getResponseCodeForService('auth');
                });
				
		return $checks;
	}
	
	
