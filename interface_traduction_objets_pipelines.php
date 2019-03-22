<?php
/**
 * Utilisations de pipelines par Interface de traduction pour objets
 *
 * @plugin     Interface de traduction pour objets
 * @copyright  2019
 * @author     RAiner
 * @licence    GNU/GPL
 * @package    SPIP\Interface_traduction_objets\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}


function interface_traduction_objets_recuperer_fond($flux) {
	//Insertion des onglets de langue
	$contexte = $flux['args']['contexte'];
	$fond = $flux['args']['fond'];

	if ($objet = _request('exec') AND
		$objet_exec = trouver_objet_exec($objet) AND
		$trouver_table = charger_fonction('trouver_table', 'base') AND
		$desc = $trouver_table($objet_exec['table_objet_sql']) AND
		isset($desc['field']['lang']) AND
		isset($desc['field']['id_trad']) AND
		isset($desc['field']['langue_choisie']) AND
		$id_table_objet = $objet_exec['id_table_objet'] AND
		$id_objet = $contexte[$id_table_objet]
	) {

		if ($fond == 'prive/squelettes/contenu/' . $objet) {
			$langues_dispos = explode(',', $GLOBALS['meta']['langues_multilingue']);
			$table_objet = $objet_exec['table_objet_sql'];

			$donnees_objet = sql_fetsel(
				'id_trad,lang,' . $id_table_objet,
				$table_objet,
				$id_table_objet . '=' . $contexte[$id_table_objet]);


			$lang_objet = $donnees_objet['lang'];

			$id_trad = $donnees_objet['id_trad'];
			$langues_traduites = [];
			if ($id_trad > 0) {
				$trad_new = FALSE;
				$langues_traduites[$lang_objet] = $lang_objet;

				$traductions = sql_allfetsel(
					'lang,' . $id_table_objet,
					$table_objet,
					'id_trad=' . $id_trad . ' AND ' . $id_table_objet . '!=' . $id_objet);

				foreach ($traductions AS $traduction) {
					$langues_traduites[$traduction['lang']] = $traduction[$id_table_objet];

				}
			}
			else {
				$trad_new = TRUE;
				$id_trad = $id_objet;
			}

			var_dump($langues_traduites);
			$contexte['objet'] = $objet;
			$contexte['id_table_objet'] = $id_table_objet;
			$contexte['langues_dispos'] = $langues_dispos;
			$contexte['lang_objet'] = $lang_objet;
			$contexte['id_trad'] = $id_trad;
			$contexte['langues_traduites'] = $langues_traduites;


			$barre_langue = recuperer_fond("prive/inclure/barre_traductions_objet", $contexte, array('ajax' => true));
				$flux['data']['texte'] = str_replace('</h1>', '</h1>' . $barre_langue, $flux['data']['texte']);

		}
	}

	return $flux;
}


function interface_traduction_objets_header_prive($flux) {
	$flux .= '<link rel="stylesheet" href="' . find_in_path('css/interface_traduction_objets_styles.css') . '" type="text/css" media="all" />';

	return $flux;
}

/*Ajoute la langue de traduction dans le chargement du formulaire edition_rubrique*/
function interface_traduction_objets_formulaire_charger($flux) {
	$form = $flux['args']['form'];
	if ($form == 'editer_rubrique' AND _request('new') == 'oui') {

		if (!$flux['data']['lang_dest'] = _request('lang_dest') AND $id_rubrique = _request('id_parent')) {
			$flux['data']['lang_dest'] = sql_getfetsel('lang', 'spip_rubriques', 'id_rubrique=' . $id_rubrique);
		}
		if (isset($flux['data']['lang_dest'])) {
			$flux['data']['_hidden'] .= '<input type="hidden" name="lang_dest" value="' . $flux['data']['lang_dest'] . '"/>';
		}
	}
	return $flux;
}

/*Prise en compte de la langue de traduction dans le traitement du formulaire edition_article*/
function interface_traduction_objets_pre_insertion($flux) {
	if ($flux['args']['table'] == 'spip_rubriques') {

		if ($lang = _request('lang_dest')) {
			$id_trad = _request('lier_trad');
			$flux['data']['lang'] = $lang;
			$flux['data']['langue_choisie'] = 'oui';
			$flux['data']['id_trad'] = $id_trad;
		}
	}
	return $flux;
}