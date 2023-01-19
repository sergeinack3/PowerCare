<?php

/**
 * @package Mediboard\Ccam\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam\Tests\Unit;

use Ox\Core\CSQLDataSource;
use Ox\Tests\OxUnitTestCase;

/**
 * Class permettant de tester la base NGAP
 */
class NGAPDatabaseTest extends OxUnitTestCase
{
    /** @var array An array of the error */
    protected $errors = [];

    /**
     * Teste la conformité de la base NGAP pour toutes les spécialités
     *
     * @return void
     */
    public function testNGAPActsPrice(): void
    {
        $this->markTestSkipped('TODO Reactivate the test after the 2022-03-31');
        $specialities = NGAPData::getSpecialities();

        foreach ($specialities as $speciality) {
            $this->checkNGAPActsPriceForSpeciality($speciality);
        }

        $this->assertFalse($this->hasErrors(), $this->report());
    }

    /**
     * Teste la conformité de la base NGAP pour une spécialité
     *
     * @param integer $speciality The speciality number
     *
     * @return void
     */
    protected function checkNGAPActsPriceForSpeciality(int $speciality): void
    {
        $acts = NGAPData::getActsForSpeciality($speciality);

        $ds = CSQLDataSource::get('ccamV2');

        foreach ($acts as $act) {
            $query = "SELECT t.`tarif` FROM `tarif_ngap` as t
        LEFT JOIN `specialite_to_tarif_ngap` as s ON s.`tarif_id` = t.`tarif_ngap_id`
        WHERE t.`zone` = 'metro' AND s.specialite = $speciality AND t.`code` = '{$act['code']}'
        AND (t.fin IS NULL OR t.fin >= '2022-04-01') AND (t.debut IS NULL OR t.debut <= '2022-04-01');";

            $result = $ds->exec($query);

            if (!$result) {
                $this->addError($speciality, $act['code'], "Code non disponible pour la spécialité");
                continue;
            }

            if ($ds->numRows($result) > 1) {
                $this->addError($speciality, $act['code'], 'Plusieurs entrées en base pour le code et la spécialité');
                continue;
            }

            $row = $ds->fetchAssoc($result);
            if ($act['price'] != $row['tarif']) {
                $this->addError(
                    $speciality,
                    $act['code'],
                    "Prix en base non conforme. Attendu : {$act['price']}, Actuel : {$row['tarif']}"
                );
            }
        }
    }

    /**
     * Ajoute une erreur dans la pile d'erreurs
     *
     * @param int     $speciality Le numéro de spécialité
     * @param string  $code       Le code NGAP
     * @param string  $error      Le message d'erreur
     *
     * @return void
     */
    protected function addError(int $speciality, string $code, string $error): void
    {
        $this->errors[] = ['speciality' => $speciality, 'code' => $code, 'error' => $error];
    }

    /**
     * Vérifie si il y a eu des erreurs générées
     *
     * @return bool
     */
    protected function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    /**
     * Construit le rapport d'erreur
     *
     * @return string
     */
    protected function report(): string
    {
        $report = count($this->errors) . " erreurs détectées :\n";

        foreach ($this->errors as $error) {
            $report .= "Spé {$error['speciality']}, Code {$error['code']} : {$error['error']}\n";
        }

        return $report;
    }
}
