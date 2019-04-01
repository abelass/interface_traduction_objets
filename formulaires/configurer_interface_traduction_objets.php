<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function formulaires_configurer_multilinguisme_charger_dist() {
	include_spip('prive/formulaires/configurer_multilinguisme');
	$valeurs = array();
	$valeurs['multi_secteurs'] = $GLOBALS['meta']['multi_secteurs'];
	foreach (array('multi_objets', 'gerer_trad_objets') as $m) {
		$valeurs[$m] = explode(',', isset($GLOBALS['meta'][$m]) ? $GLOBALS['meta'][$m] : '');
	}

	if (count($valeurs['multi_objets'])
		or count(explode(',', $GLOBALS['meta']['langues_utilisees'])) > 1
	) {
		$selection = (is_null(_request('multi_objets')) ?
			explode(',', $GLOBALS['meta']['langues_multilingue']) : _request('langues_auth'));
		$valeurs['_langues'] = saisie_langues_utiles('langues_auth', $selection ? $selection : array());
		$valeurs['_nb_langues_selection'] = count($selection);
	}

	return $valeurs;
}


