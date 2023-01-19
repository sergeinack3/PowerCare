<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CStoredObject;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CExchangeSource;

/**
 * Export class for RPUs
 */
class ExportRPU implements IShortNameAutoloadable
{
    /** @var CExchangeSource|CSourceFTP */
    private static $source;

    /** @var string */
    private const SOURCE_NAME = "Export_RPU";
    /** @var string */
    private const FILE_NAME = "E0037_URGENCESMB";

    /**
     * Returns the source set to send documents
     *
     * @param null|CExchangeSource $source
     *
     * @return CExchangeSource|CSourceFTP
     */
    public static function getSource(?CExchangeSource $source = null): CExchangeSource
    {
        if (self::$source) {
            return self::$source;
        }

        return self::$source = $source ?: CExchangeSource::get(
            self::SOURCE_NAME,
            null,
            true,
            null,
            true
        );
    }

    /**
     * RPU Datas Export in CSV
     *
     * @param int $last_days Last days number
     *
     * @return void
     * @throws Exception
     */
    public static function exportDatas(?int $last_days = null): void
    {
        $last_days  = $last_days ?: 3;
        $begin_date = CMbDT::date("- {$last_days} DAYS");

        // Load groups with emergency service
        $group  = new CGroups();
        $groups = $group->loadList(["service_urgences_id" => "IS NOT NULL"], "text ASC");

        foreach ($groups as $_group) {
            $csv      = new CCSVFile(null, CCSVFile::PROFILE_EXCEL);
            $datetime = CMbDT::format(CMbDT::dateTime(), "%Y%m%d-%H%M%S");

            $header = [
                CAppUI::tr("ExportRPU-finess"),
                "",
                CAppUI::tr("ExportRPU-INS number"),
                CAppUI::tr("ExportRPU-Patient postal code"),
                CAppUI::tr("ExportRPU-Patient age"),
                CAppUI::tr("ExportRPU-NDA number"),
                CAppUI::tr("ExportRPU-File number in the emergency management tool"),
                CAppUI::tr("ExportRPU-UFM code"),
                CAppUI::tr("ExportRPU-Average arrival code"),
                CAppUI::tr("ExportRPU-Average arrival"),
                CAppUI::tr("ExportRPU-Origin code"),
                CAppUI::tr("ExportRPU-Origin"),
                CAppUI::tr("ExportRPU-Arrival date and time"),
                CAppUI::tr("ExportRPU-Start date of medical treatment"),
                CAppUI::tr("ExportRPU-Medical decision date"),
                CAppUI::tr("ExportRPU-Exit Date and Time"),
                CAppUI::tr("ExportRPU-Transit time"),
                CAppUI::tr("ExportRPU-Traumatology"),
                CAppUI::tr("ExportRPU-Status"),
                CAppUI::tr("ExportRPU-Previous hospitalization"),
                CAppUI::tr("ExportRPU-Code Addressed by"),
                CAppUI::tr("ExportRPU-Title Addressed by"),
                CAppUI::tr("ExportRPU-Pick-up date"),
                CAppUI::tr("ExportRPU-Has imaging exams"),
                CAppUI::tr("ExportRPU-Has MRI exams"),
                CAppUI::tr("ExportRPU-Has Echo exams"),
                CAppUI::tr("ExportRPU-Has x-ray exams"),
                CAppUI::tr("ExportRPU-Has scan exams"),
                CAppUI::tr("ExportRPU-Has specialist opinions"),
                CAppUI::tr("ExportRPU-Specialist number"),
                CAppUI::tr("ExportRPU-Specialist date request"),
                CAppUI::tr("ExportRPU-Specialist date comment"),
                CAppUI::tr("ExportRPU-Has biology exams"),
                CAppUI::tr("ExportRPU-Date request radio"),
                CAppUI::tr("ExportRPU-Date Achievement radio"),
                CAppUI::tr("ExportRPU-Title radio"),
                CAppUI::tr("ExportRPU-CCMU code"),
                CAppUI::tr("ExportRPU-CIM code"),
                CAppUI::tr("ExportRPU-CIM title"),
                CAppUI::tr("ExportRPU-Prescriptions made in the service"),
                CAppUI::tr("ExportRPU-Discharge prescriptions"),
                CAppUI::tr("ExportRPU-Type orientation"),
                CAppUI::tr("ExportRPU-Destination orientation"),
                CAppUI::tr("ExportRPU-GEMSA orientation"),
                CAppUI::tr("ExportRPU-Reason for transfer orientation"),
                CAppUI::tr("ExportRPU-Reconvened on or in"),
                CAppUI::tr("ExportRPU-SFMU category"),
                CAppUI::tr("ExportRPU-Code category"),
                CAppUI::tr("ExportRPU-Title category"),
                CAppUI::tr("ExportRPU-IPP"),
                CAppUI::tr("ExportRPU-RPU sent"),
                CAppUI::tr("ExportRPU-Date RPU sent"),
            ];

            $csv->setColumnNames($header);
            $csv->writeLine($header);

            $rpu = new CRPU();
            $ds  = $rpu->getDS();

            $where = [
                "rpu.sejour_id"   => $ds->prepare("IS NOT NULL"),
                "sejour.annule"   => $ds->prepare("= '0'"),
                "sejour.entree"   => $ds->prepare(">= ?", $begin_date),
                "sejour.group_id" => $ds->prepare("= ?", $_group->_id),
            ];

            $ljoin = [
                "sejour" => "sejour.sejour_id = rpu.sejour_id",
            ];

            $order = "entree ASC";

            /** @var CRPU[] $rpus */
            $rpus = $rpu->loadList($where, $order, null, null, $ljoin);

            $finess_etablissement = $_group->finess;

            $sejours = CStoredObject::massLoadFwdRef($rpus, 'sejour_id');
            CStoredObject::massLoadBackRefs(
                $sejours,
                "prescriptions",
                null,
                ["type" => $ds->prepareIn(["sejour", "sortie"])]
            );
            $patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
            CSejour::massLoadNDA($sejours);
            CPatient::massLoadIPP($patients);
            CStoredObject::massLoadFwdRef($sejours, "uf_medicale_id");
            CStoredObject::massLoadFwdRef($rpus, "motif_sfmu");
            CStoredObject::massLoadBackRefs($rpus, "attentes_rpu");
            $consultations = CStoredObject::massLoadBackRefs($sejours, "consultations");
            CStoredObject::massLoadFwdRef($consultations, "adresse_par_prat_id");

            foreach ($rpus as $_rpu) {
                $sejour = $_rpu->loadRefSejour();
                $sejour->loadRefUFMedicale();
                $consult_urg     = $_rpu->loadRefConsult();
                $adresse_medecin = $consult_urg->loadRefAdresseParPraticien();
                $patient         = $sejour->loadRefPatient();
                $sejour->loadExtDiagnostics();
                $prescription = $sejour->loadRefsPrescriptions();
                $patient->evalAge();
                $ins_number          = $patient->getINSNIR();
                $motif_sfmu          = $_rpu->loadRefMotifSFMU();
                $counter_rpu_attente = self::counterRpuAttentes($_rpu);
                $first_rpu_attentes  = $_rpu->loadRefsFirstAttentes();
                $circonstance        = $_rpu->loadRefCirconstance();
                $_rpu->loadFirstAndLastPassages();

                $line = [
                    $finess_etablissement,
                    null,
                    $ins_number,
                    $patient->cp,
                    $patient->_annees,
                    $sejour->_NDA,
                    $sejour->_NDA,
                    $sejour->_ref_uf_medicale->code,
                    $sejour->transport,
                    $sejour->transport ? CAppUI::tr("CSejour.transport.$sejour->transport") : null,
                    $sejour->mode_entree,
                    $sejour->mode_entree ? CAppUI::tr("CSejour.mode_entree.$sejour->mode_entree") : null,
                    self::checkDatetime($sejour->entree),
                    self::checkDatetime($consult_urg->creation_date),
                    self::checkDatetime($_rpu->date_sortie_aut),
                    self::checkDatetime($sejour->sortie),
                    CMbDT::durationSecond($_rpu->_entree, $_rpu->_sortie),
                    ($motif_sfmu->categorie == "Traumatologie") ? "O" : "N",
                    ($motif_sfmu->categorie == "Traumatologie") ? "Chir" : "Med",
                    null,
                    $adresse_medecin->rpps,
                    $adresse_medecin->_view,
                    self::checkDatetime($_rpu->pec_ioa),
                    $counter_rpu_attente['all'] ? "O" : "N",
                    $counter_rpu_attente['irm'] ? "O" : "N",
                    $counter_rpu_attente['echo'] ? "O" : "N",
                    $counter_rpu_attente['classic'] ? "O" : "N",
                    $counter_rpu_attente['scanner'] ? "O" : "N",
                    $counter_rpu_attente['specialiste'] ? "O" : "N",
                    $counter_rpu_attente['specialiste'],
                    self::checkDatetime($first_rpu_attentes["specialiste"]->demande),
                    null,
                    $counter_rpu_attente['bio'] ? "O" : "N",
                    self::checkDatetime($first_rpu_attentes["radio"]->demande),
                    self::checkDatetime($first_rpu_attentes["radio"]->retour),
                    null,
                    $_rpu->ccmu,
                    $sejour->DP,
                    $sejour->_ext_diagnostic_principal,
                    $prescription["sejour"]->_id ? "O" : "N",
                    $prescription["sortie"]->_id ? "O" : "N",
                    $_rpu->orientation ? CAppUI::tr("CRPU.orientation.$_rpu->orientation") : null,
                    $_rpu->_destination ? CAppUI::tr("CRPU._destination.$_rpu->_destination") : null,
                    $_rpu->gemsa,
                    null,
                    null,
                    $motif_sfmu->categorie,
                    $circonstance->code,
                    $circonstance->libelle,
                    $patient->_IPP,
                    ($_rpu->_last_extract_passages && $_rpu->_last_extract_passages->_id) ? "O" : "N",
                    ($_rpu->_last_extract_passages && $_rpu->_last_extract_passages->_id) ? CMbDT::format(
                        $_rpu->_last_extract_passages->date_extract,
                        "%d/%m/%Y %H:%M"
                    ) : null,
                ];

                $csv->writeLine($line);
            }

            $filename = self::FILE_NAME . $finess_etablissement . "_" . $datetime;

            self::sendFile($csv->getContent(), $filename);
        }
    }

