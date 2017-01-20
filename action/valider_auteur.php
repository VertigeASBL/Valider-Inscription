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
}
