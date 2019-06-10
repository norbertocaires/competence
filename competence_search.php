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
	$competencies = call_user_func('competence_search', $search);

	$toReturn = '';

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
		$toReturn .= Renderer::replaceMacros($tpl, [
			'title' => "Competencias encontradas",
			'$competencies' => $entries,
		]);
		info(count($competencies) . L10n::t(' competence found.') . EOL);
	} else {
		$tpl = Renderer::getMarkupTemplate("competecies_search.tpl", "addon/competence");
		$toReturn .= Renderer::replaceMacros($tpl, [
			'title' => "Nenhum resultado",
			'$contacts' => []
		]);
		info(L10n::t('No competence found') . EOL);
	}

	return $toReturn;
}