    /**
     * Counter RPU attentes by type
     *
     * @param CRPU $rpu
     *
     * @return array
     */
    public static function counterRpuAttentes(CRPU $rpu): array
    {
        $rpu_attentes        = $rpu->loadRefsAttentes();
        $rpu_attente         = new CRPUAttente();
        $counter_rpu_attente = [];

        foreach ($rpu_attente->_specs["type_attente"]->_list as $_prop) {
            $counter_rpu_attente[$_prop] = 0;
        }

        foreach ($rpu_attente->_specs["type_radio"]->_list as $_prop) {
            $counter_rpu_attente[$_prop] = 0;
        }

        $counter_rpu_attente["all"] = count($rpu_attentes);

        foreach ($rpu_attentes as $_rpu_attente) {
            $counter_rpu_attente[$_rpu_attente->type_attente] += 1;

            if ($_rpu_attente->type_radio) {
                $counter_rpu_attente[$_rpu_attente->type_radio] += 1;
            }
        }

        return $counter_rpu_attente;
    }

    /**
     * Return the datetime value is not empty
     *
     * @param string $field_datetime
     *
     * @return string|null
     */
    public static function checkDatetime(?string $field_datetime = null): ?string
    {
        if ($field_datetime) {
            return CMbDT::format($field_datetime, "%d/%m/%Y %H:%M");
        }

        return null;
    }

    /**
     * File sending via the configured source
     *
     * @param string $file_content Contenu du fichier
     * @param string $filename     Nom du fichier
     *
     * @throws CMbException
     */
    public static function sendFile(string $file_content, string $filename): void
    {
        $source = self::getSource();
        $source->setData($file_content);

        try {
            if (!$source->_id) {
                throw new Exception("ExportRPU-No source");
            }

            $source->getClient()->send($filename);
        } catch (Exception $e) {
            throw new CMbException($e->getMessage());
        }
    }
}
