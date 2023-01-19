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
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CProtocole;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkRead();
$chir_id     = CView::get("chir_id", "ref class|CMediusers");
$function_id = CView::get("function_id", "ref class|CFunctions");

$tags_to_display  = CView::get("idx_tags", "str");
$exclude_no_index = CView::get("exclude_no_idx", "str") === "true" ? true : false;

if ($tags_to_display !== "") {
    $tags_to_display = explode("|", $tags_to_display);
}

CView::checkin();

$chir = new CMediusers();
$chir->load($chir_id);

if (!$function_id && $chir->_id) {
    $function_id = $chir->function_id;
}

$function = new CFunctions();
$function->load($function_id);

$whereOr = [];
if ($chir->_id) {
    $whereOr[] = "chir_id = {$chir->_id}";
}
if ($function->_id) {
    $whereOr[] = "function_id = {$function->_id}";
}

$protocole = new CProtocole();

/** @var CProtocole[] $protocoles */
$protocoles = [];

$tags_to_protocol = [];

//génération de la liste de tags d'identifiants externes à rechercher
if (is_countable($tags_to_display)) {
    foreach ($tags_to_display as $_tag) {
        $tags_to_protocol[$_tag] = [];
    }
}

$systeme_materiel = CAppUI::gconf("dPbloc CPlageOp systeme_materiel") === "expert";

// Export pour un prat ou cabinet
if ($chir->_id || $function_id) {
    $protocoles = $protocole->loadList(implode(' OR ', $whereOr), "libelle");

    CStoredObject::massLoadFwdRef($protocoles, "chir_id");
    CStoredObject::massLoadFwdRef($protocoles, "function_id");

    if (!$function->_id) {
        $function = $chir->loadRefFunction();
    }
} else {
    // Export global
    $group = CGroups::get();

    // Protocoles des cabinets
    $functions_ids   = $group->loadBackIds("functions");
    $protocoles_func = $protocole->loadList(["function_id" => CSQLDataSource::prepareIn($functions_ids)]);
    CStoredObject::massLoadFwdRef($protocoles_func, "function_id");
    if ($systeme_materiel) {
        $besoins = CStoredObject::massLoadBackRefs($protocoles_func, "besoins_ressources");
        CStoredObject::massLoadFwdRef($besoins, "type_ressource_id");
    }
    foreach ($protocoles_func as $_protocole) {
        $_protocole->loadRefFunction();
    }

    $order_text = CMbArray::pluck($protocoles_func, "_ref_function", "text");
    $order_libelle = array_map("strtolower", CMbArray::pluck($protocoles_func, "libelle"));
    $order_libelle_sejour = array_map("strtolower", CMbArray::pluck($protocoles_func, "libelle_sejour"));
    array_multisort(
        $order_text,
        SORT_ASC,
        $order_libelle,
        SORT_ASC,
        $order_libelle_sejour,
        SORT_ASC,
        $protocoles_func
    );

    // Protocoles des praticiens
    $chir_ids        = $chir->loadIds(["function_id" => CSQLDataSource::prepareIn($functions_ids)]);
    $protocoles_chir = $protocole->loadList(["chir_id" => CSQLDataSource::prepareIn($chir_ids)]);

    CStoredObject::massLoadFwdRef($protocoles_chir, "chir_id");
    if ($systeme_materiel) {
        $besoins = CStoredObject::massLoadBackRefs($protocoles_chir, "besoins_ressources");
        CStoredObject::massLoadFwdRef($besoins, "type_ressource_id");
    }
    foreach ($protocoles_chir as $_protocole) {
        $_protocole->loadRefChir();
    }

    $order_view = CMbArray::pluck($protocoles_chir, "_ref_chir", "_view");
    $order_libelle = array_map("strtolower", CMbArray::pluck($protocoles_chir, "libelle"));
    $order_libelle_sejour = array_map("strtolower", CMbArray::pluck($protocoles_chir, "libelle_sejour"));
    array_multisort(
        $order_view,
        SORT_ASC,
        $order_libelle,
        SORT_ASC,
        $order_libelle_sejour,
        SORT_ASC,
        $protocoles_chir
    );

    $protocoles = array_merge($protocoles_func, $protocoles_chir);
}

