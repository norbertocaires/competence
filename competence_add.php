<?php
/**
 * @file competencie add
 */

use Friendica\App;

use Friendica\Core\Config;
use Friendica\Core\L10n;
use Friendica\Core\System;
use Friendica\Core\Renderer;

function competence_add_post(App $a)
{
	if (!local_user()) {
		notice(L10n::t('Permission denied.') . EOL);
		return;
	}

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

	$competenciesOWL = [];
	if ($rows) {
		foreach ($rows as $row) {
			if (strpos($row['subject'], "#userId_")) {
				$competenciesOWL[] = $row;
			}
		}
	} else {
		return;
	}

	$idToSave = 0;
	foreach ($competenciesOWL as $owl) {
		if (strpos($owl['property'], "#name")) {
			$temp = explode('#', $owl['subject'])[1];
			$id = explode('_', $temp)[1];
			if ($idToSave < $id)
				$idToSave = $id;
		}
	}

	$idToSave = $idToSave + 1;
	$queryName = 'INSERT INTO <file:///home/norberto/teste.owl> CONSTRUCT {
			<http://www.professional-learning.eu/ontologies/competence.owl#CompetencyId_' . $idToSave . '#userId_' . $a->user['uid']  . '> 
			<http://www.w3.org/2000/01/rdf-schema#name> "' .
		trim($_POST['competencie_name']) .
		'" . }';
	$store->query($queryName);

	$queryStatement = 'INSERT INTO <file:///home/norberto/teste.owl> CONSTRUCT {
				<http://www.professional-learning.eu/ontologies/competence.owl#CompetencyId_' . $idToSave . '#userId_' . $a->user['uid'] . '> 
				<http://www.w3.org/2000/01/rdf-schema#statement> "' .
		trim($_POST['competencie_statement']) .
		'" . }';
	$r = false;
	$r = $store->query($queryStatement);

	if ($r) {
		info(L10n::t('Added competence.') . EOL);
		$redirect = System::baseUrl() . '/competence/' .  $a->user['nickname'];
		header("location:$redirect");
		exit();
	} else {
		info(L10n::t("erro on added competence") . EOL);
	}
}


function competence_add_content(App $a)
{

	if ((Config::get('system', 'block_public')) && (!local_user()) && (!remote_user())) {
		notice(L10n::t('Public access denied.') . EOL);
		return;
	}

	if (!array_key_exists('user', $a->data) && $a->user && $a->user['uid']) {
		notice(L10n::t('No user selected') . EOL);
		return;
	}

	$competencie = [
		'id'          => '',

		'name'        => '',
		'statement'   => '',

		'idnumber'    => '',
		'autonomy'    => false,
		'frequency'   => false,
		'familiarity' => false,
		'scope'       => false,
		'complexity'  => 'weak',
	];

	$tpl = Renderer::getMarkupTemplate("competencie_fields.tpl", "addon/competence/");

	$o = Renderer::replaceMacros($tpl, [
		'$title'       => L10n::t('Add competence'),
		'$save'        => 'Save',
		'$cancel'      => 'Cancel',
		'$cancelLink'  => System::baseUrl() . '/competence/' . $a->user['nickname'],
		'$competencie' => $competencie,
	]);

	return $o;
}
