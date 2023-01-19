<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Création d'un dossier de naissance
 */
$naissance_id      = CValue::post("naissance_id");
$operation_id      = CValue::post("operation_id");
$patient_id        = CValue::post("patient_id");
$praticien_id      = CValue::post("praticien_id");
$hors_etab         = CValue::post("hors_etab");
$sexe              = CValue::post("sexe");
$heure             = CValue::post("_heure");
$date_time         = CValue::post("date_time");
$rang              = CValue::post("rang");
$date_naissance    = CValue::post("naissance");
$nom               = CValue::post("nom");
$prenom            = CValue::post("prenom");
$prenoms           = CValue::post("prenoms");
$lieu_naissane     = CValue::post("lieu_naissance");
$commune_naissance = CValue::post("commune_naissance_insee");
$cp_naissance      = CValue::post("cp_naissance");
$pays_naissance    = CValue::post("_pays_naissance_insee");
$poids             = CValue::post("_poids_g");
$taille            = CValue::post("taille");
$num_naissance     = CValue::post("num_naissance");
$num_semaines      = CValue::post("num_semaines");
$interruption      = CValue::post("interruption");
$rques             = CValue::post("rques");
$type_allaitement  = CValue::post("type_allaitement");
$constantes_id     = CValue::post("constantes_medicales_id");
$sejour_maman_id   = CValue::post("sejour_maman_id");
$perimetre_cranien = CValue::post("perimetre_cranien");
$by_caesarean      = CValue::post("by_caesarean");
$callback          = CValue::post("callback");
$service_id        = CValue::post("service_id");
$uf_id             = CValue::post("uf_soins_id");
$charge_id         = CValue::post("charge_id");
$bebe_id           = CValue::post("bebe_id");
$provisoire        = CValue::post("provisoire");
$uf_medicale_id    = CValue::post("uf_medicale_id");
$tutelle           = CValue::post("tutelle");

$sejour = new CSejour();
$sejour->load($sejour_maman_id);
$parturiente = $sejour->loadRefPatient();
$grossesse   = $sejour->loadRefGrossesse();
$curr_affect = $sejour->loadRefCurrAffectation();

$datetime = CMbDT::dateTime();
$date     = CMbDT::date($datetime);

$group = CGroups::get();

/**
 * Fonction utilitaire pour la sauvegarde rapide d'un object avec génération du message
 *
 * @param CMbObject $object Objet à enregister
 *
 * @return void
 */
function storeObject($object)
{
    $title = $object->_id ? "-msg-modify" : "-msg-create";
    if ($msg = $object->store()) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
        echo CAppUI::getMsg();

        return;
        // Il peut y avoir un msg de retour postérieur à la création de l'objet
        // On continue donc le processus de création de la naissance
        //CApp::rip();
    }

    CAppUI::setMsg(CAppUI::tr($object->_class . $title), UI_MSG_OK);
}

// Six étapes pour la création de la naissance :
//   1. Créer le nouveau patient (enfant)
//   2. Créer le séjour de l'enfant
//   3. Créer la naissance
//   4. Restore du séjour si pas provisoire
//   5. Créer l'affectation du séjour
//   6. Créer le relevé de constantes

