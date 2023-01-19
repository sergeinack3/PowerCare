<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Export;


use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Core\Import\CMbObjectExport;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CCSVImportPatients;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Patient export in CSV format
 */
class CCSVPatientExport
{
    public const EXPORT_HEADER = [
        '_IPP',
        'nom',
        'prenom',
        'naissance',
        'sexe',
        'prenoms',
        'nom_jeune_fille',
        'nom_soundex2',
        'prenom_soundex2',
        'nomjf_soundex2',
        'medecin_traitant_declare',
        'matricule',
        'code_regime',
        'caisse_gest',
        'centre_gest',
        'code_gestion',
        'centre_carte',
        'regime_sante',
        'civilite',
        'adresse',
        'province',
        'is_smg',
        'ville',
        'cp',
        'tel',
        'tel2',
        'tel_autre',
        'email',
        'vip',
        'situation_famille',
        'tutelle',
        'incapable_majeur',
        'ATNC',
        'avs',
        'deces',
        'rques',
        'c2s',
        'ame',
        'ald',
        'code_exo',
        'libelle_exo',
        'deb_amo',
        'fin_amo',
        'notes_amo',
        'notes_amc',
        'rang_beneficiaire',
        'qual_beneficiaire',
        'rang_naissance',
        'fin_validite_vitale',
        'code_sit',
        'regime_am',
        'mutuelle_types_contrat',
        'pays',
        'pays_insee',
        'lieu_naissance',
        'cp_naissance',
        'pays_naissance_insee',
        'profession',
        'csp',
        'status',
        'assure_nom',
        'assure_prenom',
        'assure_prenoms',
        'assure_nom_jeune_fille',
        'assure_sexe',
        'assure_civilite',
        'assure_naissance',
        'assure_adresse',
        'assure_ville',
        'assure_cp',
        'assure_tel',
        'assure_tel2',
        'assure_pays',
        'assure_pays_insee',
        'assure_lieu_naissance',
        'assure_cp_naissance',
        'assure_pays_naissance_insee',
        'assure_profession',
        'assure_rques',
        'assure_matricule',
        'date_lecture_vitale',
        'allow_sms_notification',
        'allow_sisra_send',
        'identifiants_externes',
    ];

    /** @var CGroups */
    private $group;

    /** @var array */
    private $praticien_ids = [];

    /** @var array */
    private $patients = [];

    /** @var string */
    private $file;

    /** @var CCSVFile */
    private $csv;

    /** @var string */
    private $ipp_tag;

    public function __construct(CGroups $group, array $praticien_ids)
    {
        $this->group         = $group;
        $this->praticien_ids = $praticien_ids;
    }

    public function exportPatient(int $patient_id)
    {
        $patient = CPatient::findOrFail($patient_id);
        if (!$patient->getPerm(PERM_READ)) {
            throw new CMbException('common-msg-You are not allowed to access this information (%s)', $patient);
        }

        $this->patients = [$patient->_id => $patient];
        $this->makePatientExport();
    }

    public function doExport(bool $all_prats = false, ?string $date_min = null, ?string $date_max = null): void
    {
        $this->patients = $this->getPatientsToExport($all_prats, $date_min, $date_max);

        $this->makePatientExport();
    }

    private function makePatientExport(): void
    {
        $this->prepareExportFile();

        $this->writeLines();

        $this->closeExportFile();

        $this->sendFile();
        $this->removeTempFile();
    }

    private function getPatientsToExport(bool $all_prats, ?string $date_min = null, ?string $date_max = null): array
    {
        if ($all_prats) {
            $praticiens          = CMbObjectExport::getPraticiensFromGroup();
            $this->praticien_ids = CMbArray::pluck($praticiens, 'user_id');
        }

        [$patients,] = CMbObjectExport::getPatientsToExport($this->praticien_ids, $date_min, $date_max);

        return $patients;
    }

    private function prepareExportFile(): void
    {
        $this->file = tempnam(rtrim(CAppUI::conf('root_dir'), '/\\') . "/tmp", 'export-patients');

        $fp        = fopen($this->file, 'w+');
        $this->csv = new CCSVFile($fp);
        $this->csv->setColumnNames(CCSVPatientExport::EXPORT_HEADER);
        $this->csv->writeLine(CCSVPatientExport::EXPORT_HEADER);
    }

    private function writeLines(): void
    {
        $this->ipp_tag = CPatient::getTagIPP($this->group->_id);

        CPatient::massLoadIPP($this->patients, $this->group->_id);

        /** @var CPatient $_patient */
        foreach ($this->patients as $_patient) {
            $this->csv->writeLine($this->buildLine($_patient));
        }
    }

    private function buildLine(CPatient $patient): array
    {
        $line = [];
        foreach (self::EXPORT_HEADER as $_field) {
            if ($_field === 'identifiants_externes') {
                $line[] = $this->buildIdx($patient);
                continue;
            }

            $line[] = (property_exists($patient, $_field)) ? $patient->$_field : null;
        }

        return $line;
    }

    private function closeExportFile(): void
    {
        $this->csv->close();
    }

    private function sendFile(): void
    {
        // Direct download of the file
        // BEGIN extra headers to resolve IE caching bug (JRP 9 Feb 2003)
        // [http://bugs.php.net/bug.php?id=16173]
        header("Pragma: ");
        header("Cache-Control: ");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");  //HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        // END extra headers to resolve IE caching bug

        header("MIME-Version: 1.0");

        header("Content-disposition: attachment; filename=\"patients-{$this->group->text}.csv\";");
        header("Content-type: text/csv");
        header("Content-length: " . filesize($this->file));

        readfile($this->file);
    }

    private function removeTempFile(): void
    {
        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }

    private function buildIdx(CPatient $patient): string
    {
        $idx = [];

        $ds    = $patient->getDS();
        // Use the NULL-safe operator rather then an OR clause for null
        $where = [
            $ds->prepare('NOT `tag` <=> ?', $this->ipp_tag),
        ];

        /** @var CIdSante400 $id400 */
        foreach ($patient->loadBackRefs('identifiants', 'id_sante400_id ASC', null, null, null, null, '', $where) as $id400) {
            $idx[] = $id400->id400 . ($id400->tag ? ('|' . $id400->tag) : null);
        }

        return implode(CCSVImportPatients::EXTERNAL_IDS_SEPARATOR, $idx);
    }
}
