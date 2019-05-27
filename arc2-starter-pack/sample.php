<?php
	include_once("/opt/lampp/htdocs/arc2-starter-pack/arc/ARC2.php");
	$config = array(
		/* db */
		'db_name' => 'arc2test',
		'db_user' => 'root',
		'db_pwd' => '',
		/* store */
		'store_name' => 'arc_tests',
		/* stop after 100 errors */
		'max_errors' => 100,
	);
	$store = ARC2::getStore($config);
	if (!$store->isSetUp()) {
		$store->setUp();
	}
	$store->query('LOAD <http://www.daml.org/2003/01/periodictable/PeriodicTable.owl>');