if (!$naissance_id) {
    // Ne pas pouvoir créer une naissance avant l'entrée du séjour de la maman
    if (!$hors_etab && !$provisoire && CMbDT::dateFromLocale($date_naissance) . " $heure" < $sejour->entree) {
        CAppUI::setMsg("CNaissance-hors_bornes", UI_MSG_ERROR);

        echo CAppUI::getMsg();
        CApp::rip();
    }

    // Etape 1 (patient)
    $patient = new CPatient();
    if ($bebe_id) {
        $patient->load($bebe_id);
    }
    $patient->nom                     = $nom ? $nom : $parturiente->nom;
    $patient->prenom                  = $prenom ?: "provisoire";
    $patient->sexe                    = $sexe ?: null;
    $patient->civilite                = "enf";
    $patient->naissance               = $date_naissance;
    $patient->tutelle                 = $tutelle;
    $patient->prenoms                 = $prenoms;
    $patient->lieu_naissance          = $lieu_naissane;
    $patient->cp_naissance            = $cp_naissance;
    $patient->_pays_naissance_insee   = $pays_naissance;
    $patient->commune_naissance_insee = $commune_naissance;
    // Indispensable pour indiquer aux handlers que l'on est dans le cas d'une naissance
    $patient->_naissance = true;
    // Ajout du sejour_id de la maman pour qu'à la création dans l'interop. on est l'info.
    $patient->_sejour_maman_id = $sejour_maman_id;

    $fields = ["adresse", "ville", "cp"];
    foreach ($fields as $_field) {
        $patient->$_field = $parturiente->$_field;
    }

    storeObject($patient);

    // Etape 2 (séjour)
    $sejour_enfant                = new CSejour();
    $sejour_enfant->entree_prevue = $provisoire ? CMbDT::dateTime() : CMbDT::dateFromLocale(
            $date_naissance
        ) . " $heure";
    if (!$provisoire) {
        $sejour_enfant->entree_reelle = $sejour_enfant->entree_prevue;
    }
    $sejour_enfant->sortie_prevue = $curr_affect->sortie ? $curr_affect->sortie : $sejour->sortie;
    $sejour_enfant->patient_id    = $patient->_id;
    $sejour_enfant->praticien_id  = $praticien_id ?: $sejour->praticien_id;
    $sejour_enfant->group_id      = $sejour->group_id;
    $sejour_enfant->libelle       = CAppUI::tr("CNaissance");

    // Indispensable pour indiquer aux handlers que l'on est dans le cas d'une naissance
    $sejour_enfant->_naissance           = true;
    $sejour_enfant->_apply_sectorisation = false;
    $sejour_enfant->service_id           = $service_id ?: $sejour->service_id;
    $sejour_enfant->uf_medicale_id       = $uf_medicale_id;
    $sejour_enfant->mode_entree          = "N";
    if ($uf_id) {
        $sejour_enfant->uf_soins_id = $uf_id;
    }
    if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_entree")) {
        $mode_entree           = new CModeEntreeSejour();
        $mode_entree->code     = "N";
        $mode_entree->group_id = $sejour_enfant->group_id;
        $mode_entree->actif    = 1;
        $mode_entree->loadMatchingObject();

        $sejour_enfant->mode_entree_id = $mode_entree->_id;
    }
    $sejour_enfant->charge_id = $charge_id;
    // Ajout du sejour_id de la maman pour qu'à la création dans l'interop. on est l'info.
    $sejour_enfant->_sejour_maman_id = $sejour_maman_id;
    storeObject($sejour_enfant);

    // Etape 3 (naissance)
    $naissance                   = new CNaissance();
    $naissance->sejour_maman_id  = $sejour_maman_id;
    $naissance->sejour_enfant_id = $sejour_enfant->_id;
    $naissance->operation_id     = $operation_id;
    $naissance->grossesse_id     = $grossesse->_id;
    $naissance->rang             = $rang;
    $naissance->_heure           = $heure;
    if (!$provisoire) {
        $naissance->date_time = CMbDT::dateFromLocale($date_naissance) . " $heure";
    }
    $naissance->hors_etab        = $hors_etab;
    $naissance->num_naissance    = $num_naissance;
    $naissance->num_semaines     = $num_semaines;
    $naissance->interruption     = $interruption;
    $naissance->rques            = $rques;
    $naissance->type_allaitement = $type_allaitement;
    $naissance->by_caesarean     = $by_caesarean;
    storeObject($naissance);

    // Etape 4 : on met maintenant l'entrée réelle puisque la naissance est créée
    if (!$provisoire) {
        $sejour_enfant->_naissance    = false;
        $sejour_enfant->entree_reelle = CMbDT::dateFromLocale($date_naissance) . " $heure";
        storeObject($sejour_enfant);
    }

    // Etape 5 (affectation)
    // Checker si l'affectation de la maman existe
    if ($curr_affect->_id) {
        $affectation             = $sejour_enfant->loadRefFirstAffectation();
        $affectation->entree     = $sejour_enfant->entree;
        $affectation->sortie     = $sejour_enfant->sortie;
        $affectation->lit_id     = $curr_affect->lit_id;
        $affectation->service_id = $curr_affect->service_id;
        if ($uf_id) {
            $affectation->uf_soins_id = $uf_id;
        }
        $affectation->sejour_id             = $sejour_enfant->_id;
        $affectation->parent_affectation_id = $curr_affect->_id;
        storeObject($affectation);
    }

    // Etape 6 (constantes)
    if ($poids || $taille || $perimetre_cranien) {
        $constantes                    = new CConstantesMedicales();
        $constantes->patient_id        = $patient->_id;
        $constantes->datetime          = "now";
        $constantes->_poids_g          = $poids;
        $constantes->taille            = $taille;
        $constantes->perimetre_cranien = $perimetre_cranien;
        $constantes->_object_guid      = $naissance->_guid;
        $constantes->_object_field     = 'nouveau_ne_constantes_id';
        $constantes->context_class     = $sejour_enfant->_class;
        $constantes->context_id        = $sejour_enfant->_id;

        storeObject($constantes);
    }
} // Modification d'une naissance
else {
    $validation_naissance = false;
    $naissance            = new CNaissance();
    $naissance->load($naissance_id);
    $naissance->rang             = $rang;
    $naissance->hors_etab        = $hors_etab;
    $naissance->num_naissance    = $num_naissance;
    $naissance->num_semaines     = $num_semaines;
    $naissance->interruption     = $interruption;
    $naissance->rques            = $rques;
    $naissance->type_allaitement = $type_allaitement;
    $naissance->by_caesarean     = $by_caesarean;
    $naissance->sejour_maman_id  = $sejour_maman_id;
    $naissance->grossesse_id     = $sejour->grossesse_id;

    if (!$naissance->date_time && $heure) {
        $validation_naissance    = true;
        $naissance->operation_id = $operation_id;
    }

    $naissance->date_time = CMbDT::dateFromLocale($date_naissance) . ' ' . $heure;

    storeObject($naissance);

    $sejour = $naissance->loadRefSejourEnfant();

    $patient = new CPatient();
    $patient->load($sejour->patient_id);
    $patient->nom                     = $nom;
    $patient->prenom                  = $prenom;
    $patient->nom                     = $nom;
    $patient->prenoms                 = $prenoms;
    $patient->lieu_naissance          = $lieu_naissane;
    $patient->cp_naissance            = $cp_naissance;
    $patient->_pays_naissance_insee   = $pays_naissance;
    $patient->commune_naissance_insee = $commune_naissance;
    $patient->sexe                    = $sexe;
    $patient->naissance               = $date_naissance;
    storeObject($patient);

    $sejour_enfant = new CSejour();
    $sejour_enfant->load($naissance->sejour_enfant_id);
    $sejour_enfant->praticien_id         = $praticien_id;
    $sejour_enfant->_naissance           = true;
    $sejour_enfant->_apply_sectorisation = false;
    $sejour_enfant->service_id           = $service_id;
    $sejour_enfant->uf_soins_id          = $uf_id;
    $sejour_enfant->uf_medicale_id       = $uf_medicale_id;
    storeObject($sejour_enfant);

    // Effectuer l'admission si nécessaire (si issu d'un dossier provisoire)
    if ($validation_naissance) {
        // L'entrée réelle du séjour à la date et heure de naissance
        $sejour_enfant->entree_reelle = $naissance->date_time;
        storeObject($sejour_enfant);

        // Checker également si l'affectation de la maman existe
        // Et dans ce cas, la créer pour le bébé
        if ($curr_affect->_id) {
            $affectation = $sejour_enfant->loadRefCurrAffectation();

            if (!$affectation->_id) {
                $affectation                        = new CAffectation();
                $affectation->entree                = $sejour_enfant->entree_reelle;
                $affectation->sortie                = $sejour_enfant->sortie_prevue;
                $affectation->service_id            = $curr_affect->service_id;
                $affectation->lit_id                = $curr_affect->lit_id;
                $affectation->sejour_id             = $sejour_enfant->_id;
                $affectation->parent_affectation_id = $curr_affect->_id;
                $affectation->uf_soins_id           = $uf_id;
                storeObject($affectation);
            }
        }
    }

    if ($poids || $taille || $perimetre_cranien) {
        $constantes = new CConstantesMedicales();
        $constantes->load($constantes_id);
        $constantes->_poids_g          = $poids;
        $constantes->taille            = $taille;
        $constantes->perimetre_cranien = $perimetre_cranien;

        // Depuis un dossier provisoire, les constantes médicales ne sont pas créées.
        if (!$constantes->_id) {
            $constantes->context_class = $sejour_enfant->_class;
            $constantes->context_id    = $sejour_enfant->_id;
            $constantes->patient_id    = $patient->_id;
            $constantes->datetime      = CMbDT::dateTime();
            $constantes->_object_guid  = $naissance->_guid;
            $constantes->_object_field = 'nouveau_ne_constantes_id';
        }

        storeObject($constantes);
    }
}
echo CAppUI::getMsg();

if ($callback) {
    CAppUI::callbackAjax($callback);
}

CApp::rip();
