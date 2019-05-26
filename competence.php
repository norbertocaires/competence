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
use Friendica\Content\Nav;
use Friendica\Core\Renderer;
use Friendica\Core\PConfig;
use Friendica\Core\Config;
use Friendica\Core\Worker;

use Friendica\Util\Strings;


use Friendica\Database\DBM;
use Friendica\Database\DBA;

use Friendica\Model\Profile;
use Friendica\Model\Contact;
use Friendica\Model\Group;
use Friendica\Model\Item;
use Friendica\Model\Term;

use Friendica\Protocol\DFRN;

function competence_install() {
	Hook::register('profile_tabs', 'addon/competence/competence.php', 'competence_profile_tabs');

	Logger::log('registered competence');
}

function competence_uninstall() {
	Hook::unregister('profile_tabs', 'addon/competence/competence.php', 'competence_profile_tabs');

	Logger::log('unregistered competence');
}

function competence_profile_tabs($a, &$b) {
	$temp = [
		'label' => L10n::t('Competencie'),
		/*'url'   => $a->getBaseURL() . '/addon/competencie/' . $b['nickname'],*/
		'url' 	=> 'competence/' . $b['nickname'],
		'sel'   => !$b['tab'] && $a->argv[0] == 'competence' ? 'active' : '',
		'title' => L10n::t('Competencie'),
		'id'    => 'competencie-tab',
		'accesskey' => 'c',
	];

	$b['tabs'][] = array_splice($b['tabs'], 4, 0, [$temp] );
}


function competence_module() {}




function competence_init($a) {
	if($a->argc > 1)
		DFRN::autoRedir($a, $a->argv[1]);

	if((Config::get('system','block_public')) && (! local_user()) && (! remote_user())) {
		return;
	}

	Nav::setSelected('home');

	$o = '';

	if($a->argc > 1) {
		$nick = $a->argv[1];

		$condition = ['uid' => local_user(), 'blocked' => false,
			'account_expired' => false, 'account_removed' => false];
		$user = DBA::selectFirst('user', ['email'], $condition);

		if(! count($user))
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


		/*if(! x($a->page,'aside'))
			$a->page['aside'] = '';*/
		$a->page['aside'] .= $vcard_widget;

	}
}

function competence_content(App $a) {
	$o = '';
		

	$is_owner = local_user() == $a->user['uid'];

	$t = '';
	$teste = '';
	$competencies = [];

	$o .= Profile::getTabs($a, $is_owner, $a->profile['nickname']);

	$tpl = Renderer::getMarkupTemplate("competencie.tpl", "addon/competence/");

	$o .= Renderer::replaceMacros($tpl, [
		'q'	       => $t,
		'$title'       => L10n::t('Competencias'),
                '$show'        => $is_owner ? '': 'none' ,
		'$edit'        => 'Editar competencia',
                '$del'         => 'Deletar',
                '$add'         => 'Adicionar competencia',
		'$competencies'=> $competencies,
	]);

	return $o;
}
