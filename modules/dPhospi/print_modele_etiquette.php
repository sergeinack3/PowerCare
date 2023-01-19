<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CModeleEtiquette;

if (count($_POST) < 17) {
  CAppUI::stepAjax("Vous pouvez fermer cette fenêtre, car elle ne contient pas les données nécessaire à l'aperçu du modèle d'étiquette");
  CApp::rip();
}

$modele_etiquette = new CModeleEtiquette();

$modele_etiquette->largeur_page     = CValue::post("largeur_page");
$modele_etiquette->hauteur_page     = CValue::post("hauteur_page");
$modele_etiquette->nb_lignes        = CValue::post("nb_lignes");
$modele_etiquette->nb_colonnes      = CValue::post("nb_colonnes");
$modele_etiquette->marge_horiz      = CValue::post("marge_horiz");
$modele_etiquette->marge_vert       = CValue::post("marge_vert");
$modele_etiquette->marge_horiz_etiq = CValue::post("marge_horiz_etiq");
$modele_etiquette->marge_vert_etiq  = CValue::post("marge_vert_etiq");
$modele_etiquette->hauteur_ligne    = CValue::post("hauteur_ligne");
$modele_etiquette->nom              = CValue::post("nom");
$modele_etiquette->texte            = CValue::post("texte");
$modele_etiquette->texte_2          = CValue::post("texte_2");
$modele_etiquette->texte_3          = CValue::post("texte_3");
$modele_etiquette->texte_4          = CValue::post("texte_4");
$modele_etiquette->font             = CValue::post("font");
$modele_etiquette->show_border      = CValue::post("show_border");
$modele_etiquette->text_align       = CValue::post("text_align");
$modele_etiquette->printEtiquettes();
