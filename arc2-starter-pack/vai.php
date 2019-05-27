<?php
	include_once("arc/ARC2.php");
	include_once('config.php');
	$store = ARC2::getStore($arc_config);
	if (!$store->isSetUp()) {
		$store->setUp(); /* create MySQL tables */
	}
	$q ='PREFIX table:
	<file:///home/norberto/teste#>
	SELECT *
	FROM <file:///home/norberto/teste.owl>
';
	$rows = $store->query($q, 'rows');
	$r = '';
	
	if ($rows = $store->query($q, 'rows')) {
		$r = '<table border=1>
			<th>Name</th><th>Symbol</th><th>Number</th>'."\n";
		foreach ($rows as $row) {
			$r .= '<tr><td>'.$row['name'] .
			'</td><td>'.$row['symbol'] .
			'</td><td>'.$row['number'] . '</td></tr>'."\n";
		}
		$r .='</table>'."\n";
	}else{
		$r = '<em>No data returned</em>';
	}
	echo $r;
?>
