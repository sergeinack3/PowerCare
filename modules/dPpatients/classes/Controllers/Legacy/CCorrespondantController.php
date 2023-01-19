<?php

/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbException;
use Ox\Core\CMbString;
use Ox\Core\CView;
use Ox\Import\Rpps\CMedecinExercicePlaceManager;
use Ox\Import\Rpps\Entity\CAbstractExternalRppsObject;
use Ox\Import\Rpps\Entity\CMssanteInfos;
use Ox\Import\Rpps\Entity\CPersonneExercice;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CExercicePlace;
use Ox\Mediboard\Patients\CMedecin;

class CCorrespondantController extends CLegacyController
{
    /**
     * Open the search modal
     *
     * @throws Exception
     */
    public function openCorrespondantImportFromRPPSModal(): void
    {
        $this->checkPermRead();

        $correspondant = new CMedecin();
        $this->renderSmarty('inc_form_find_correspondant', ['medecin' => $correspondant]);
    }

    /**
     * Retrieve the list of correspondant matching the fields
     *
     * @throws Exception
     */
    public function retrieveMatchingCorrespondantFromRPPS(): void
    {
        $_START = 0;
        $_STEP  = 20;
        $_LIMIT = 1000;
        $this->checkPermRead();

        $correspondant_nom    = CView::get("nom", "str");
        $correspondant_prenom = CView::get("prenom", "str");
        $correspondant_type   = CView::get("type", "str");
        $correspondant_cp     = CView::get("cp", "str");
        $correspondant_ville  = CView::get("ville", "str");
        $rpps                 = CView::get("rpps", "numchar");

        // pagination
        $start = CView::get("start", 'numchar') ?? $_START;
        $step  = CView::get("step", 'numchar') ?? $_STEP;

        CView::checkin();

        $correspondant = new CMedecin();
        $ds            = $correspondant->getDS();

        $where = [];

        if ($correspondant_nom) {
            /* We use stripslashes because if the string contains quotes, they can already be escaped,
             * and if they are, the call to prepare() will fucked it up by adding more slashes */
            $where["nom"] = CMbString::removeDiacritics($ds->prepareLike(stripslashes("$correspondant_nom%")));
        }

        if ($correspondant_prenom) {
            $where["prenom"] = CMbString::removeDiacritics($ds->prepareLike(stripslashes("$correspondant_prenom%")));
        }

        if ($correspondant_type) {
            $where["code_profession"] = $ds->prepare('= ?', array_search($correspondant_type, CMedecin::$types));
        }

        if ($correspondant_cp) {
            $where["cp"] = $ds->prepareLike("$correspondant_cp%");
        }

        if ($correspondant_ville) {
            $where["libelle_commune"] = CMbString::removeDiacritics(
                stripslashes($ds->prepareLike("$correspondant_ville%"))
            );
        }

        if ($rpps && strlen($rpps) === 11) {
            $where["identifiant"] = $ds->prepare('= ?', $rpps);
            unset($where["nom"]);
            unset($where["prenom"]);
            unset($where["code_profession"]);
            unset($where["cp"]);
            unset($where["libelle_commune"]);
        } else {
            unset($where["identifiant"]);
        }

        if (empty($where)) {
            CAppUI::stepMessage(UI_MSG_WARNING, "CCorrespondant-warning fill at least one field");

            return;
        }

        $where["type_identifiant"] = $ds->prepare('= ?', CAbstractExternalRppsObject::TYPE_IDENTIFIANT_RPPS);

        $personne                = new CPersonneExercice();
        $order                   = "nom, prenom";
        $count_personne_exercice = $personne->countList($where) > $_LIMIT ? $_LIMIT : $personne->countList($where);
        $personnes               = $personne->loadList($where, $order, "$start, $step");
        $correspondants          = [];

        // Gestion du cloisonnement utilisateur
        $current_user = CMediusers::get();
        $function_id  = null;
        $group_id     = null;

        if (CAppUI::isCabinet()) {
            $function_id = $current_user->function_id;
        } elseif (CAppUI::isGroup()) {
            $group_id = CGroups::loadCurrent()->_id;
        }

        foreach ($personnes as $_personne_exercice) {
            /** @var CMedecin $medecin */
            $medecin       = $_personne_exercice->synchronize();
            $medecin->type = $_personne_exercice->code_profession ?
                CMedecin::$types[$_personne_exercice->code_profession] : null;

            $medecin->_alreadyImported = false;
            $medecin->_id              = null;

            if ($medecin->loadFromRPPS($_personne_exercice->identifiant, $function_id, $group_id)->_id) {
                $medecin->_alreadyImported = true;
            }

            $exercicePlace                 = new CExercicePlace();
            $exercicePlace->adresse        = $_personne_exercice->buildAdresse();
            $exercicePlace->cp             = $_personne_exercice->cp;
            $exercicePlace->commune        = $_personne_exercice->libelle_commune;
            $exercicePlace->raison_sociale = $_personne_exercice->raison_sociale_site;

            $correspondants[$medecin->rpps]['medecin'][]        = $medecin;
            $correspondants[$medecin->rpps]['disciplines'][]    = $_personne_exercice->code_savoir_faire ?
                $_personne_exercice->code_savoir_faire . ' : '
                . $_personne_exercice->libelle_savoir_faire : null;
            $correspondants[$medecin->rpps]['exercicePlaces'][] = $exercicePlace;
        }

        $this->renderSmarty(
            'inc_list_rpps_correspondant',
            [
                "correspondants"    => $correspondants,
                "nb_correspondants" => $count_personne_exercice,
                "start"             => $start,
                "step"              => $step,
            ]
        );
    }

