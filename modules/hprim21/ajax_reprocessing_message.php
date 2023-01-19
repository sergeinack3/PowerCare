<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Interop\Hprim21\CEchangeHprim21;
use Ox\Interop\Hprim21\CHPrim21Reader;

/**
 * Reprocessing des messages Hprim21
 */
CCanDo::checkRead();

$echg_hprim21_id = CValue::get("echange_hprim21_id");

// Chargement de l'objet
$echg_hprim21 = new CEchangeHprim21();
$echg_hprim21->load($echg_hprim21_id);

$hprimFile = tmpfile();
fwrite($hprimFile, $echg_hprim21->message);
fseek($hprimFile, 0);

$hprimReader = new CHPrim21Reader();
$hprimReader->_echange_hprim21 = $echg_hprim21;
$hprimReader->readFile(null, $hprimFile);

// Mapping de l'échange
$echg_hprim21 = $hprimReader->bindEchange();

if (!count($hprimReader->error_log)) {
  $echg_hprim21->message_valide = true;
}
else {
  $echg_hprim21->message_valide = false;
  CAppUI::setMsg("Erreur(s) pour le fichier '$echg_hprim21->nom_fichier' : $hprimReader->error_log", UI_MSG_WARNING);
}
    
$echg_hprim21->store();

CAppUI::setMsg("Message HPRIM 2.1 retraité", UI_MSG_OK);

echo CAppUI::getMsg();

