<?php
/**
 * Action pour inscrire un auteur
 *
 * surcharge le fichier ecrire/action/inscrire_auteur.php
 *
 * @plugin     Valider Auteur
 * @copyright  2016
 * @author     Michel @ Vertige ASBL
 * @licence    GNU/GPL
 */

/**
 * Inscrire un nouvel auteur sur la base de son nom et son email
 * L'email est utilise pour reperer si il existe deja ou non
 * => identifiant par defaut
 *
 * @param string $statut
 * @param string $mail_complet
 * @param string $nom
 * @param array $options
 *   login : login precalcule
 *   id : id_rubrique fournit en second arg de #FORMULAIRE_INSCRIPTION
 *   from : email de l'envoyeur pour l'envoi du mail d'inscription
 *   force_nouveau : forcer le statut nouveau sur l'auteur inscrit, meme si il existait deja en base
 * @return array|string
 */
function action_inscrire_auteur($statut, $mail_complet, $nom, $options = array()) {

	if (! is_array($options)) {
		$options = array('id' => $options);
	}

	if (function_exists('test_inscription')) {
		$f = 'test_inscription';
	} else {
		$f = 'test_inscription_dist';
	}
	$desc = $f($statut, $mail_complet, $nom, $options);

	// erreur ?
	if (! is_array($desc)) {
		return _T($desc);
	}

	include_spip('base/abstract_sql');
	$row = sql_fetsel(
		'statut, id_auteur, login, email',
		'spip_auteurs',
		'email=' . sql_quote($desc['email'])
	);

	if ($row) {
		if (isset($options['force_nouveau']) and $options['force_nouveau'] == true) {
			$desc['id_auteur'] = $row['id_auteur'];
			$desc = inscription_nouveau($desc);
		} else {
			$desc = $row;
		}
	} else {
		// s'il n'existe pas deja, creer les identifiants
		$desc = inscription_nouveau($desc);
	}

	// erreur ?
	if (! is_array($desc)) {
		return $desc;
	}

	$notifications = charger_fonction('notifications', 'inc');
	$notifications(
		'inscription_a_valider',
		$desc['id_auteur'],
		array('nom' => $desc['nom'], 'email' => $desc['email'])
	);

	return $desc;
}
