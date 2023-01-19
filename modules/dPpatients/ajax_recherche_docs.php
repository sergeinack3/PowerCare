<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$date_min          = CView::get("_date_min", "date default|" . CMbDT::date("-1 month"), true);
$date_max          = CView::get("_date_max", "date default|" . CMbDT::date(), true);
$object_class      = CView::get("object_class", "str", true);
$nom_doc           = CView::get("_nom", "str", true);
$user_id           = CView::get("user_id", "ref class|CMediusers", true);
$patient_id_search = CView::get("patient_id_search", "ref class|CPatient", true);
$page              = CView::get("page", "num default|0", true);
$status_doc        = CView::get("_status", "enum list|signe|non_signe|sent", true);
$praticien_id      = CView::get("praticien_id", "ref class|CMediusers", true);
$service_id        = CView::get("service_id", "ref class|CService", true);
$type              = CView::get("type", "str", true);
$lock              = CView::get("_is_locked", "str");
CView::checkin();

$nom_doc = trim($nom_doc);

$ds = CSQLDataSource::get("std");

$document = new CCompteRendu();

$documents_ids = [];

$users_ids = [];

// Seulement les utilisateurs pour lesquels on a le droit en lecture
if (!$user_id) {
    $curr_user = CMediusers::get();
    $users     = $curr_user->loadUsers();
    $users_ids = CMbArray::pluck($users, "_id");
}

foreach (array_keys(CCompteRendu::$templated_classes) as $_template_classe) {
    if (($object_class
            && $_template_classe != $object_class)
        || !in_array($_template_classe, ["CConsultation", "CConsultAnesth", "COperation", "CPatient", "CSejour", "CEvenementPatient"])
    ) {
        continue;
    }

    $request = new CRequest();
    $request->addSelect("compte_rendu.compte_rendu_id, compte_rendu.creation_date");
    $request->addTable("compte_rendu");

    $request->addWhere(["compte_rendu.object_class" => "= '$_template_classe'"]);
    $request->addWhere(["compte_rendu.creation_date BETWEEN '$date_min 00:00:00' AND '$date_max 23:59:59'"]);
    $request->addWhere(["compte_rendu.object_id" => "IS NOT NULL"]);
    if ($lock !== "all") {
        if ($lock === "0") {
            $request->addWhere(["compte_rendu.valide" => "IS NULL OR compte_rendu.valide = '$lock'"]);
        } else {
            $request->addWhere(["compte_rendu.valide" => "= '$lock'"]);
        }
    }

    if ($nom_doc) {
        $request->addWhere(["compte_rendu.nom" => "LIKE '%$nom_doc%'"]);
    }

    // Contraites sur le séjour
    if ($object_class === "CSejour" && ($praticien_id || $service_id || $type)) {
        $request->addLJoin(["sejour" => "sejour.sejour_id = compte_rendu.object_id"]);
        if ($praticien_id) {
            $request->addWhere(["sejour.praticien_id" => "= '$praticien_id'"]);
        }
        if ($service_id) {
            $request->addWhere(["sejour.service_id" => "= '$service_id'"]);
        }
        if ($type) {
            $request->addWhere(["sejour.type" => "= '$type'"]);
        }
    }

    if (in_array($status_doc, ["signe", "non_signe"])) {
        $request->addWhere(
            [
                "compte_rendu.valide" => "= '" . ($status_doc === "signe" ? 1 : 0) . "'"
                    . ($status_doc === "non_signe" ? " OR compte_rendu.valide IS NULL" : ""),
            ]
        );
    }

    $request->addWhere(["compte_rendu.author_id" => $user_id ? "= '$user_id'" : CSQLDataSource::prepareIn($users_ids)]);


    switch ($_template_classe) {
        case "CConsultAnesth":
            $request->addLJoin(["consultation_anesth" => "consultation_anesth.consultation_anesth_id = compte_rendu.object_id"]);
            $request->addLJoin(["consultation" => "consultation.consultation_id = consultation_anesth.consultation_id"]);

            if ($praticien_id) {
                $request->addLJoin(["plageconsult" => "consultation.plageconsult_id = plageconsult.plageconsult_id"]);
                $request->addWhere(["consultation_anesth.chir_id = '$praticien_id' OR plageconsult.chir_id = '$praticien_id'"]);
            }

            if ($patient_id_search) {
                $request->addWhere(["consultation.patient_id" => "= '$patient_id_search'"]);
            }
            break;

        case "CConsultation":
            $request->addLJoin(["consultation" => "consultation.consultation_id = compte_rendu.object_id"]);

            if ($praticien_id) {
                $request->addLJoin(["plageconsult" => "consultation.plageconsult_id = plageconsult.plageconsult_id"]);
                $request->addWhere(["plageconsult.chir_id" => "= '$praticien_id'"]);
            }

            if ($patient_id_search) {
                $request->addWhere(["consultation.patient_id" => "= '$patient_id_search'"]);
            }
            break;

        case "COperation":
            $request->addLJoin(["operations" => "operations.operation_id = compte_rendu.object_id"]);
            $request->addLJoin(["sejour" => "sejour.sejour_id = operations.sejour_id"]);

            if ($praticien_id) {
                $request->addWhere(["operations.chir_id" => "= '$praticien_id'"]);
            }

            if ($patient_id_search) {
                $request->addWhere(["sejour.patient_id" => "= '$patient_id_search'"]);
            }
            break;

        case "CPatient":
            $request->addLJoin(["patients" => "patients.patient_id = compte_rendu.object_id"]);

            if ($patient_id_search) {
                $request->addWhere(["patients.patient_id" => "= '$patient_id_search'"]);
            }
            break;

        case "CSejour":
            $request->addLJoin(["sejour" => "sejour.sejour_id = compte_rendu.object_id"]);

            if ($praticien_id) {
                $request->addWhere(["sejour.praticien_id" => "= '$praticien_id'"]);
            }

            if ($patient_id_search) {
                $request->addWhere(["sejour.patient_id" => "= '$patient_id_search'"]);
            }
            break;
        case "CEvenementPatient":
            $request->addLJoin(["evenement_patient" => "evenement_patient.evenement_patient_id = compte_rendu.object_id"]);

            if ($praticien_id) {
                $request->addWhere(["evenement_patient.praticien_id" => "= '$praticien_id'"]);
            }

            if ($user_id) {
                $request->addWhere(["evenement_patient.owner_id" => "= '$user_id'"]);
            }
            break;
        default:
            break;
    }

    $documents_ids = array_merge($documents_ids, $ds->loadList($request->makeSelect()));
}