//Recherche des identifiants externes correspondant à chaque tag, pour chaque protocole
foreach ($protocoles as $prot_key => $_prot) {
    foreach ($tags_to_protocol as $key => &$_tag) {
        $_that_tag               = new CIdSante400();
        $_that_tag->tag          = $key;
        $_that_tag->object_class = $_prot->_class;
        $_that_tag->object_id    = $_prot->_id;
        $_that_tag->loadMatchingObject();
        if ($_that_tag->id400 !== null) {
            $_tag[$_prot->_id] = $_that_tag;
        } elseif ($exclude_no_index) {
            unset($protocoles[$prot_key]);
        }
    }
}

$csv = new CCSVFile();

$line = [
    CAppUI::tr("CProtocole-Export-Titre-Nom fonction"),
    CAppUI::tr("CProtocole-Export-Titre-Nom praticien"),
    CAppUI::tr("CProtocole-Export-Titre-Prenom praticien"),
    CAppUI::tr("CProtocole-Export-Titre-Libelle intervention"),
    CAppUI::tr("CProtocole-Export-Titre-Libelle sejour"),
    CAppUI::tr("CProtocole-Export-Titre-Duree intervention"),
    CAppUI::tr("CProtocole-Export-Titre-Actes ccam"),
    CAppUI::tr("CProtocole-Export-Titre-Diagnostic"),
    CAppUI::tr("CProtocole-Export-Titre-Type hospitalisation"),
    CAppUI::tr("CProtocole-Export-Titre-Duree hospitalisation"),
    CAppUI::tr("CProtocole-Export-Titre-Duree uscpo"),
    CAppUI::tr("CProtocole-Export-Titre-Duree preop"),
    CAppUI::tr("CProtocole-Export-Titre-Presence preop"),
    CAppUI::tr("CProtocole-Export-Titre-Presence postop"),
    CAppUI::tr("CProtocole-Export-Titre-UF hebergement"),
    CAppUI::tr("CProtocole-Export-Titre-UF medicale"),
    CAppUI::tr("CProtocole-Export-Titre-UF de soins"),
    CAppUI::tr("CProtocole-Export-Titre-Facturable"),
    CAppUI::tr("CProtocole-Export-Titre-RRAC"),
    CAppUI::tr("CProtocole-Export-Titre-Medical"),
    CAppUI::tr("CProtocole-Export-Titre-Extempo"),
    CAppUI::tr("CProtocole-Export-Titre-Cote"),
    CAppUI::tr("CProtocole-Export-Titre-Bilan preop"),
    CAppUI::tr("CProtocole-Export-Titre-Materiel"),
    CAppUI::tr("CProtocole-Export-Titre-Examens perop"),
    CAppUI::tr("CProtocole-Export-Titre-Depassement honoraires"),
    CAppUI::tr("CProtocole-Export-Titre-Forfait clinique"),
    CAppUI::tr("CProtocole-Export-Titre-Fournitures"),
    CAppUI::tr("CProtocole-Export-Titre-Remarques intervention"),
    CAppUI::tr("CProtocole-Export-Titre-Convalescence"),
    CAppUI::tr("CProtocole-Export-Titre-Remarques sejour"),
    CAppUI::tr("CProtocole-Export-Titre-Septique"),
    CAppUI::tr("CProtocole-Export-Titre-Duree heure hospitalisation"),
    CAppUI::tr("CProtocole-Export-Titre-Pathologie"),
    CAppUI::tr("CProtocole-Export-Titre-Type pec"),
    CAppUI::tr("CProtocole-Export-Titre-Hospitalisation de jour"),
    CAppUI::tr("CProtocole-Export-Titre-Service"),
    CAppUI::tr("CProtocole-Export-Titre-Heure entree"),
    CAppUI::tr("CProtocole-Export-Titre-Mode traitement"),
];

if ($systeme_materiel) {
    $line[] = "Besoin";
}

if (CModule::getActive("appFineClient")) {
    $line[] = "Packs de demande (AppFine)";
}

// type de circuit en ambulatoire
$line[] = CAppUI::tr("CProtocole-circuit_ambu");

//ajout des tags d'identifiants externes dans l'en-tête
foreach ($tags_to_protocol as $key => $value) {
    $line[] = $key;
}

