<?php
/**
 * Autorisations du plugin Valider Inscriptions
 *
 * @plugin     Valider Inscriptions
 * @copyright  2016
 * @author     Michel @ Vertige ASBL
 * @licence    GNU/GPL
 */

function valider_inscription_autoriser() {
}

/* Autoriser à valider une inscription */
function autoriser_valider_auteur_dist($faire, $type, $id, $qui, $opt) {

	return ($qui['statut'] === '0minirezo');
}