$documents = [];

$total = count($documents_ids);

if ($documents_ids) {
    $order_date = CMbArray::pluck($documents_ids, "creation_date");
    array_multisort($order_date, SORT_DESC, $documents_ids);

    $documents_ids = array_slice($documents_ids, $page, 30);

    $documents = $document->loadList(
        ["compte_rendu_id" => CSQLDataSource::prepareIn(CMbArray::pluck($documents_ids, "compte_rendu_id"))],
        "creation_date DESC"
    );

    CStoredObject::massLoadFwdRef($documents, "object_id");
    CStoredObject::massLoadFwdRef($documents, "author_id");

    foreach ($documents as $_document) {
        $object = $_document->loadTargetObject();

        $consult_anesth = null;

        // Chargement du praticien en charge
        switch ($object->_class) {
            case "CConsultAnesth":
                $object->loadRefChir();
                break;

            case "CConsultation":
                $consult_anesth = $object->loadRefConsultAnesth();
                $object->loadRefPraticien();
                break;

            case "COperation":
                $object->loadRefChir();
                break;

            case "CSejour":
            case "CEvenementPatient":
                $object->loadRefPraticien();
                break;

            default:
                break;
        }

        if (($object instanceof CConsultAnesth && !$object->sejour_id && !$object->canDo()->edit)
            || ($object instanceof CConsultation
                && (!$consult_anesth->_id || !$consult_anesth->sejour_id) && !$object->sejour_id && !$object->canDo()->edit)
        ) {
            unset($documents[$_document->_id]);
            continue;
        }

        if (($object instanceof CSejour || $object instanceof CConsultation) && $status_doc === "sent") {
            $_document->getDeliveryStatus();

            if (!$this->_sent_mail && !$this->_sent_apicrypt && !$this->_sent_mssante) {
                unset($documents[$_document->_id]);
                continue;
            }
        }

        if ($object instanceof CPatient) {
            // Si un praticien est sélectionné, on retire les documents liés à un contexte patient
            if ($praticien_id) {
                unset($documents[$_document->_id]);
                continue;
            }
            $object->_ref_patient = $object;
        } elseif(!($object instanceof CEvenementPatient)) {
            $object->_ref_patient = $object->loadRelPatient();
        }

        $_document->loadRefAuthor();
    }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("documents", $documents);
$smarty->assign("page", $page);
$smarty->assign("total", $total);

$smarty->display("inc_recherche_docs.tpl");
