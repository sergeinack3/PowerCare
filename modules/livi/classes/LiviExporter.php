<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Livi;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbPath;
use Ox\Core\CMbPDFMerger;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Patients\CPatient;
use ZipArchive;
use Exception;

/**
 * Classe d'export pour le module Livi
 */
class LiviExporter implements IShortNameAutoloadable
{
    /** @var array */
    private $patients_livi_ids = [];

    /** @var CSQLDataSource */
    private $ds;

    /**
     * LiviExporter constructor.
     *
     * @param array $patients_livi_ids
     *
     * @throws CMbException
     */
    public function __construct(array $patients_livi_ids)
    {
        if (!count($patients_livi_ids)) {
            throw new CMbException("common-msg-No patient found.");
        }

        foreach ($patients_livi_ids as $patients_livi_id) {
            if (!self::isValidUuid($patients_livi_id)) {
                throw new CMbException("LiviExporter-error-Invalid id format");
            }
        }

        $this->patients_livi_ids = $patients_livi_ids;
        $this->ds                = CSQLDataSource::get("std");
    }

    /**
     * Récupération des identifiants livi du fichier uploadé
     *
     * @param CCSVFile $csv
     *
     * @return static
     * @throws CMbException
     */
    public static function fromCsv(CCSVFile $csv): self
    {
        $patients_livi_ids = [];
        $csv->jumpLine(1);

        while ($data = $csv->readLine()) {
            if (count($data) !== 1) {
                throw new CMbException("common-error-Invalid format");
            }
            $patients_livi_ids[] = reset($data);
        }

        return new self($patients_livi_ids);
    }

    /**
     * Chargement des patients à partir des identifiants récupérés
     *
     * @return CPatient[]
     * @throws CMbException
     */
    private function loadPatients(): array
    {
        // Les identifiants Livi sont stockés dans le champ "ville" des patients
        $where = [
            "ville" => $this->ds->prepareIn($this->patients_livi_ids),
        ];
        // Récupération des patients
        $patients_livi = (new CPatient())->loadList($where);

        if (!count($patients_livi)) {
            throw new CMbException("common-msg-No patient found.");
        }

        return $patients_livi;
    }

