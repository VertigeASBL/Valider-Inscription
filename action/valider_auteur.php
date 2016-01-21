<?php

/**
 * Action sécurisée pour valider manuellement les membres du site
 *
 * On change le statut de l'auteur et on lui envoie un mail avec de quoi se loguer
 *
 * @return rien
 */
function action_valider_auteur_dist() {

    $securiser_action = charger_fonction('securiser_action', 'inc');
    $id_auteur = $securiser_action();

    if ((! $id_auteur) or (! autoriser('valider', 'auteur', $id_auteur))) {
        include_spip('inc/minipres');
        $msg = _T('valider_inscription:permissions_insuffisantes');
        die(minipres($msg));
    }

    include_spip('base/abstract_sql');
    // le statut à donné a été enregistré dans le champs « prefs »
    $statut = sql_getfetsel('prefs', 'spip_auteurs', 'id_auteur='.intval($id_auteur));

    include_spip('action/editer_objet');
    if ($err = objet_modifier('auteur', $id_auteur, array('statut' => $statut))) {
        include_spip('inc/minipres');
        die(minipres(_T('valider_inscription:erreur_validation'), $err));
    }

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

	include_spip('inc/notifications');
	notifications_envoyer_mails($desc['email'], $msg, $sujet, $from, $head);
}
