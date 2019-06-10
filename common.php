<?php
/**
 * @file mod/dirfind.php
 */
use Friendica\App;

function competence_search($search)
{
	$rows = competence_rows();
	$competenciesIds = competence_ids($rows);

	$competencies = [];
	foreach ($competenciesIds as $id) {
		$name = '';
		$statement = '';
		$uid = '';
		foreach ($rows as $owl) {
			if (strpos($owl['subject'], "#CompetencyId_" . $id)) {
				if (strpos($owl['property'], "#name")) {
					$name = $owl['object'];
				}
				if (strpos($owl['property'], "#statement")) {
					$statement = $owl['object'];
				}
				$user = explode('#', $owl['subject'])[2];
				$uid = explode('_', $user)[1];
			}
		}
		if (strpos($name, $search) > -1 || strpos($statement, $search) > -1) {
			$competencies[] = [
				'uid'          => $uid,

				'name'        => $name,
				'statement'   => $statement,
			];
		}
	}
	sort($competencies);
	return $competencies;
}

function competence_user(App $a, $is_owner, $uid)
{
	$rows = competence_rows();

	$competenciesIds = competence_ids_byUser($rows, $uid);

	$competencies = [];
	foreach ($competenciesIds as $id) {
		$name = '';
		$statement = '';
		foreach ($rows as $owl) {
			if (strpos($owl['subject'], "#CompetencyId_" . $id)) {
				if (strpos($owl['property'], "#name")) {
					$name = $owl['object'];
				}
				if (strpos($owl['property'], "#statement")) {
					$statement = $owl['object'];
				}
			}
		}
		$competencies[] = [
			'name'        => $name,
			'statement'   => $statement,

			'$show'       => $is_owner ? '' : 'none',
			'edit'        => 'competence/' . $a->user['nickname'] . '?action=update&competencyId=' . $id,
			'del'         => 'competence/' . $a->user['nickname'] . '?action=del&competencyId=' . $id
		];
	}
	sort($competencies);
	return $competencies;
}

function competence_add(App $a)
{
	include_once("addon/competence/arc2-starter-pack/arc/ARC2.php");
	include_once("addon/competence/arc2-starter-pack/config.php");
	$store = ARC2::getStore($arc_config);
	if (!$store->isSetUp()) {
		$store->setUp(); /* create MySQL tables */
	}
	$q = '
		SELECT DISTINCT ?subject ?property ?object WHERE { 
		?subject ?property ?object .
		}
	';
	$rows = $store->query($q, 'rows');


	$competenciesIds = competence_ids($rows);

	$idToSave = max($competenciesIds) + 1;

	$r = false;
	$queryName = 'INSERT INTO <file:///home/norberto/teste.owl> CONSTRUCT {
			<http://www.professional-learning.eu/ontologies/competence.owl#CompetencyId_' . $idToSave . '#userId_' . $a->user['uid']  . '> 
			<http://www.w3.org/2000/01/rdf-schema#name> "' .
		trim($_POST['competencie_name']) .
		'" . }';
	$r = $store->query($queryName);
	if (!$r)
		return $r;

	$queryStatement = 'INSERT INTO <file:///home/norberto/teste.owl> CONSTRUCT {
				<http://www.professional-learning.eu/ontologies/competence.owl#CompetencyId_' . $idToSave . '#userId_' . $a->user['uid'] . '> 
				<http://www.w3.org/2000/01/rdf-schema#statement> "' .
		trim($_POST['competencie_statement']) .
		'" . }';
	$r = $store->query($queryStatement);
	return $r;
}

