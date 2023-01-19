<?php

/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CMergeLog;
use Ox\Mediboard\Urgences\CRPU;

function viewMsg($msg, $action, $txt = "")
{
    $action = CAppUI::tr($action);
    if ($msg) {
        CAppUI::setMsg("$action: $msg", UI_MSG_ERROR);
        echo CAppUI::getMsg();

        return;
    }
    CAppUI::setMsg("$action $txt", UI_MSG_OK);
    echo CAppUI::getMsg();
}

$filter = new CSejour();

//récupération de l'identifiant du séjour à fusionner
$sejour_id_merge = CView::post("sejour_id_merge", "ref class|CSejour");
$uf_soins_id     = CView::post("uf_soins_id", "ref class|CUniteFonctionnelle");
$uf_medicale_id  = CView::post("uf_medicale_id", "ref class|CUniteFonctionnelle");
$confirme        = CView::post("confirme", "bool default|0");
$service_id      = CView::post("service_id", "ref class|CService");
$praticien_id    = CView::post("praticien_id", "ref class|CMediusers");
$group_id        = CView::post("group_id", "ref class|CGroups");
$mode_entree     = CView::post("mode_entree", "str");
$duree_prevue    = CView::post("_duree_prevue", "num");
$rpu_id          = CView::post("rpu_id", "ref class|CRPU");
$current_g       = CView::post("current_g", "ref class|CGroups");
$type_pec        = CView::post("type_pec", "str default|" . reset($filter->_specs["type_pec"]->_list));

CView::checkin();

CSejour::$_in_transfert = true;

$rpu = new CRPU();
$rpu->load($rpu_id);

$sejour            = $rpu->loadRefSejour();
$sejour_rpu        = $sejour;
$properties_sejour = $sejour->getProperties();
$current_group     = CGroups::get();

//Cas d'une collision ou d'un rattachement d'un séjour
if ($sejour_id_merge && (!$group_id || ($group_id == $current_g))) {
    $sejour_merge = new CSejour();
    $sejour_merge->load($sejour_id_merge);

    $sejour_merge->entree_reelle  = $sejour->entree_reelle;
    $sejour_merge->mode_entree_id = $sejour->mode_entree_id;
    $sejour_merge->mode_entree    = $sejour->mode_entree;
    $sejour_merge->provenance     = $sejour->provenance;

    foreach ($sejour_merge->loadRefsAffectations() as $_affectation_sejour_merge) {
        $_affectation_sejour_merge->delete();
    }

    $merge_log = CMergeLog::logStart(CUser::get()->_id, $sejour_merge, [$sejour], true);

    // Fusion massive pour passer outre le placement déjà effectué
    try {
        $sejour_merge->merge([$sejour], true, $merge_log);
        $merge_log->logEnd();
        $msg = null;
    } catch (Throwable $t) {
        $merge_log->logFromThrowable($t);
        $msg = $t->getMessage();
    }

    viewMsg($msg, "Fusion");
    $sejour         = $sejour_merge;
    $rpu->sejour_id = $sejour_merge->_id;
}

$create_sejour_hospit = CAppUI::conf("dPurgences create_sejour_hospit");

$sejour->uf_medicale_id = null;
$sejour->loadOldObject();
$sejour->_old->uf_medicale_id = null;
$sejour->makeUF();
$sejour->_NDA     = null;
$sejour->_ref_NDA = null;

// Hospitalisation dans un établissement différent (clôture du séjour d'urgences)
if ($group_id && $group_id != $current_g) {
    if ($sejour_id_merge) {
        $sejour = new CSejour();
        $sejour->load($sejour_id_merge);
    }

    $sejour->loadRefsAffectations();
    foreach ($sejour->_ref_affectations as $_affectation_sejour) {
        if ($_affectation_sejour->_id != $affectation_id_merge) {
            $_affectation_sejour->delete();
        }
    }

    if (!$sejour_id_merge) {
        $sejour->_id      = "";
        $sejour->group_id = $group_id;
        $sejour->type     = "comp";
        $sejour->_old     = null;
    }

    // On vide l'entrée réelle
    $sejour->entree_reelle       = "";
    $sejour->uf_hebergement_id   = "";
    $sejour->uf_soins_id         = "";
    $sejour->uf_medicale_id      = "";
    $sejour->charge_id           = "";
    $sejour->DP                  = '';
    $sejour->praticien_id        = $praticien_id;
    $sejour->type_pec            = $type_pec;
    $sejour->_date_entree_prevue = CMbDT::date();
    $sejour->_hour_entree_prevue = CMbDT::transform(null, null, "%H");
    $sejour->_min_entree_prevue  = CMbDT::transform(null, null, "%M");

    // Ajustement de la sortie prévue si nécessaire
    if ("$sejour->_date_entree_prevue $sejour->_hour_entree_prevue:$sejour->_min_entree_prevue" > $sejour->sortie_prevue) {
        $sejour->_date_sortie_prevue = CMbDT::date("+24 hours", $sejour->_date_entree_prevue);
    }
} // Creation d'un séjour reliquat si demandé
elseif (!$create_sejour_hospit) {
    // Clonage
    $sejour_rpu = new CSejour();
    foreach ($properties_sejour as $name => $value) {
        $sejour_rpu->$name = $value;
    }

    // Forcer le reliquat du séjour en urgences
    $sejour_rpu->type = CAppUI::gconf("dPurgences CRPU type_sejour") === "urg_consult" ? "consult" : "urg";

    // On retire le service pour ne pas créer d'affectation
    $sejour_rpu->service_id = null;

    // Enregistrement
    $sejour_rpu->_id = null;

    // Pas de génération du NDA, et pas de synchro (handler) du séjour
    $sejour_rpu->_generate_NDA   = false;
    $sejour_rpu->_no_synchro     = true;
    $sejour_rpu->_no_synchro_eai = true;
    $msg                         = $sejour_rpu->store();
    viewMsg($msg, "Séjour reliquat enregistré");

    // Transfert du RPU sur l'ancien séjour
    $rpu->sejour_id = $sejour_rpu->_id;
}