    /**
     * Génération du fichier pdf pour un patient pour une période
     *
     * @param CPatient $patient
     * @param string   $date_debut
     * @param string   $date_fin
     *
     * @return string
     * @throws Exception
     */
    private function generatePdf(CPatient $patient, string $date_debut, string $date_fin): string
    {
        $tmp_files     = [];
        $pdfmerger     = new CMbPDFMerger();
        $where_consult = [
            "plageconsult.date" => $this->ds->prepareBetween($date_debut, $date_fin),
        ];
        // Chargement des références pour le fetch des différents templates
        $patient->loadRefsConsultations($where_consult);
        $patient->loadRefLastVerrouDossier();
        $patient->loadRefMedecinTraitant();
        $patient->canDo();
        $dossier = $patient->loadRefDossierMedical(false);
        $dossier->countAllergies();
        $dossier->countAntecedents(false);
        $patient->loadRefLatestConstantes();
        // On charge les antécédents non présents
        $atcd_absence           = $dossier->loadRefsAntecedents(false, false, false, false, 1);
        $count_allergie_absence = 0;
        foreach ($atcd_absence as $_all_absence) {
            if ($_all_absence->type == 'alle') {
                $count_allergie_absence++;
            }
        }
        // On charge les antécédents
        $dossier->loadRefsAntecedents(false, false);
        $dossier->loadRefsAllergies();
        $dossier->loadRefsTraitements();
        $dossier->loadRefsPathologies();
        $dossier->loadRefsEvenementsPatient();
        foreach ($dossier->_ref_pathologies as $pathologie) {
            $pathologie->loadRefCim10();
        }
        foreach ($dossier->_ref_evenements_patient as $event) {
            $event->loadRefPraticien();
        }
        $dossier->loadRefPrescription();

        // Synthèse médicale
        $smarty = new CSmartyDP('modules/oxCabinet');
        $smarty->assign("patient_id", $patient->_id);
        $smarty->assign("date", CMbDT::date());
        $smarty->assign("patient", $patient);
        $smarty->assign("full_age", $patient->getRestAge());
        $smarty->assign("edit_mode", 0);
        $smarty->assign("sort_by_date", true);
        $smarty->assign("count_allergie_absence", 0);
        $smarty->assign("rosp_year", 0);
        $smarty->assign("mailing", 0);
        $smarty->assign("print", 1);
        $smarty->assign("view_mode", 0);
        $smarty->assign("countRelatives", 0);
        $smarty->assign("count_allergie_absence", $count_allergie_absence);
        $smarty->assign("atcd_absence", $atcd_absence);
        $smarty->assign("dossier_medical", $dossier);
        $content_synthese = $smarty->fetch("vw_synthese_medicale");

        $file         = new CFile();
        $synthese_pdf = tempnam("./tmp", "pdf_synthese");

        $compte_rendu               = new CCompteRendu();
        $compte_rendu->_page_format = "A4";
        $compte_rendu->_orientation = "portrait";

        $content = $this->generateHTML($content_synthese);

        $htmltopdf = new CHtmlToPDF();
        $pdf       = $htmltopdf->generatePDF($content, false, $compte_rendu, $file, false, false);
        file_put_contents($synthese_pdf, $pdf);
        $pdfmerger->addPDF($synthese_pdf);

        foreach ($patient->_ref_consultations as $consultation) {
            $consultation->loadRefPatient();
            $consultation->loadRefPraticien();
            $consultation->loadRefPlageConsult();
            $consultation->loadRefsFiles();

            // Fiche examen
            $smarty = new CSmartyDP('modules/dPcabinet');
            $smarty->assign("consult", $consultation);
            $smarty->assign("patient", $patient);
            $content_examen = $smarty->fetch("print_examen");

            $file        = new CFile();
            $consult_pdf = tempnam("./tmp", "pdf_consult");

            $compte_rendu               = new CCompteRendu();
            $compte_rendu->_page_format = "A4";
            $compte_rendu->_orientation = "portrait";

            // Fichiers
            $content = $this->generateHTML($content_examen);

            $htmltopdf = new CHtmlToPDF();

            $pdf = $htmltopdf->generatePDF($content, false, $compte_rendu, $file, false, false);
            file_put_contents($consult_pdf, $pdf);
            $pdfmerger->addPDF($consult_pdf);
            $tmp_files[] = $consult_pdf;

            foreach ($consultation->_ref_files as $_file) {
                if ($_file->_file_type === "pdf") {
                    $pdfmerger->addPDF($_file->_file_path);
                } elseif ($_file->isImage()) {
                    $img_to_pdf = tempnam("./tmp", "livi");
                    file_put_contents(
                        $img_to_pdf,
                        CFile::mergeBase64Pictures([base64_encode($_file->getBinaryContent())])
                    );
                    $pdfmerger->addPDF($img_to_pdf);
                    $tmp_files[] = $img_to_pdf;
                }
            }
        }
        // Finalisation du fichier pdf
        $file_merge = tempnam("./tmp", "pdf_patient") . ".pdf";
        $pdfmerger->merge("file", $file_merge, false, true);

        foreach ($tmp_files as $_tmp_file) {
            unlink($_tmp_file);
        }

        return $file_merge;
    }

    /**
     * Export des données patient sur une période
     *
     * @param string $date_debut
     * @param string $date_fin
     *
     * @return string
     * @throws CMbException
     */
    public function toZip(string $date_debut, string $date_fin): string
    {
        if (CMbDT::date($date_debut) > CMbDT::date($date_fin)) {
            throw new CMbException("Invalid");
        }

        $patients_livi = $this->loadPatients();
        // Création du zip
        $zip      = new ZipArchive();
        $zip_name = "./tmp/export_patient_livi.zip";

        if (file_exists($zip_name)) {
            CMbPath::remove($zip_name);
        }
        $zip->open($zip_name, ZipArchive::CREATE);

        $consultations = CMbObject::massLoadBackRefs($patients_livi, "consultations");

        CMbObject::massLoadFwdRef($consultations, "plageconsult_id");
        CMbObject::massLoadFwdRef($consultations, "patient_id");
        CMbObject::massLoadFwdRef($consultations, "owner_id");

        foreach ($patients_livi as $patient) {
            $file = $this->generatePdf($patient, $date_debut, $date_fin);
            $zip->addFile(
                $file,
                $patient->_view . ".pdf"
            );
        }
        $zip->close();

        return $zip_name;
    }

    /**
     * Génération du contenu HTML pour les documents pdf
     *
     * @param string $content
     *
     * @return string
     * @throws Exception
     */
    private function generateHTML(string $content): string
    {
        $css_content = file_get_contents(CAppUI::conf("base_url") . "/style/mediboard_ext/standard.css");

        $smarty = new CSmartyDP("modules/livi");

        $smarty->assign("css_content", $css_content);
        $smarty->assign("content", $content);

        return $smarty->fetch("print_content");
    }

    /**
     * Vérification du format des identifiants récupérés
     *
     * @param mixed $uuid
     *
     * @return bool
     */
    private static function isValidUuid($uuid): bool
    {
        if (
            !is_string($uuid)
            || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1)
        ) {
            return false;
        }

        return true;
    }
}
