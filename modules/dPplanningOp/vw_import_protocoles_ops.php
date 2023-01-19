<?php

/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Dmi\CDM;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CMaterielOperatoire;
use Ox\Mediboard\PlanningOp\CProtocoleOperatoire;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkEdit();

$chir_id     = CView::get("chir_id", "ref class|CMediusers");
$function_id = CView::get("function_id", "ref class|CFunctions");
$group_id    = CView::get("group_id", "ref class|CGroups");
$file        = CValue::files("import");

CView::checkin();

$chir = new CMediusers();
$chir->load($chir_id);

$function = new CFunctions();
$function->load($function_id);

$group = new CGroups();
$group->load($group_id);

if ($file && ($csv = new CCSVFile($file["tmp_name"], CCSVFile::PROFILE_EXCEL))) {
    // Object columns on the first line
    $csv->jumpLine(1);

    // Each line
    $save_protocole = null;

    $chir     = new CUser();
    $function = new CFunctions();
    $etab     = new CGroups();

    while ($line = $csv->readLine()) {
        $is_protocole = CMbArray::get($line, 0);
        $is_materiel  = CMbArray::get($line, 1);

        // Protocole
        if ($is_protocole) {
            $protocole_operatoire = new CProtocoleOperatoire();

            $nom_chir    = CMbArray::get($line, 2);
            $prenom_chir = CMbArray::get($line, 3);
            $cabinet     = CMbArray::get($line, 4);

            if ($nom_chir && $prenom_chir) {
                $chir->user_last_name  = $nom_chir;
                $chir->user_first_name = $prenom_chir;

                $protocole_operatoire->chir_id = $chir->loadMatchingObject();
            } elseif ($cabinet) {
                $function->text = $cabinet;

                $protocole_operatoire->function_id = $function->loadMatchingObject();
            } elseif ($group) {
                $etab->_name = $group;

                $protocole_operatoire->group_id = $etab->loadMatchingObject();
            }

            $code = CMbArray::get($line, 6);

            $id_protocole = null;

            if ($code) {
                $protocole_operatoire->code = $code;
                $id_protocole               = $protocole_operatoire->loadMatchingObjectEsc();
            }

            $protocole_operatoire->libelle = CMbArray::get($line, 5);

            if (!$id_protocole) {
                $id_protocole = $protocole_operatoire->loadMatchingObjectEsc();
            }

            $protocole_operatoire->numero_version                   = CMbArray::get($line, 7);
            $protocole_operatoire->remarque                         = CMbArray::get($line, 8);
            $protocole_operatoire->description_equipement_salle     = CMbArray::get($line, 9);
            $protocole_operatoire->description_installation_patient = CMbArray::get($line, 10);
            $protocole_operatoire->description_preparation_patient  = CMbArray::get($line, 11);
            $protocole_operatoire->description_instrumentation      = CMbArray::get($line, 12);

            $msg = $protocole_operatoire->store();

            CAppUI::setMsg($msg ?: ("CProtocoleOperatoire-msg-" . ($id_protocole ? "modify" : "create")));

            $save_protocole = $protocole_operatoire;
            continue;
        }

        // Matériel
        $materiel_operatoire                          = new CMaterielOperatoire();
        $materiel_operatoire->protocole_operatoire_id = $save_protocole->_id;

        $nom_dm      = CMbArray::get($line, 13);
        $code_cip    = CMbArray::get($line, 14);
        $bdm         = CMbArray::get($line, 15);
        $code_pharma = CMbArray::get($line, 17);

        if ($code_pharma) {
            $pharma_code_idex = CIdSante400::getMatch($dm->_class, "pharma", $code_pharma);
            $dm->load($pharma_code_idex->object_id);
        } elseif ($nom_dm) {
            $dm      = new CDM();
            $dm->nom = $nom_dm;
            $materiel_operatoire->dm_id = $dm->loadMatchingObjectEsc();
        } else {
            $materiel_operatoire->code_cip = $code_cip;
            $materiel_operatoire->bdm      = $bdm;
        }

        $id_materiel = $materiel_operatoire->loadMatchingObject();

        $materiel_operatoire->qte_prevue = CMbArray::get($line, 16);

        $msg = $materiel_operatoire->store();

        CAppUI::setMsg($msg ?: ("CMaterielOperatoire-msg-" . ($id_materiel ? "modify" : "create")));
    }
}


// Création du template
$smarty = new CSmartyDP();

$smarty->assign("messages", CAppUI::getMsg());

$smarty->display("vw_import_protocoles_op");
