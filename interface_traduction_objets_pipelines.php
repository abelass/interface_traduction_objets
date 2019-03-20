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

	if ($objet = _request('exec') AND
		$objet_exec = trouver_objet_exec($objet) AND
		$trouver_table = charger_fonction('trouver_table', 'base') AND
		$desc = $trouver_table($objet_exec['table_objet_sql']) AND
		isset($desc['field']['lang']) AND
		isset($desc['field']['id_trad']) AND
		isset($desc['field']['langue_choisie'])
	) {

		if ($flux['args']['fond'] == 'prive/squelettes/contenu/' . $objet) {
			print 'ok';
			/*include_spip('inc/config');
			$contexte = array('id_rubrique' => $flux['args']['contexte']['id_rubrique']);

			//Verifier si le plugin taa à prévu une limitation d'affiçchage au niveau des secteur
			$id_secteur = sql_getfetsel('id_secteur', 'spip_rubriques', 'id_rubrique=' . $contexte['id_rubrique']);
			$limiter_secteur = lire_config('taa/limiter_secteur') ? lire_config('taa/limiter_secteur') : array();

			if (!in_array($id_secteur, $limiter_secteur)) {
				$barre_langue = recuperer_fond("prive/editer/barre_traductions_rubrique", $contexte, array('ajax' => true));
				$flux['data']['texte'] = str_replace('</h1>', '</h1>' . $barre_langue, $flux['data']['texte']);
			}*/
		}
	}

	return $flux;
}
