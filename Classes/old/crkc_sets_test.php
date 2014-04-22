<?php
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_sets.php');

$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');
	
$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');		// default locale_id
	
$t_list = new ca_lists(); 
$vn_Set_Type_id	= $t_list->getItemIDFromList('set_types', 'public_presentation');

$t_web = new ca_sets();
if (!$t_web->load(array('set_code' => 'PubliceerOpWeb'))) {
	$t_web->setMode(ACCESS_WRITE);
	$t_web->set('set_code', 'PubliceerOpWeb');
	$t_web->set('name_singular', 'Publiceren op het web');
	$t_web->set('name_plural', 'Publiceer op het web');
	$t_web->set('parent_id', null);
	$t_web->set('table_num', 57);
	$t_web->set('type_id', $vn_Set_Type_id );
	$t_web->set('status', 4);
	$t_web->set('access', 1);
	$t_web->set('user_id', 1);
	$t_web->insert();

	if ($t_web->numErrors()) {
		print "ERROR: couldn't create ca_sets row for Publiceer op het web: ".join('; ', $t_web->getErrors())."\n";
		die;
	}

	$t_web->addLabel(array('name' => 'Publiceer op het web'), $pn_locale_id, null, true);
}
$t_web->setMode(ACCESS_WRITE);
$vn_web_id = $t_web->getPrimaryKey();


$t_VenA = new ca_sets();
if (!$t_VenA->load(array('set_code' => 'VraagenAanbod'))) {
	$t_VenA->setMode(ACCESS_WRITE);
	$t_VenA->set('set_code', 'VraagenAanbod');
	$t_VenA->set('name_singular', 'Vraag en Aanbod ');
	$t_VenA->set('name_plural', 'Vraag en Aanbod');
	$t_VenA->set('parent_id', null);
	$t_VenA->set('table_num', 57);
	$t_VenA->set('type_id', $vn_Set_Type_id );
	$t_VenA->set('status', 4);
	$t_VenA->set('access', 1);
	$t_VenA->set('user_id', 1);
	$t_VenA->insert();

	if ($t_VenA->numErrors()) {
		print "ERROR: couldn't create ca_sets row for Vraag en Aanbod: ".join('; ', $t_VenA->getErrors())."\n";
		die;
	}

	$t_VenA->addLabel(array('name' => 'Vraag en Aanbod'), $pn_en_locale_id, null, true);
}
$t_VenA->setMode(ACCESS_WRITE);
$vn_VenA_id = $t_VenA->getPrimaryKey();

print "{$vn_web_id}\n";

?>
