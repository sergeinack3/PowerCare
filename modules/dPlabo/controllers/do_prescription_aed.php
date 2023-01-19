<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CValue;
use Ox\Mediboard\Labo\CPrescriptionLabo;
use Ox\Mediboard\Sante400\CIdSante400;

$verouillee = CValue::post("verouillee");

// Si la prescription est verouillée, un id externe est créé pour identifier la prescription 
if ($verouillee) {
  $tagCatalogue = CAppUI::gconf("dPlabo CCatalogueLabo remote_name");

  $prescription_labo_id = CValue::post("prescription_labo_id");
  $prescription = new CPrescriptionLabo();
  $prescription->load($prescription_labo_id);
  if (!$prescription->verouillee) {
    $prescription->loadRefsFwd();

    // Chargement de l'id400 "labo code4" du praticien
    $prat =& $prescription->_ref_praticien;
    $tagCode4 = "labo code4";
    $idSantePratCode4 = new CIdSante400();
    $idSantePratCode4->loadLatestFor($prat, $tagCode4);

    // creation de l'id400 de la prescription
    $idPresc = new CIdSante400();

    //Paramétrage de l'id 400
    $idPresc->tag = "$tagCatalogue Prat:".str_pad($idSantePratCode4->id400, 4, '0', STR_PAD_LEFT); // tag LABO Prat: 0017
    $idPresc->object_class = "CPrescriptionLabo";
    // Chargement du dernier id externe de prescription du praticien s'il existe
    $idPresc->loadMatchingObject("id400 DESC");

    // Incrementation de l'id400
    $idPresc->id400++;
    $idPresc->id400 = str_pad($idPresc->id400, 4, '0', STR_PAD_LEFT);

    $idPresc->_id = null;
    $idPresc->object_id = $prescription->_id;
    $idPresc->store();
  }
}

$do = new CDoObjectAddEdit("CPrescriptionLabo");
$do->doIt();

