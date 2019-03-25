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

			$select = ['id_trad', 'lang', $id_table_objet];

			$id_parent_table = '';
			if (isset($desc['field']['id_rubrique'])) {
				if ($objet != 'rubrique') {
					$id_parent_table = 'id_rubrique';
				}
				else {
					$id_parent_table = 'id_parent';
				}
				$select[] = $id_parent_table;
			}

			$donnees_objet = sql_fetsel(
				$select,
				$table_objet,
				$id_table_objet . '=' . $contexte[$id_table_objet]);

			$id_trad_parent = '';
			if ($id_parent_table) {
				$rubrique_parent = sql_fetsel(
					'id_trad, id_rubrique',
					'spip_rubriques',
					'id_rubrique=' . $donnees_objet[$id_parent_table]);

					$id_trad_parent = $rubrique_parent['id_rubrique'];
			}


			$lang_objet = $donnees_objet['lang'];
			$id_trad = $donnees_objet['id_trad'];
			$langues_traduites = [];

			if ($id_trad > 0) {
				$trad_new = FALSE;
				$langues_traduites[$lang_objet] = $id_objet;
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

			$contexte['objet'] = $objet;
			$contexte['id_objet'] = $id_objet;

			// Si secteur par langue, on établit l'id_parent.
			if (test_plugin_actif('secteur_langue')) {
				$contexte['id_parent'] = isset($donnees_objet['id_rubrique']) ?
					$donnees_objet['id_rubrique'] :
					(isset($donnees_objet['id_parent']) ? $donnees_objet['id_parent'] : '');
			}
			$contexte['id_table_objet'] = $id_table_objet;
			$contexte['langues_dispos'] = $langues_dispos;
			$contexte['lang_objet'] = $lang_objet;
			$contexte['id_trad'] = $id_trad;
			$contexte['id_trad_parent'] = $id_trad_parent;
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
	$segments = explode('_', $form);

	if ($segments[0] == 'editer' AND
		_request('new') == 'oui' AND
		isset($segments[1]) AND
		$table_objet = table_objet($segments[1])) {

		if (!$flux['data']['lang_dest'] = _request('lang_dest') AND $id_parent = _request('id_parent')) {
			$flux['data']['lang_dest'] = sql_getfetsel('lang', 'spip_rubriques', 'id_rubrique=' . $id_parent);
		}
		if (isset($flux['data']['lang_dest'])) {
			$flux['data']['_hidden'] .= '<input type="hidden" name="lang_dest" value="' . $flux['data']['lang_dest'] . '"/>';
		}
	}


	return $flux;
}

/*Prise en compte de la langue de traduction dans le traitement du formulaire edition_article*/
function interface_traduction_objets_pre_insertion($flux) {
	if ($lang = _request('lang_dest') AND $id_trad = _request('lier_trad')) {
		$flux['data']['lang'] = $lang;
		$flux['data']['langue_choisie'] = 'oui';
		$flux['data']['id_trad'] = $id_trad;
		spip_log($flux, 'teste');
	}
	return $flux;
}