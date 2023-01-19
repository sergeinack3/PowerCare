<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Ccam\CFilterCotation;
use Ox\Mediboard\Mediusers\CMediusers;

class TdbSaisieCodages
{
    private const CSV_FILENAME_PREFIX = "export-intervention_non_cotes-";

    public const CSV_LINE_HEADER = [
        "Praticiens",
        "Patient",
        "Evènement",
        "Actes Non cotés",
        "Codes prévus",
        "Actes cotés",
    ];

    private int $total_seances_non_cotees = 0;

    private int $total_sejours_non_cotes = 0;

    private int $total_consultations_non_cotees = 0;

    private int $total_operations_non_cotees = 0;

    private array $filters;

    private CFilterCotation $filter;

    private CMediusers $user;

    private array $sejours = [];

    private array $consultations = [];

    private array $interventions = [];

    private array $seances = [];

    private array $total = [];

    private array $exported_sejours = [];

    private array $exported_consult = [];

    private array $exported_operation = [];

    private bool $all_prats = true;

    public function __construct(
        CMediusers $mediuser,
        array $filters,
        bool $all_prats = false
    ) {
        $this->filters   = $filters;
        $this->filter    = new CFilterCotation($filters);
        $this->all_prats = $all_prats;

        $this->user = $mediuser;
    }


    /**
     * @throws Exception
     */
    public function loadObjetsNonCotes(): void
    {
        $objects = $this->filter->getCotationDetails();

        if (array_key_exists('COperation', $objects)) {
            $this->interventions = $objects['COperation'];
        }

        /* Calcul du nombre d'interventions sans codes pour les 3 derniers mois */
        if (!$this->all_prats) {
            if ($this->user->_id) {
                $chirs = [$this->user->_id];
            } else {
                $this->user = CMediusers::get();
                $chirs      = array_keys($this->user->loadPraticiens());
            }

            $this->total_operations_non_cotees = CFilterCotation::countUncodedCodable(
                $this->user,
                'COperation',
                $chirs
            );
        }

        if (array_key_exists('CConsultation', $objects)) {
            $this->consultations = $objects['CConsultation'];
        }

        /* Calcul du nombre d'interventions sans codes pour les 3 derniers mois */
        if (!$this->all_prats) {
            $this->total_consultations_non_cotees = CFilterCotation::countUncodedCodable($this->user, 'CConsultation');
        }

        if (array_key_exists('CSejour', $objects)) {
            $this->sejours = $objects['CSejour'];
        }

        /* Calcul du nombre de séjours sans codes pour les 3 derniers mois */
        if (!$this->all_prats) {
            $this->total_sejours_non_cotes = CFilterCotation::countUncodedCodable($this->user, 'CSejour');
        }

        if (array_key_exists('CSejour-seance', $objects)) {
            $this->seances = $objects['CSejour-seance'];
        }

        /* Calcul du nombre de séjours de type séance sans codes pour les 3 derniers mois */
        if (!$this->all_prats) {
            $this->total_seances_non_cotees = CFilterCotation::countUncodedCodable($this->user, 'CSejour-seance');
        }

        $this->total = $objects["totals"];
    }

    /**
     * @throws Exception
     */
    public function exportToCsv(bool $stream = true): void
    {
        $csv = $this->generateCsvFile();

        $this->exportSejours($csv);
        $this->exportConsultations($csv);
        $this->exportInterventions($csv);

        if ($stream) {
            $csv->stream(self::CSV_FILENAME_PREFIX . $this->filters["begin_date"] . "-" . $this->filters["end_date"]);
        }
    }

    private function generateCsvFile(): CCSVFile
    {
        $csv = new CCSVFile();
        /** @var array $line */
        $line = self::CSV_LINE_HEADER;
        if (!$this->all_prats) {
            unset($line[0]);
        }
        $csv->writeLine($line);

        return $csv;
    }

    private function exportSejours(CCSVFile $csv): void
    {
        foreach ($this->sejours as $_sejour) {
            $line = [];

            if ($this->all_prats) {
                $line[] = $_sejour->_ref_chir->_view;
            }

            $line[] = $_sejour->_ref_patient->_view;

            $line[] = $_sejour->_view;

            $line[] = (!$_sejour->_count_actes && !$_sejour->_ext_codes_ccam)
                ? CAppui::tr("None")
                : $_sejour->_actes_non_cotes . "acte(s)";

            $actes = "";
            foreach ($_sejour->_ext_codes_ccam as $code) {
                $actes .= $actes == "" ? "" : "\n";
                $actes .= "$code->code";
            }
            $line[] = $actes;

            $actes_cotes = "";
            $code        = "";
            foreach ($_sejour->_ref_actes_ccam as $_acte) {
                $code .= $actes_cotes == "" ? "" : "\n";
                $code .= $_acte->code_acte . "-" . $_acte->code_activite . "-" . $_acte->code_phase;
                if ($_acte->modificateurs) {
                    $code .= " MD:" . $_acte->modificateurs;
                }
                if ($_acte->montant_depassement) {
                    $code .= " DH:" . $_acte->montant_depassement;
                }
                $actes_cotes .= "$code";
            }
            $code = "";
            foreach ($_sejour->_ref_actes_ngap as $_acte) {
                $code .= $actes_cotes == "" ? "" : "\n";
                if ($_acte->quantite > 1) {
                    $actes_cotes .= "$_acte->quantite x ";
                }
                $actes_cotes .= $_acte->code;
                if ($_acte->coefficient != 1) {
                    $actes_cotes .= " ($_acte->coefficient)";
                }
                if ($_acte->complement) {
                    $actes_cotes .= " $_acte->complement";
                }
                $actes_cotes .= " $_acte->_tarif";
            }
            $line[] = $actes_cotes;

            $csv->writeLine($line);

            $this->exported_sejours[] = $line;
        }
    }