$line[] = CAppUI::tr("CProtocole-Export-Titre-Actif");

$csv->writeLine($line);

CStoredObject::massLoadFwdRef($protocoles, "uf_hebergement_id");
CStoredObject::massLoadFwdRef($protocoles, "uf_medicale_id");
CStoredObject::massLoadFwdRef($protocoles, "uf_soins_id");
CStoredObject::massLoadFwdRef($protocoles, "service_id");
CStoredObject::massLoadFwdRef($protocoles, "charge_id");

if (CModule::getActive("appFineClient")) {
    CStoredObject::massLoadBackRefs($protocoles, "pack_appFine");
}

foreach ($protocoles as $_protocole) {
    $_protocole->loadRefUfHebergement();
    $_protocole->loadRefUfMedicale();
    $_protocole->loadRefUfSoins();
    $_protocole->loadRefChir();
    $_protocole->loadRefFunction();
    $_protocole->loadRefService();
    $_protocole->loadRefChargePriceIndicator();

    // On vérifie le protocole est actif ainsi que le praticien ou la fonction
    if (
        $_protocole->actif && (
            ($_protocole->_ref_chir && $_protocole->_ref_chir->actif)
            || ($_protocole->_ref_function && $_protocole->_ref_function->actif)
        )
    ) {
        $_line = [
            $_protocole->_ref_function->text,
            $_protocole->_ref_chir->_user_last_name,
            $_protocole->_ref_chir->_user_first_name,
            $_protocole->libelle,
            $_protocole->libelle_sejour,
            CMbDT::transform($_protocole->temp_operation, null, "%H:%M"),
            $_protocole->codes_ccam,
            $_protocole->DP,
            $_protocole->type,
            $_protocole->duree_hospi,
            $_protocole->duree_uscpo,
            $_protocole->duree_preop ? CMbDT::transform($_protocole->duree_preop, null, "%H:%M") : "",
            $_protocole->presence_preop ? CMbDT::transform($_protocole->presence_preop, null, "%H:%M") : "",
            $_protocole->presence_postop ? CMbDT::transform($_protocole->presence_postop, null, "%H:%M") : "",
            $_protocole->_ref_uf_hebergement->code,
            $_protocole->_ref_uf_medicale->code,
            $_protocole->_ref_uf_soins->code,
            $_protocole->facturable,
            $_protocole->RRAC,
            $_protocole->for_sejour,
            $_protocole->exam_extempo,
            $_protocole->cote,
            $_protocole->examen,
            $_protocole->materiel,
            $_protocole->exam_per_op,
            $_protocole->depassement,
            $_protocole->forfait,
            $_protocole->fournitures,
            $_protocole->rques_operation,
            $_protocole->convalescence,
            $_protocole->rques_sejour,
            $_protocole->septique,
            $_protocole->duree_heure_hospi,
            $_protocole->pathologie,
            $_protocole->type_pec,
            $_protocole->hospit_de_jour,
            $_protocole->_ref_service->_view,
            $_protocole->time_entree_prevue,
            $_protocole->_ref_charge_price_indicator->code,
        ];

        if ($systeme_materiel) {
            $besoins = [];
            foreach ($_protocole->loadRefsBesoins() as $_besoin) {
                $besoins[] = $_besoin->loadRefTypeRessource()->libelle;
            }

            $_line[] = implode("|", $besoins);
        }

        if (CModule::getActive("appFineClient")) {
            $packs = [];
            foreach ($_protocole->loadRefsPacksAppFine() as $_pack) {
                $packs[] = $_pack->pack_id;
            }

            $_line[] = implode("|", $packs);
        }

        // type de circuit en ambulatoire
        $_line[] = $_protocole->circuit_ambu;

        //Ajout de l'identifiant externe (si trouvé) pour chaque tag
        foreach ($tags_to_protocol as $tag) {
            if (isset($tag[$_protocole->_id])) {
                $_line[] = $tag[$_protocole->_id]->id400;
            } else {
                $_line[] = "";
            }
        }

        $_line[] = $_protocole->actif;

        $csv->writeLine($_line);
    }
}

$csv->stream("export-protocoles-" . ($chir_id ? $chir->_view : $function->text));