function competence_update(App $a, $competencyId)
{
	include_once("addon/competence/arc2-starter-pack/arc/ARC2.php");
	include_once("addon/competence/arc2-starter-pack/config.php");
	$store = ARC2::getStore($arc_config);
	$q = '
		SELECT DISTINCT ?subject ?property ?object WHERE { 
		?subject ?property ?object .
		}
	';
	$rows = $store->query($q, 'rows');

	if ($rows) {
		foreach ($rows as $row) {
			if (strpos($row['subject'], "#CompetencyId_" . $competencyId)) {
				$query = 'DELETE { <' . $row['subject'] . '> <' . $row['property'] . '> "' . $row['object'] . '" . }';
				$store->query($query);
			}
		}
	} else {
		return;
	}

	$queryName = 'INSERT INTO <file:///home/norberto/teste.owl> CONSTRUCT {
		<http://www.professional-learning.eu/ontologies/competence.owl#CompetencyId_' . $competencyId . '#userId_' . $a->user['uid']  . '> 
		<http://www.w3.org/2000/01/rdf-schema#name> "' .
		trim($_POST['competencie_name']) .
		'" . }';
	$store->query($queryName);

	$queryStatement = 'INSERT INTO <file:///home/norberto/teste.owl> CONSTRUCT {
			<http://www.professional-learning.eu/ontologies/competence.owl#CompetencyId_' . $competencyId . '#userId_' . $a->user['uid'] . '> 
			<http://www.w3.org/2000/01/rdf-schema#statement> "' .
		trim($_POST['competencie_statement']) .
		'" . }';
	return $store->query($queryStatement);
}


function competence_delete($competencyId)
{
	include_once("addon/competence/arc2-starter-pack/arc/ARC2.php");
	include_once("addon/competence/arc2-starter-pack/config.php");
	$store = ARC2::getStore($arc_config);
	if (!$store->isSetUp()) {
		$store->setUp(); /* create MySQL tables */
	}
	$q = '
		SELECT DISTINCT ?subject ?property ?object WHERE { 
		?subject ?property ?object .
		}
	';
	$rows = $store->query($q, 'rows');

	$ok = false;
	if ($rows) {
		foreach ($rows as $row) {
			if (strpos($row['subject'], "#CompetencyId_" . $competencyId)) {
				$query = 'DELETE { <' . $row['subject'] . '> <' . $row['property'] . '> "' . $row['object'] . '" . }';
				$ok = $store->query($query);
			}
		}
	}
	return $ok;
}

function competence_byId($competencyId)
{
	$rows = competence_rows();

	$name = '';
	$statement = '';
	if ($rows) {
		foreach ($rows as $row) {
			if (strpos($row['subject'], "#CompetencyId_" . $competencyId)) {
				if (strpos($row['property'], "#name")) {
					$name = $row['object'];
				}
				if (strpos($row['property'], "#statement")) {
					$statement = $row['object'];
				}
			}
		}
	} else {
		return;
	}

	return [
		'name'        => $name,
		'statement'   => $statement,
	];
}

function competence_ids_byUser($rows, $uid)
{
	$competenciesIds = [];
	foreach ($rows as $row) {
		if (strpos($row['property'], "#name")) {
			if (strpos($row['subject'], "#userId_" . $uid)) {
				$temp = explode('#', $row['subject']);
				$competenciesIds[] = explode('_', $temp[1])[1];
			}
		}
	}
	return $competenciesIds;
}

function competence_ids($rows)
{
	$competenciesIds = [];
	foreach ($rows as $row) {
		if (strpos($row['property'], "#name")) {
			$temp = explode('#', $row['subject']);
			$competenciesIds[] = explode('_', $temp[1])[1];
		}
	}
	return $competenciesIds;
}

function competence_rows()
{
	include_once("addon/competence/arc2-starter-pack/arc/ARC2.php");
	include_once("addon/competence/arc2-starter-pack/config.php");
	$store = ARC2::getStore($arc_config);
	if (!$store->isSetUp()) {
		$store->setUp(); /* create MySQL tables */
	}
	$q = '
		SELECT DISTINCT ?subject ?property ?object WHERE { 
		?subject ?property ?object .
		}
	';
	return $store->query($q, 'rows');
}
