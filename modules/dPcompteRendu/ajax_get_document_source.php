<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CCompteRendu;

/**
 * Retourne la source d'un document
 */
CCanDo::checkRead();

$cr_id             = CValue::get("compte_rendu_id");
$update_date_print = CValue::get("update_date_print", 0);

$cr = new CCompteRendu();
$cr->load($cr_id);

if (!$cr->_id) {
  return;
}

$cr->loadContent();

if (!$cr->canRead()) {
  return;
}

// Mise à jour de la date d'impression
if ($update_date_print) {
  $cr->date_print = "now";
  if ($msg = $cr->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
  }
}
echo "<!DOCTYPE html>
<html>
  <head>
    <link type=\"text/css\" rel=\"stylesheet\" href=\"style/mediboard_ext/htmlarea.css\" media=\"all\" />
  </head>
  <body>
    $cr->_source
  </body>
</html>";
