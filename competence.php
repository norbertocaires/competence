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
use Friendica\Core\Config;

use Friendica\Util\Strings;

use Friendica\Database\DBA;

use Friendica\Model\Profile;
use Friendica\Model\Contact;

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


		if(! array_key_exists('aside', $a->page))
			$a->page['aside'] = '';
		$a->page['aside'] .= $vcard_widget;

	}
}

function competence_content(App $a) {
	if((Config::get('system','block_public')) && (! local_user()) && (! remote_user())) {
		notice(L10n::t('Public access denied.') . EOL);
		return;
	}

	$o = '';
	$is_owner = local_user() == $a->user['uid'];


	include_once("/var/www/friendica/addon/competence/arc2-starter-pack/arc/ARC2.php");
	include_once("/var/www/friendica/addon/competence/arc2-starter-pack/config.php");
	$store = ARC2::getStore($arc_config);
	if (!$store->isSetUp()) {
		$store->setUp(); /* create MySQL tables */
	}
	$q = '
		SELECT DISTINCT ?subject ?property ?object WHERE { 
		?subject ?property ?object .
		}
	';
	$t = '';
	$rows = $store->query($q, 'rows');

	$competenciesOWL = [];
	if ($rows) {
		foreach ($rows as $row) {
			if(strpos($row['subject'], "#Competency_")){
				$competenciesOWL[] = $row;
			}
		}
	} else{
		return;
	}


	if(! array_key_exists('user', $a->data)) {
		notice(L10n::t('No competencie selected') . EOL );
		return;
	}
        

        $competencies = [];

	foreach($competenciesOWL as $owl){
		$name = '';
		$statement = '';


		$competencies[] = [
			'id'          => 0,
			
		        'name'        => $name,
			'statement'   => $statement,

		        '$show'        => $is_owner ? '': 'none' ,
		        'edit'        => 'update_competencie/' . $a->user['nickname'] .'/'. 0,
		        'del'         => 'competencie/'. $a->user['nickname'] .'/'. 0 
		];
	}




	$o .= Profile::getTabs($a, $is_owner, $a->profile['nickname']);

	$tpl = Renderer::getMarkupTemplate("competencies.tpl", "addon/competence/");

	$o .= Renderer::replaceMacros($tpl, [
		'q'	       => $t,
		'$title'       => L10n::t('Competencias'),
                '$show'        => $is_owner ? '': 'none' ,
		'$edit'        => 'Editar competencia',
                '$del'         => 'Deletar',
                '$add'         => 'Adicionar competencia',
		'$addLink'     => System::baseUrl().'/competence/add_competence/' . $a->user['nickname'],
		'$competencies'=> $competencies,
	]);

	return $o;
}