$no_synchro = true;
if ($uf_soins_id || $uf_medicale_id || $group_id || $praticien_id || $type_pec || ($duree_prevue && $mode_entree === "6")) {
    if ($praticien_id) {
        $sejour->praticien_id = $praticien_id;
    }

    if ($type_pec) {
        $sejour->type_pec = $type_pec;
    }

    if ($uf_soins_id || $uf_medicale_id || $group_id) {
        $sejour->uf_soins_id    = $uf_soins_id;
        $sejour->uf_medicale_id = $uf_medicale_id;
        $rpu->_uf_medicale_id   = $uf_medicale_id;

        if ($group_id) {
            $sejour->service_id     = $service_id;
            $sejour->group_id       = $group_id;
            $sejour->mode_entree_id = "";
            $sejour->mode_entree    = $mode_entree;
        }
    }

    if ($duree_prevue && $mode_entree === "6") {
        $sejour->_date_sortie_prevue = CMbDT::date("+$duree_prevue DAY");
    }

    $no_synchro = false;
    $msg        = $sejour->store();
    viewMsg($msg, "CSejour-msg-modify");
}

//Cas d'une hospitalisation normale sans collision et sans rattachement
if (!$sejour_id_merge) {
    // On affecte le mode d'entrée et la provenance
    if (CAppUI::conf("dPurgences CRPU mode_entree_provenance_mutation", $sejour->loadRefEtablissement())) {
        $sejour->mode_entree = 8;
        $sejour->provenance  = 5;

        if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_entree")) {
            $mode_entree_sejour           = new CModeEntreeSejour();
            $mode_entree_sejour->code     = "85";
            $mode_entree_sejour->group_id = $sejour->group_id;
            $mode_entree_sejour->actif    = 1;
            $mode_entree_sejour->loadMatchingObject();

            $sejour->mode_entree_id = $mode_entree_sejour->_id;
        }

        $no_synchro = false;
        $msg        = $sejour->store();
        viewMsg($msg, "CSejour-msg-modify");
    }

    // Passage en séjour d'hospitalisation
    $sejour->type = "comp";
    if (!$group_id || $group_id == $current_g) {
        $sejour->_en_mutation = $sejour_rpu->_id;
    }

    // Remise à non du flag UHCD si création d'un séjour reliquat (ou hospitalisation dans un établissement différent)
    if (!$create_sejour_hospit || ($group_id && $group_id != $current_g)) {
        $sejour->UHCD = 0;
    }

    // La synchronisation était désactivée après la sauvegarde du RPU
    $sejour->_no_synchro     = $no_synchro;
    $sejour->_no_synchro_eai = $no_synchro;
    $msg                     = $sejour->store();
    viewMsg($msg, "CSejour-msg-modify");
}

// Modification du RPU
if (!$group_id || ($group_id == $current_g)) {
    $rpu->mutation_sejour_id = $sejour->_id;
}
$rpu->date_sortie_aut  = "now";
$rpu->sortie_autorisee = "1";
$rpu->gemsa            = "4";
$rpu->_transfert_rpu   = true;
$msg                   = $rpu->store();
viewMsg($msg, "CRPU-title-close");

CSejour::$_in_transfert = false;

if ($confirme && !$create_sejour_hospit) {
    CAppUI::js("Sejour.current_group_id = $current_group->_id;");
    CAppUI::js("Sejour.original_group_id = $current_g;");
    CAppUI::js(
        "Admissions.validerSortie($rpu->sejour_id, false, function() {
    Sejour.editModal($sejour->_id, 1);
  }, $current_g)"
    );
} else {
    CAppUI::js("Sejour.original_group_id = $current_g;");
    CAppUI::callbackAjax("Sejour.editModal", $sejour->_id, 1);
}
