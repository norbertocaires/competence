<?php
/**
 * @file competence update
 */

use Friendica\App;

use Friendica\Content\Nav;

use Friendica\Core\Config;
use Friendica\Core\L10n;
use Friendica\Core\System;
use Friendica\Core\Worker;
use Friendica\Core\Renderer;

use Friendica\Database\DBM;

use Friendica\Model\Contact;
use Friendica\Model\Group;
use Friendica\Model\Item;
use Friendica\Model\Profile;
use Friendica\Model\Term;

use Friendica\Protocol\DFRN;

use Friendica\Util\DateTimeFormat;

function competence_update_post(App $a) {

	if (! local_user()) {
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

	$name = '';
	$statement = '';
	if ($rows) {
		foreach ($rows as $row) {
			if(strpos($row['subject'], "#Competency_" . $r[0]['competencyId'])){
				$query = 'DELETE { <' . $row['subject'] . '> <' . $row['property'] . '> "' . $row['object'] . '" . }';
				$store->query($query);
			}
		}
	} else{
		return;
	}

	$queryName = 'INSERT INTO <file:///home/norberto/teste.owl> CONSTRUCT {
			<http://www.professional-learning.eu/ontologies/competence.owl#Competency_' . $r[0]['competencyId'] . '> 
			<http://www.w3.org/2000/01/rdf-schema#name> "' . 
			trim($_POST['competencie_name']) . 
			'" . }';
	$store->query($queryName);

	$queryStatement = 'INSERT INTO <file:///home/norberto/teste.owl> CONSTRUCT {
				<http://www.professional-learning.eu/ontologies/competence.owl#Competency_' . $r[0]['competencyId'] . '> 
				<http://www.w3.org/2000/01/rdf-schema#statement> "' . 
				trim($_POST['competencie_statement']) . 
				'" . }';
	$store->query($queryStatement);
        

        if ($r) {
            info(L10n::t('Competencia atualizada.') . EOL);
            $redirect = System::baseUrl() . '/competencie/' . $a->data['user']['nickname'];
            header("location:$redirect");
            exit();
        }else{
            info(L10n::t("erro") . EOL);
        }
}


function competence_update_content(App $a) {

	if((Config::get('system','block_public')) && (! local_user()) && (! remote_user())) {
		notice(L10n::t('Public access denied.') . EOL);
		return;
	}


	if(! array_key_exists('user', $a->data)) {
		notice(L10n::t('No user selected') . EOL );
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
			if(strpos($row['subject'], "#Competency_" . $r[0]['competencyId'])){
				if(strpos($row['property'], "#name")){
					$name = $row['object'];
				}
				if(strpos($row['property'], "#statement")){
					$statement = $row['object'];
				}
			}
		}
	} else{
		return;
	}

	$competencie = [
		'id'		  => $r[0]['id'],
	
		'name'        => $name,
		'statement'   => $statement,
	];


	$tpl = Renderer::getMarkupTemplate("competencie_fields.tpl", "addon/competence/");

	$o = Renderer::replaceMacros($tpl, [
		'$title'       => L10n::t('Adicionar competencia'),          
		'$save'        => 'Salvar',
		'$cancel'      => 'Cancelar',
		'$cancelLink'  => System::baseUrl(). '/competence/' . $a->user['nickname'],
        '$competencie' => $competencie,            
	]);
        
	return $o;
}

