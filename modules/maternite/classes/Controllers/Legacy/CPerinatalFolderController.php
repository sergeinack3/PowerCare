<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite\Controllers\Legacy;


use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CDepistageGrossesse;
use Ox\Mediboard\Maternite\CDossierPerinat;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\Maternite\CSurvEchoGrossesse;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CConstantesMedicales;

class CPerinatalFolderController extends CLegacyController
{
    /**
     * Edit the perinatal folder in the light view
     *
     * @throws Exception
     */
    public function ajax_vw_edit_perinatal_folder(): void
    {
        $this->checkPermEdit();
        $grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
        CView::checkin();

        $grossesse = CGrossesse::findOrNew($grossesse_id);
        $patiente  = $grossesse->loadRefParturiente();
        $patiente->loadLastAllaitement();
        $dossier = $grossesse->loadRefDossierPerinat();
        $pere    = $grossesse->loadRefPere();
        $pere->loadRefDossierMedical();
        $grossesse->loadRefsNaissances();
        $last_sejour     = $grossesse->loadLastSejour();
        $last_constantes = CConstantesMedicales::getLatestFor(
            $patiente,
            null,
            ["poids", "taille"],
            $last_sejour,
            false
        );

        $constantes_maman = $dossier->loadRefConstantesAntecedentsMaternels();

        $difference_poids = 0;

        if ($constantes_maman->poids && $last_constantes[0]->poids) {
            $difference_poids = $last_constantes[0]->poids - $constantes_maman->poids;
        }

        $depistages     = $grossesse->loadBackRefs("depistages", "date ASC");
        $last_depistage = end($depistages);

        $depistage = $last_depistage;

        if (!$last_depistage) {
            $last_depistage       = new CDepistageGrossesse();
            $depistage            = new CDepistageGrossesse();
            $last_depistage->date = "now";
            $depistage->date      = "now";
        }

        $tpl_vars = [
            'grossesse'          => $grossesse,
            'last_poids'         => $last_constantes,
            'constantes_maman'   => $constantes_maman,
            'depistage'          => $depistage,
            'last_depistage'     => $last_depistage,
            'naissance'          => new CNaissance(),
            'echographique'      => new CSurvEchoGrossesse(),
            'pathologies_fields' => $dossier->getMotherPathologiesFields(),
        ];

        $this->renderSmarty('inc_vw_edit_perinatal_folder', $tpl_vars);
    }

    /**
     * Show the mother's pathology list
     */
    public function motherPathologiesAutocomplete(): void
    {
        $this->checkPermRead();
        $input_field        = CView::get("input_field", "str");
        $keywords           = CView::get("{$input_field}", "str");
        $dossier_perinat_id = CView::get("dossier_perinat_id", "ref class|CDossierPerinat");
        CView::checkin();

        $dossier = CDossierPerinat::findOrNew($dossier_perinat_id);

        $tpl_vars = [
            'dossier'            => $dossier,
            'pathologies_fields' => $dossier->getMotherPathologiesFields($keywords),
        ];

        $this->renderSmarty('vw_mother_pathologies_autocomplete', $tpl_vars);
    }

    /**
     * Show the mother's pathology list
     */
    public function motherPathologiesTags(): void
    {
        $this->checkPermRead();
        $dossier_perinat_id = CView::get("dossier_perinat_id", "ref class|CDossierPerinat");
        CView::checkin();

        $dossier = CDossierPerinat::findOrNew($dossier_perinat_id);

        $tpl_vars = [
            'dossier'            => $dossier,
            'pathologies_fields' => $dossier->getMotherPathologiesFields(),
        ];

        $this->renderSmarty('dossier_perinatal_light/inc_vw_mother_pathologies_tags', $tpl_vars);
    }

    /**
     * Show the maternity folder of the first contact
     */
    public function dossier_mater_premier_contact(): void
    {
        $this->checkPermEdit();

        $grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
        $print        = CView::get("print", "bool default|0");
        CView::checkin();

        $grossesse = new CGrossesse();
        $grossesse->load($grossesse_id);
        $grossesse->loadRefGroup();
        $grossesse->loadRefPere();
        $grossesse->getDateAccouchement();
        $grossesse->loadRefsNaissances();

        $patient = $grossesse->loadRefParturiente();
        $patient->loadIPP($grossesse->group_id);
        $patient->loadRefsCorrespondants();
        $patient->loadRefsCorrespondantsPatient();

        $dossier = $grossesse->loadRefDossierPerinat();

        if ($dossier->date_premier_contact) {
            $sa_comp  = $grossesse->getAgeGestationnel($dossier->date_premier_contact);
            $age_gest = $sa_comp["SA"];
        }
        else {
            $age_gest = "--";
        }

        $consultations = $grossesse->loadRefsConsultations();
        foreach ($consultations as $consult) {
            $consult->loadRefPraticien();
            $consult->loadRefSuiviGrossesse();
            $consult->getSA();
        }

        // Liste des consultants
        $mediuser        = new CMediusers();
        $listConsultants = $mediuser->loadProfessionnelDeSanteByPref(PERM_EDIT);

        if (!$dossier->consultant_premier_contact_id && in_array(CAppUI::$user->_id, array_keys($listConsultants))) {
            $dossier->consultant_premier_contact_id = CAppUI::$user->_id;
        }

        $this->renderSmarty("dossier_mater_premier_contact", [
            "grossesse"       => $grossesse,
            "age_gest"        => $age_gest,
            "listConsultants" => $listConsultants,
            "print"           => $print,
        ]);
    }
}