    /**
     * Create a CMedecin from a CPersonneExercice, create the associated CExercicePlace if needed and sync the
     * CMedecinExercicePlace
     *
     * @throws CMbException
     * @throws Exception
     */
    public function addCorrespondantFromRPPS(): void
    {
        $this->checkPermRead();

        $selectedCorrespondants = CView::get("medecins", "str");

        CView::checkin();

        $nbCorrespondants = 0;

        foreach ($selectedCorrespondants as $correspondant_id) {
            $this->synchronizeMedecinAndExercicePlace($correspondant_id);
            $nbCorrespondants++;
        }

        if ($nbCorrespondants < 2) {
            CAppUI::stepAjax(
                "$nbCorrespondants " . CAppUI::tr(
                    "mod-dPpatients-tab-openCorrespondantImportFromRPPSModal-msg-add correspondant success.one"
                )
            );
        } else {
            CAppUI::stepAjax(
                "$nbCorrespondants " . CAppUI::tr(
                    "mod-dPpatients-tab-openCorrespondantImportFromRPPSModal-msg-add correspondant success.some"
                )
            );
        }

        CAppUI::stepAjax(
            CAppUI::tr("mod-dPpatients-tab-openCorrespondantImportFromRPPSModal-msg-Synchronize exercicePlace success")
        );
    }

    /**
     * Update a CMedecin from a CPersonneExercice, update the associated CExercicePlace if needed and sync the
     * CMedecinExercicePlace
     *
     * @throws CMbException
     * @throws Exception
     */
    public function updateCorrespondant(): void
    {
        $this->checkPermRead();

        $selectedCorrespondant = CView::get("rpps", "str");

        CView::checkin();

        $this->synchronizeMedecinAndExercicePlace($selectedCorrespondant);

        CAppUI::stepAjax(
            CAppUI::tr("mod-dPpatients-tab-openCorrespondantImportFromRPPSModal-msg-Update correspondant success")
        );
        CAppUI::stepAjax(
            CAppUI::tr("mod-dPpatients-tab-openCorrespondantImportFromRPPSModal-msg-Synchronize exercicePlace success")
        );
    }

