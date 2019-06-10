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

	$r = call_user_func('competence_update', $a, $competencyId);

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

	$competencie = call_user_func('competence_byId', $competencyId);


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
