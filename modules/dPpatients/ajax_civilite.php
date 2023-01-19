<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkAdmin();

$patient = new CPatient;
$fields  = array("civilite", "assure_civilite");

foreach ($fields as $_field) {
  switch ($mode = CValue::get("mode")) {
    case "check":
      $ds    = $patient->_spec->ds;
      $query = "SELECT `$_field`, COUNT( * ) AS  `counts`
        FROM `patients`
        GROUP BY `$_field`";
      foreach ($ds->loadHashList($query) as $value => $count) {
        $msgType = $value ? UI_MSG_OK : UI_MSG_WARNING;
        CAppUI::stepAjax(
          "Nombre d'occurences pour '%s' valant '%s' : '%s'",
          $msgType,
          CAppUI::tr("CPatient-$_field-desc"),
          CAppUI::tr("CPatient.$_field.$value"),
          $count
        );
      };
      break;

    case "repair" :
      $where            = array();
      $where["$_field"] = "IS NULL";
      $repaired         = 0;
      $max              = CValue::get("max", 1000);
      $limit            = "0, $max";
      CAppUI::stepAjax(
        "Patients détectés pour une correction de '%s' : %s trouvés.",
        UI_MSG_OK,
        CAppUI::tr("CPatient-$_field-desc"),
        $patient->countList($where)
      );

      foreach ($patient->loadList($where, null, $limit) as $_patient) {
        $_patient->$_field = "guess";
        if ($msg = $_patient->store()) {
          CAppUI::stepAjax(
            "Echec de la correction de %s pour le patient '%s' : %s",
            UI_MSG_WARNING,
            CAppUI::tr("CPatient-$_field-desc"),
            $_patient,
            $msg
          );
          continue;
        }
        $repaired++;
      }
      CAppUI::stepAjax("%s patients corrigés", UI_MSG_OK, $repaired);
      break;

    default:
      CAppUI::stepAjax("Mode '$mode' non pris en charge", UI_MSG_ERROR);
      break;
  }
}
