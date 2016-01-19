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

    include_spip('action/editer_objet');
    if ($err = objet_modifier('auteur', $id_auteur, array('statut' => '6forum'))) {
        include_spip('inc/minipres');
        die(minipres(_T('valider_inscription:erreur_validation'), $err));
    }

    include_spip('base/abstract_sql');
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

	$envoyer_inscription = charger_fonction('envoyer_inscription', '');
	list($sujet, $msg, $from, $head) = $envoyer_inscription($desc, $nom, '6forum');

	include_spip('inc/notifications');
	notifications_envoyer_mails($desc['email'], $msg, $sujet, $from, $head);
}