    /**
     * Return the exercice place of the correspondant
     *
     * @param CExercicePlace $correspondant Correspondant
     *
     * @return CExercicePlace|string
     * @throws Exception
     */
    private function loadExercicePlace(CPersonneExercice $correspondant)
    {
        $existingExercicePlace = new CExercicePlace();
        $exercicePlace         = new CExercicePlace();
        $ds                    = $exercicePlace->getDS();
        $where                 = [];

        if ($correspondant->hashIdentifier()) {
            $where["exercice_place_identifier"] = $ds->prepare('= ?', $correspondant->hashIdentifier());
            if ($where) {
                $existingExercicePlace->loadObject($where);
            }
        }

        $where = [];

        if ($correspondant->siret_site) {
            $where["siret"] = $ds->prepare('= ?', $correspondant->siret_site);
        }

        if ($correspondant->siren_site) {
            $where["siren"] = $ds->prepare('= ?', $correspondant->siren_site);
        }

        if ($correspondant->finess_site) {
            $where["finess"] = $ds->prepare('= ?', $correspondant->finess_site);
        }

        if ($correspondant->finess_etab_juridique) {
            $where["finess_juridique"] = $ds->prepare('= ?', $correspondant->finess_etab_juridique);
        }

        if ($correspondant->id_technique_structure) {
            $where["id_technique"] = $ds->prepare('= ?', $correspondant->id_technique_structure);
        }

        if ($correspondant->raison_sociale_site) {
            $where["raison_sociale"] = $ds->prepare('= ?', $correspondant->raison_sociale_site);
        }

        if ($where) {
            $exercicePlace->loadObject($where);

            if ($existingExercicePlace->_id && $existingExercicePlace->_id === $exercicePlace->_id) {
                return $existingExercicePlace;
            } else {
                return $correspondant->updateOrCreatePlace($exercicePlace);
            }
        }

        return null;
    }

    /**
     * Synchronize the medecin and the associated exercice places
     *
     * @param int $rpps
     *
     * @return CExercicePlace|string
     * @throws Exception
     */
    public function synchronizeMedecinAndExercicePlace(int $rpps): void
    {
        $correspondant = new CPersonneExercice();
        $medecin       = new CMedecin();

        $where = [];

        // Gestion du cloisonnement utilisateur
        $current_user = CMediusers::get();
        $function_id  = null;
        $group_id     = null;

        if (CAppUI::isCabinet()) {
            $function_id = $current_user->function_id;
        } elseif (CAppUI::isGroup()) {
            $group_id = CGroups::loadCurrent()->_id;
        }

        $medecin->loadFromRPPS($rpps, $function_id, $group_id);

        $ds = $correspondant->getDS();

        $where['identifiant'] = $ds->prepare('= ?', $rpps);
        $correspondants       = $correspondant->loadList($where);

        $mssante_adress              = new CMssanteInfos();
        $mssante_adress->identifiant = $rpps;
        $mssante_adresses            = $mssante_adress->loadMatchingList();

        foreach ($correspondants as $_correspondant) {
            $medecin              = $_correspondant->synchronize($medecin);
            $medecin->function_id = $function_id ?? null;
            $medecin->group_id    = $group_id ?? null;

            // Gestion du cas des séparations par cabinets où on ajoute un médecin en tant qu'administrateur
            if (!$medecin->_id && $medecin->group_id) {
                $medecin->enableImporting();
            }

            $res = $medecin->store();
            if (!is_null($res)) {
                CAppUI::stepAjax("$res", UI_MSG_ERROR);
            }

            $exercicePlace = $this->loadExercicePlace($_correspondant);

            $medecin_exercice_place = $_correspondant->synchronizeExercicePlace($medecin, $exercicePlace);

            if (!empty($mssante_adresses)) {
                $_correspondant->addMSSanteAddress(
                    $medecin_exercice_place,
                    $medecin,
                    $mssante_adresses
                );
            }

            $manager = new CMedecinExercicePlaceManager();
            $manager->removeBadMatchingMedecinExercicePlace($medecin);
        }
    }

    /**
     * @throws Exception
     */
    public function checkIfMedecinIsUpdatable(int $existing_medecin_id, CMedecin $medecin): bool
    {
        $existing_medecin = new CMedecin();
        $existing_medecin->load($existing_medecin_id);

        return !$existing_medecin->equals($medecin);
    }

    /**
     * @throws Exception
     */
    public function checkIfExercicePlaceIsUpdatable(int $existingExercicePlaceId, CExercicePlace $exercicePlace): bool
    {
        $existing_exercice_place = new CExercicePlace();
        $existing_exercice_place->load($existingExercicePlaceId);

        return !$existing_exercice_place->equals($exercicePlace);
    }

    /**
     * @throws Exception
     */
    public function checkIfMSSanteInfoIsUpdatable(int $existingMSSanteInfoId, CMssanteInfos $mssante_infos): bool
    {
        $existing_mssante_infos = new CMssanteInfos();
        $existing_mssante_infos->load($existingMSSanteInfoId);

        return !$existing_mssante_infos->equals($mssante_infos);
    }
}
