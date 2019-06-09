<?php
/**
 * @file competence update
 */

use Friendica\App;

use Friendica\Core\Config;
use Friendica\Core\L10n;
use Friendica\Core\System;
use Friendica\Core\Renderer;

function competence_update_post(App $a, $competencyId)
{
	if (!local_user()) {
		notice(L10n::t('Permission denied.') . EOL);
		return;
	}

	include_once("addon/competence/arc2-starter-pack/arc/ARC2.php");
	include_once("addon/competence/arc2-starter-pack/config.php");
	$store = ARC2::getStore($arc_config);
	$q = '
		SELECT DISTINCT ?subject ?property ?object WHERE { 
		?subject ?property ?object .
		}
	';
	$t = '';
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
	$r = $store->query($queryStatement);


	if ($r) {
		info(L10n::t('Update sucess.') . EOL);
		$redirect = System::baseUrl() . '/competence/' . $a->user['nickname'];
		header("location:$redirect");
		exit();
	} else {
		info(L10n::t("erro") . EOL);
	}
}


function competence_update_content(App $a, $competencyId)
{

	if ((Config::get('system', 'block_public')) && (!local_user()) && (!remote_user())) {
		notice(L10n::t('Public access denied.') . EOL);
		return;
	}


	if (!array_key_exists('user', $a->data)) {
		notice(L10n::t('No user selected') . EOL);
		return;
	}

	include_once("addon/competence/arc2-starter-pack/arc/ARC2.php");
	include_once("addon/competence/arc2-starter-pack/config.php");
	$store = ARC2::getStore($arc_config);
	$q = '
		SELECT DISTINCT ?subject ?property ?object WHERE { 
		?subject ?property ?object .
		}
	';
	$rows = $store->query($q, 'rows');

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

	$competencie = [
		'id'		  => $r[0]['id'],

		'name'        => $name,
		'statement'   => $statement,
	];


	$tpl = Renderer::getMarkupTemplate("competencie_fields.tpl", "addon/competence/");

	$o = Renderer::replaceMacros($tpl, [
		'$title'       => L10n::t('Edit competencie'),
		'$save'        => 'Save',
		'$cancel'      => 'Cancel',
		'$cancelLink'  => System::baseUrl() . '/competence/' . $a->user['nickname'],
		'$competencie' => $competencie,
	]);

	return $o;
}
