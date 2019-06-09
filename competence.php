<?php
/**
 * Name: Competence App
 * Description: Simple Competence Application
 * Version: 0.1
 * Author: Norberto Junior <https://github.com/norbertocaires>
 */

use Friendica\App;

use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\System;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Core\Config;

use Friendica\Content\Nav;


use Friendica\Database\DBA;

use Friendica\Model\Profile;
use Friendica\Model\Contact;

use Friendica\Protocol\DFRN;

function competence_install()
{
	Hook::register('nav_info', 'addon/competence/competence.php', 'competence_nav_info');
	Hook::register('profile_tabs', 'addon/competence/competence.php', 'competence_profile_tabs');

	Logger::log('registered competence');
}

function competence_uninstall()
{
	Hook::unregister('nav_info', 'addon/competence/competence.php', 'competence_nav_info');
	Hook::unregister('profile_tabs', 'addon/competence/competence.php', 'competence_profile_tabs');

	Logger::log('unregistered competence');
}

function competence_nav_info($a, &$b)
{
	if (array_key_exists('search-option', $_GET)) {
		if ($_GET['search-option'] == 'fulltext') {
			$searchFile = "addon/competence/competence_search.php";

			if (file_exists($searchFile)) {
				include_once($searchFile);
			} else {
				notice(L10n::t('File competence_search not found.') . EOL);
				return;
			}

			$a->page['content'] .= call_user_func('competence_search_content', $a, $_GET['search']);
		}
	}
}

function competence_profile_tabs($a, &$b)
{
	$temp = [
		'label' => L10n::t('Competencie'),
		'url' 	=> 'competence/' . $b['nickname'],
		'sel'   => !$b['tab'] && $a->argv[0] == 'competence' ? 'active' : '',
		'title' => L10n::t('Competencie'),
		'id'    => 'competencie-tab',
		'accesskey' => 'c',
	];

	$b['tabs'][] = array_splice($b['tabs'], 4, 0, [$temp]);
}


function competence_module()
{ }


function competence_init($a)
{
	if ($a->argc > 1)
		DFRN::autoRedir($a, $a->argv[1]);

	if ((Config::get('system', 'block_public')) && (!local_user()) && (!remote_user())) {
		return;
	}

	Nav::setSelected('home');

	$o = '';

	if ($a->argc > 1) {
		$nick = $a->argv[1];

		$condition = [
			'uid' => local_user(), 'blocked' => false,
			'account_expired' => false, 'account_removed' => false
		];
		$user = DBA::selectFirst('user', ['email'], $condition);

		if (!count($user))
			return;

		$a->data['user'] = $user[0];
		$a->profile_uid = $user[0]['uid'];

		$profile = Profile::getByNickname($nick, $a->profile_uid);

		$account_type = Contact::getAccountType($profile);

		$tpl = Renderer::getMarkupTemplate("vcard-widget.tpl", "view/");

		$vcard_widget = Renderer::replaceMacros($tpl, [
			'$name' => $profile['name'],
			'$photo' => $profile['photo'],
			'$addr' => defaults($profile, 'addr', ''),
			'$account_type' => $account_type,
			'$pdesc' => defaults($profile, 'pdesc', ''),
		]);


		if (!array_key_exists('aside', $a->page))
			$a->page['aside'] = '';
		$a->page['aside'] .= $vcard_widget;
	}
}

function competence_post(App $a)
{

	$action = $_GET["action"];
	$competencyId = $_GET["competencyId"];
	if ($action == 'add') {
		$addFile = "addon/competence/competence_add.php";

		if (file_exists($addFile)) {
			include_once($addFile);
		} else {
			notice(L10n::t('File competence_add not found.') . EOL);
			return;
		}

		call_user_func('competence_add_post', $a, $competencyId);
	} else if ($action == 'update') {
		$updateFile = "addon/competence/competence_update.php";

		if (file_exists($updateFile)) {
			include_once($updateFile);
		} else {
			notice(L10n::t('File competence_update not found.') . EOL);
			return;
		}

		call_user_func('competence_update_post', $a, $competencyId);
	} else if ($action == 'del') {
		delete($a, $competencyId);
	}
}

function competence_content(App $a)
{

	if ((Config::get('system', 'block_public')) && (!local_user()) && (!remote_user())) {
		notice(L10n::t('Public access denied.') . EOL);
		return;
	}

	if (!array_key_exists('user', $a->data)) {
		notice(L10n::t('No user selected') . EOL);
		return;
	}

	$toReturn = '';
	$nickname =  explode('?', explode('/', $_SERVER['REQUEST_URI'])[2])[0];
	$user = dba::selectFirst('user', ['uid'], ['nickname' => $nickname]);


	$is_owner = local_user() == $user['uid'];

	$action = $_GET["action"];
	$competencyId = $_GET["competencyId"];
	if (!$action || $action == 'del') {
		$toReturn = content($a, $is_owner, $user['uid']);
	} else if ($is_owner && $action == 'add') {
		$addFile = "addon/competence/competence_add.php";

		if (file_exists($addFile)) {
			include_once($addFile);
		} else {
			notice(L10n::t('File competence_add not found.') . EOL);
			return;
		}

		$toReturn .= call_user_func('competence_add_content', $a);
	} else if ($is_owner && $action == 'update') {

		$updateFile = "addon/competence/competence_update.php";

		if (file_exists($updateFile)) {
			include_once($updateFile);
		} else {
			notice(L10n::t('File competence_update not found.') . EOL);
			return;
		}

		$toReturn .= call_user_func('competence_update_content', $a, $competencyId);
	} else if ($is_owner && $action == 'del') {
		$toReturn = content($a, $is_owner, $user['uid']);
	}
	return $toReturn;
}

function content(App $a, $is_owner, $uid)
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

	$competenciesOWL = [];
	if ($rows) {
		foreach ($rows as $row) {
			if (strpos($row['subject'], "#userId_" . $uid)) {
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
		foreach ($competenciesOWL as $owl) {
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

	$o .= Profile::getTabs($a, $is_owner, $a->user['nickname']);
	$tpl = Renderer::getMarkupTemplate("competencies.tpl", "addon/competence/");
	$o .= Renderer::replaceMacros($tpl, [
		'$title'       => L10n::t('Competencies'),
		'$show'        => $is_owner ? '' : 'none',
		'$edit'        => L10n::t('Edit competencie'),
		'$del'         => L10n::t('Delete competencie'),
		'$add'         => L10n::t('Add competencie'),
		'$addLink'     => System::baseUrl() . '/competence/' . $a->user['nickname'] . '?action=add',
		'$competencies' => $competencies,
	]);

	return $o;
}

function delete(App $a, $competencyId)
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

	$ok = false;
	$name = '';
	$statement = '';
	if ($rows) {
		foreach ($rows as $row) {
			if (strpos($row['subject'], "#CompetencyId_" . $competencyId)) {
				$query = 'DELETE { <' . $row['subject'] . '> <' . $row['property'] . '> "' . $row['object'] . '" . }';
				$ok = $store->query($query);
			}
		}
	} else {
		return;
	}

	if ($ok) {
		info(L10n::t('Competence deleted.') . EOL);
		$redirect = System::baseUrl() . '/competence/' .  $a->user['nickname'];
		header("location:$redirect");
		exit();
	} else {
		info(L10n::t("erro on delete competence") . EOL);
	}
}
