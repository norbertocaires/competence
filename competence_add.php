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

	$r = call_user_func('competence_add', $a);

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
