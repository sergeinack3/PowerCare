<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Services;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use Exception;
use Ox\Core\CMbDT;
use Ox\Mediboard\Patients\BloodSugarDayAdministrationsReport;
use Ox\Mediboard\Patients\BloodSugarDayConstantsReport;
use Ox\Mediboard\Patients\BloodSugarDayReport;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;

/**
 * Identity qualification service
 */
class ConstantesService
{
    private ?DateTime $date_min = null;
    private ?DateTime $date_max = null;
    private bool     $choose_unit;

    /**
     * @param CSejour $sejour
     *
     * @return BloodSugarDayReport[]
     * @throws Exception
     */
    public function getBloodSugarReport(CSejour $sejour): array
    {
        $blood_sugar_reports = [];
        $date_min            = $this->date_min ? $this->date_min->format('Y-m-d') : null;
        $date_max            = $this->date_max ? $this->date_max->format('Y-m-d') : null;

        // Get the prescription of the stay
        $prescription = $this->getPreparedPrescriptionFromSejour($sejour);

        // Get constants
        $constantes_medicales = $this->getPreparedConstantesFromSejour($sejour);

        // Get prescription lines which are related to A10A
        // MEDICAMENTS DU DIABETE (A10) / INSULINES ET ANALOGUES (A10A)
        $A10A_lines = $this->getDiabetesLinesFromPrescription($prescription);

        $midnight_enter = (new DateTimeImmutable($sejour->entree))->setTime(0, 0);

        $nb_days = 4;
        $day     = new DateTimeImmutable();
        if ($date_min && $date_max) {
            $day     = new DateTimeImmutable($date_max);
            $nb_days = CMbDT::daysRelative($date_min, $date_max);
        }

        for ($i = 0; $i <= $nb_days; $i++) {
            $midnight_date = (clone $day)->setTime(0, 0);
            if ($midnight_date < $midnight_enter) {
                break;
            }
            // Prepare all administrations related to blood sugar of the day
            $blood_administrations = [];
            foreach ($A10A_lines as $_line) {
                if (
                    $day < new DateTimeImmutable($_line->_debut_reel)
                    || $day > new DateTimeImmutable($_line->_fin)
                    || $midnight_date > new DateTimeImmutable($_line->date_arret)
                ) {
                    continue;
                }

                $_line->_ref_produit->updateRatioMassique();

                $blood_administrations_line = new BloodSugarDayAdministrationsReport($_line);

                foreach ($_line->_ref_administrations as $_administration) {
                    if (
                        ($date_min && ($_administration->dateTime < $date_min))
                        || ($date_max && $_administration->dateTime > $date_max)
                    ) {
                        continue;
                    }

                    // If the administration isn't cancelled
                    if ($_administration->quantite <= 0 || $_administration->planification) {
                        continue;
                    }

                    $_administration->loadRefPrise(false);
                    $ratio_ui = $_line->_ref_produit->_ratio_UI;

                    $qte_ui                                    = ($ratio_ui && $ratio_ui > 0) ? round(
                        $_administration->quantite / $ratio_ui,
                        2
                    ) : '-';
                    $_administration->_ref_prise->_quantite_UI = $qte_ui;

                    $date = new DateTime($_administration->dateTime);

                    if ($date->format('Y-m-d') === $day->format('Y-m-d')) {
                        $blood_administrations_line->add($_administration);
                    }
                }
                $blood_administrations[] = $blood_administrations_line;
            }

            // Prepare all blood sugar constants of the day
            $day_constants = new BloodSugarDayConstantsReport();
            foreach ($constantes_medicales as $_constant) {
                if (!$_constant->glycemie) {
                    continue;
                }

                $constant_date = new DateTime($_constant->datetime);

                if ($constant_date->format('Y-m-d') === $day->format('Y-m-d')) {
                    $day_constants->add($_constant);
                }
            }

            // Make a blood sugar report of the day
            $blood = new BloodSugarDayReport($day, $day_constants, $blood_administrations);

            // Add the blood sugar report to a collection
            $blood_sugar_reports[] = $blood;

            $day = $day->sub(new DateInterval("P1D"));
        }

        return $this->sortReports($blood_sugar_reports);
    }

    /**
     * @param array $blood_sugar_reports
     *
     * @return BloodSugarDayReport[]
     */
    private function sortReports(array $blood_sugar_reports): array
    {
        // Sort the blood sugar collection by date
        usort($blood_sugar_reports, function ($a, $b) {
            return $b->getDate() <=> $a->getDate();
        });

        return $blood_sugar_reports;
    }

    /**
     * @param CSejour $sejour
     *
     * @return CPrescription
     */
    private function getPreparedPrescriptionFromSejour(CSejour $sejour): CPrescription
    {
        $prescription = $sejour->loadRefPrescriptionSejour();
        $prescription->loadRefsLinesMed(true, true);

        return $prescription;
    }

    /**
     * @param CSejour $sejour
     *
     * @return CConstantesMedicales[]
     */
    private function getPreparedConstantesFromSejour(CSejour $sejour): array
    {
        $sejour->loadRefConstantes();
        $constantes_medicales = $sejour->_ref_suivi_medical;

        return CConstantesMedicales::getConvertUnitGlycemie(
            $constantes_medicales,
            false,
            $this->choose_unit
        );
    }

    private function getDiabetesLinesFromPrescription(CPrescription $prescription): array
    {
        $A10A_lines = [];
        foreach ($prescription->_ref_prescription_lines as $_line) {
            if ($_line->_ref_produit->_ref_ATC_3_code === 'A10A') {
                $_line->loadRefsAdministrations();

                $A10A_lines[] = $_line;
            }
        }

        return $A10A_lines;
    }

    /**
     * @param DateTime|null $date_min
     * @param DateTime|null $date_max
     *
     * @return ConstantesService
     */
    public function betweenDates(?DateTime $date_min = null, ?DateTime $date_max = null): ConstantesService
    {
        $this->date_min = $date_min;
        $this->date_max = $date_max;

        return $this;
    }

    /**
     * @param bool $choose
     *
     * @return ConstantesService
     */
    public function withChooseUnit(bool $choose): ConstantesService
    {
        $this->choose_unit = $choose;

        return $this;
    }
}
