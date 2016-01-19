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