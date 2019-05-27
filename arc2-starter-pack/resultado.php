<?php
	include_once("arc/ARC2.php");
	include_once('config.php');
	$store = ARC2::getStore($arc_config);
	if (!$store->isSetUp()) {
		$store->setUp(); /* create MySQL tables */
	}
	$q = ' SELECT DISTINCT ?subject ?property ?object WHERE { 
		?subject ?property ?object . }';

	$rows = $store->query($q, 'rows');
	$r = '';

	if ($rows = $store->query($q, 'rows')) {
		$r = '<table border=1>
			<th>Subject</th><th>Property</th><th>Object</th>'."\n";
		foreach ($rows as $row) {
			$r .= '<tr><td>'.$row['subject'] .
				'</td><td>'.$row['property'] .
				'</td><td>'.$row['object'] . '</td></tr>'."\n";
		}
		$r .='</table>'."\n";
	}else{
		$r = '<em>No data returned</em>';
	}
	echo $r;
?>