    /**
     * @throws Exception
     */
    private function exportConsultations(CCSVFile $csv): void
    {
        foreach ($this->consultations as $_consult) {
            $line = [];

            if ($this->all_prats) {
                $line[] = $_consult->_ref_chir->_view;
            }

            $line[] = $_consult->_ref_patient->_view;

            $view = "Consultation le " . CMbDT::format($_consult->_datetime, CAppUI::conf("date"));
            if ($_consult->_ref_sejour && $_consult->_ref_sejour->libelle) {
                $view .= $_consult->_ref_sejour->libelle;
            }
            $line[] = $view;

            $line[] = (!$_consult->_count_actes && !$_consult->_ext_codes_ccam)
                ? CAppui::tr("None")
                : $_consult->_actes_non_cotes . "acte(s)";

            $actes = "";
            foreach ($_consult->_ext_codes_ccam as $code) {
                $actes .= $actes == "" ? "" : "\n";
                $actes .= "$code->code";
            }
            $line[] = $actes;

            $actes_cotes = "";
            $code        = "";
            foreach ($_consult->_ref_actes_ccam as $_acte) {
                $code .= $actes_cotes == "" ? "" : "\n";
                $code .= $_acte->code_acte . "-" . $_acte->code_activite . "-" . $_acte->code_phase;
                if ($_acte->modificateurs) {
                    $code .= " MD:" . $_acte->modificateurs;
                }
                if ($_acte->montant_depassement) {
                    $code .= " DH:" . $_acte->montant_depassement;
                }
                $actes_cotes .= "$code";
            }
            $code = "";
            foreach ($_consult->_ref_actes_ngap as $_acte) {
                $code .= $actes_cotes == "" ? "" : "\n";
                if ($_acte->quantite > 1) {
                    $actes_cotes .= "$_acte->quantite x ";
                }
                $actes_cotes .= $_acte->code;
                if ($_acte->coefficient != 1) {
                    $actes_cotes .= " ($_acte->coefficient)";
                }
                if ($_acte->complement) {
                    $actes_cotes .= " $_acte->complement";
                }
                $actes_cotes .= " $_acte->_tarif";
            }
            $line[] = $actes_cotes;

            $csv->writeLine($line);

            $this->exported_consult[] = $line;
        }
    }

    private function exportInterventions(CCSVFile $csv): void
    {
        foreach ($this->interventions as $_interv) {
            $line = [];
            if ($this->all_prats) {
                $chir = $_interv->_ref_chir->_view;
                if ($_interv->_ref_anesth->_id) {
                    $chir .= "\n" . $_interv->_ref_anesth->_view;
                }
                $line[] = $chir;
            }
            $line[] = $_interv->_ref_patient->_view;

            $interv = $_interv->_view;
            if ($_interv->_ref_sejour->libelle) {
                $interv .= "\n" . $_interv->_ref_sejour->libelle;
            }
            if ($_interv->libelle) {
                $interv .= "\n" . $_interv->libelle;
            }
            $line[] = $interv;

            $line[] = (!$_interv->_count_actes && !$_interv->_ext_codes_ccam)
                ? CAppui::tr("None")
                : $_interv->_actes_non_cotes . "acte(s)";

            $actes = "";
            foreach ($_interv->_ext_codes_ccam as $code) {
                $actes .= $actes == "" ? "" : "\n";
                $actes .= "$code->code";
            }
            $line[] = $actes;

            $actes_cotes = "";
            $code        = "";
            foreach ($_interv->_ref_actes_ccam as $_acte) {
                $code .= $actes_cotes == "" ? "" : "\n";
                $code .= $_acte->code_acte . "-" . $_acte->code_activite . "-" . $_acte->code_phase;
                if ($_acte->modificateurs) {
                    $code .= " MD:" . $_acte->modificateurs;
                }
                if ($_acte->montant_depassement) {
                    $code .= " DH:" . $_acte->montant_depassement;
                }
                $actes_cotes .= "$code";
            }
            $line[] = $actes_cotes;

            $csv->writeLine($line);

            $this->exported_operation[] = $line;
        }
    }

    /**
     * @return array
     */
    public function getInterventions(): array
    {
        return $this->interventions;
    }

    /**
     * @return array
     */
    public function getConsultations(): array
    {
        return $this->consultations;
    }

    /**
     * @return array
     */
    public function getSejours(): array
    {
        return $this->sejours;
    }

    /**
     * @return int
     */
    public function getTotal(): array
    {
        return $this->total;
    }

    public function getSeances(): array
    {
        return $this->seances;
    }

    /**
     * @return int
     */
    public function getTotalOperationsNonCotees(): int
    {
        return $this->total_operations_non_cotees;
    }

    /**
     * @return int
     */
    public function getTotalSeancesNonCotees(): int
    {
        return $this->total_seances_non_cotees;
    }

    /**
     * @return int
     */
    public function getTotalSejoursNonCotes(): int
    {
        return $this->total_sejours_non_cotes;
    }

    /**
     * @return int
     */
    public function getTotalConsultationsNonCotees(): int
    {
        return $this->total_consultations_non_cotees;
    }

    /**
     * @return array
     */
    public function getExportedOperation(): array
    {
        return $this->exported_operation;
    }

    /**
     * @return array
     */
    public function getExportedConsult(): array
    {
        return $this->exported_consult;
    }

    /**
     * @return array
     */
    public function getExportedSejours(): array
    {
        return $this->exported_sejours;
    }
}
