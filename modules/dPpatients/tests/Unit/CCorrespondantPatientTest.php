<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit;

use Ox\Core\CMbDT;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CModelObjectException;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\Tests\Fixtures\SimplePatientFixtures;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CCorrespondantPatientTest extends OxUnitTestCase {
  /**
   * @param string $cp CP to test
   *
   * @config dPpatients INSEE france 1
   * @config dPpatients INSEE suisse 1
   * @config dPpatients INSEE allemagne 1
   * @config dPpatients INSEE espagne 1
   * @config dPpatients INSEE portugal 1
   * @config dPpatients INSEE gb 1
   *
   * @dataProvider cpProvider
   */
  public function testCPSize($cp) {
    $cp_fields = ['cp'];

    $correspondant = new CCorrespondantPatient();
    foreach ($cp_fields as $_cp) {
      $correspondant->{$_cp} = $cp;
    }

    $correspondant->repair();

    foreach ($cp_fields as $_cp) {
      $this->assertEquals($cp,
        $correspondant->{$_cp});
    }
  }

  public function cpProvider() {
    return array(
      ["3750-012"], ["12"], ["17000"], ["6534887"]
    );
  }

    /**
     * @throws TestsException
     * @throws CModelObjectException
     * @throws CMbModelNotFoundException
     * @dataProvider tutellePatientProvider
     */
    public function testCreateCorrespondantTutelleChangePatientTutelleValue(string $expected, string $parente): void
    {
        /** @var CPatient $patient */
        $patient = $this->getObjectFromFixturesReference(
            CPatient::class,
            SimplePatientFixtures::SAMPLE_PATIENT,
            true
        );

        $correspondant             = CCorrespondantPatient::getSampleObject();
        $correspondant->patient_id = $patient->_id;
        $correspondant->relation   = CCorrespondantPatient::RELATION_REPRESENTANT_LEGAL;
        $correspondant->parente    = $parente;
        $correspondant->date_debut = CMbDT::date("-1 day");
        $correspondant->date_fin   = CMbDT::date("+1 day");

        $this->storeOrFailed($correspondant);
        $patient = CPatient::findOrFail($patient->_id);

        $this->assertEquals($expected, $patient->tutelle);
    }

    public function tutellePatientProvider(): array
    {
        return [
            "tutelle"  => [
                "tutelle",
                CCorrespondantPatient::PARENTES_TUTELLE["tuteur"],
            ],
            "curateur" => [
                "curatelle",
                CCorrespondantPatient::PARENTES_TUTELLE["curateur"],
            ],
        ];
    }
}
