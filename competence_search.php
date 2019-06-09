<?php
/**
 * @file mod/dirfind.php
 */
use Friendica\App;
use Friendica\Core\L10n;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;


function competence_search_content(App $a, $search)
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

	$o = '';

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

	$competenciesIds = [];
	foreach ($competenciesOWL as $owl) {
		if (strpos($owl['property'], "#name")) {
			$temp = explode('#', $owl['subject']);
			$competenciesIds[] = explode('_', $temp[1])[1];
		}
	}

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

	if ($competencies) {
		sort($competencies);
		$entries = [];
		foreach ($competencies as $com) {
			$user = DBA::selectFirst('user', ['username', 'nickname'], ['uid' => $com['uid'], 'account_removed' => false, 'blocked' => 0]);
			$entries[] = [
				//user
				'username' => $user['username'],
				'linkProfile' => 'profile/' . $user['nickname'],
				//competencie
				'name'      => $com['name'],
				'statement' => $com['statement']
			];
		}
		$tpl = Renderer::getMarkupTemplate("competecies_search.tpl", "addon/competence");
		$o .= Renderer::replaceMacros($tpl, [
			'title' => "Competencias encontradas",
			'$competencies' => $entries,
		]);
		info(count($competencies) . L10n::t(' competence found.') . EOL);
	} else {
		$tpl = Renderer::getMarkupTemplate("competecies_search.tpl", "addon/competence");
		$o .= Renderer::replaceMacros($tpl, [
			'title' => "Nenhum resultado",
			'$contacts' => []
		]);
		info(L10n::t('No competence found') . EOL);
	}

	return $o;
}
