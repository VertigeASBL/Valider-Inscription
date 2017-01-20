<?php
/**
 * Pipelines du plugin Valider Inscription
 *
 * @plugin     Valider Inscription
 * @copyright  2016
 * @author     Michel @ Vertige ASBL
 * @licence    GNU/GPL
 */

/**
 * Changer le message de retour du formulaire d'inscription
 *
 * @pipeline formulaire_traiter
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function valider_inscription_formulaire_traiter($flux) {

	if ($flux['args']['form'] === 'inscription') {
		$flux['data']['message_ok'] = _T('valider_inscription:message_inscription_ok');
	}

	return $flux;
}

function valider_inscription_accueil_encours($flux) {

	$flux .= recuperer_fond(
		'prive/objets/liste/auteurs',
		array(
			'statut' => 'nouveau',
			'titre' => _T('valider_inscription:titre_notification_inscription_a_valider')
		)
	);

	return $flux;
}

function valider_inscription_post_edition($flux) {
	if ($flux['args']['table'] == table_objet_sql('auteur')
		and $flux['args']['action'] == 'instituer'
		and $flux['args']['statut_ancien'] == 'nouveau'
	) {
		$id_auteur = $flux['args']['id_objet'];

		$desc = sql_fetsel(
			'statut, id_auteur, login, email',
			'spip_auteurs',
			'id_auteur=' . intval($id_auteur)
		);

		include_spip('action/inscrire_auteur');

		// generer le mot de passe (ou le refaire si compte inutilise)
		$desc['pass'] = creer_pass_pour_auteur($id_auteur);

		// attribuer un jeton pour confirmation par clic sur un lien
		$desc['jeton'] = auteur_attribuer_jeton($id_auteur);

		// Si on doit lier l'auteur à une zone, il faut le faire ici,
		// parce que les visiteurs n'ont pas les autorisations nécessaires
		// lors de l'inscription
		if (test_plugin_actif('auteur2zone')) {
			include_spip('inc/config');
			$config = lire_config('auteur2zone');

			// Lier à la zone
			include_spip('action/editer_zone');
			zone_lier($config['auteur_zone_auto'], 'auteur', $id_auteur);
		}

		$envoyer_inscription = charger_fonction('envoyer_inscription', '');
		list($sujet, $msg, $from, $head) = $envoyer_inscription($desc, $nom, $statut);
		$envoyer_mail = charger_fonction('envoyer_mail', 'inc');
		$envoyer_mail(
			$desc['email'],
			$sujet,
			$msg
		);
	}

	return $flux;
}
