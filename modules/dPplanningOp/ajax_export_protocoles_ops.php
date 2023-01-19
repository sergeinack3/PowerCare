<?php

/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CProtocoleOperatoire;

CCanDo::checkEdit();

$chir_id                 = CView::get("chir_id", "ref class|CMediusers");
$function_id             = CView::get("function_id", "ref class|CFunctions");
$search_all_protocole_op = CView::get("search_all_protocole_op", "bool default|1");

CView::checkin();

$context = null;

$chir = new CMediusers();
if ($chir->load($chir_id)) {
    $context = $chir;
}

$function = new CFunctions();
if ($function->load($function_id)) {
    $context = $function;
}

$protocole_op = new CProtocoleOperatoire();
$ds           = CSQLDataSource::get('std');
$where        = [];

if ($search_all_protocole_op) {
    $group       = CGroups::get();
    $where_group = [
        'group_id' => $ds->prepare("= ?", $group->_id),
    ];
    $group->loadFunctions();
    $funcs_ids  = $function->loadIds($where_group);
    $where_func = [
        'function_id' => $ds->prepareIn($funcs_ids),
    ];
    $chirs_ids  = $chir->loadIds($where_func);
    $where_or   = [
        'chir_id ' . $ds->prepareIn($chirs_ids),
        'function_id ' . $ds->prepareIn($funcs_ids),
    ];
    $where[]    = implode(' OR ', $where_or);
    $context    = $group;
} elseif ($chir_id) {
    $where["chir_id"] = "= '$chir_id'";
} elseif ($function_id) {
    $where["function_id"] = "= '$function_id'";
}

$protocoles_ops = $protocole_op->loadList($where, "libelle");

CStoredObject::massLoadBackRefs($protocoles_ops, "materiels_operatoires", null, ["operation_id" => "IS NULL"]);

$csv = new CCSVFile(null, CCSVFile::PROFILE_EXCEL);

$csv->writeLine(
    [
        CAppUI::tr("CProtocoleOperatoire"),
        CAppUI::tr("CMaterielOperatoire"),
        CAppUI::tr("common-Practitioner name"),
        CAppUI::tr("common-Practitioner firstname"),
        CAppUI::tr("Cabinet"),
        CAppUI::tr('CProtocoleOperatoire-libelle'),
        CAppUI::tr('CProtocoleOperatoire-code'),
        CAppUI::tr("CProtocoleOperatoire-numero_version"),
        CAppUI::tr("CProtocoleOperatoire-remarque"),
        CAppUI::tr("CProtocoleOperatoire-description_equipement_salle"),
        CAppUI::tr("CProtocoleOperatoire-description_installation_patient"),
        CAppUI::tr("CProtocoleOperatoire-description_preparation_patient"),
        CAppUI::tr("CProtocoleOperatoire-description_instrumentation"),
        CAppUI::tr("CDM"),
        CAppUI::tr("CPrescriptionLineMedicament-code_cip"),
        CAppUI::tr("CMaterielOperatoire-bdm-desc"),
        CAppUI::tr("CMaterielOperatoire-qte_prevue"),
        CAppUI::tr('CDM-_pharma_code')
    ]
);

/** @var CProtocoleOperatoire $_protocole_op */
foreach ($protocoles_ops as $_protocole_op) {
    $_protocole_op->loadRefChir();
    $_protocole_op->loadRefFunction();
    $_protocole_op->loadRefGroup();
    $csv->writeLine(
        [
            1,
            0,
            $_protocole_op->_ref_chir->_user_last_name,
            $_protocole_op->_ref_chir->_user_first_name,
            $_protocole_op->_ref_function->text,
            $_protocole_op->libelle,
            $_protocole_op->code,
            $_protocole_op->numero_version,
            $_protocole_op->remarque,
            $_protocole_op->description_equipement_salle,
            $_protocole_op->description_installation_patient,
            $_protocole_op->description_preparation_patient,
            $_protocole_op->description_instrumentation,
            null,
            null,
            null,
            null,
        ]
    );

    foreach ($_protocole_op->loadRefsMaterielsOperatoires() as $_materiel_operatoire) {
        $_materiel_operatoire->loadRelatedProduct();

        $csv->writeLine(
            [
                0,
                1,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $_materiel_operatoire->_ref_dm->nom,
                $_materiel_operatoire->code_cip,
                $_materiel_operatoire->bdm,
                $_materiel_operatoire->qte_prevue,
                $_materiel_operatoire->loadRefDM()->getPharmaCode(),
            ]
        );
    }
}
$csv->stream(CAppUI::tr("CProtocoleOperatoire|pl") . " - " . $context->_view);
