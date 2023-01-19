<?php
/**
 * @package Mediboard\Patients\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit;

use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CTraitement;
use Ox\Mediboard\Patients\Tests\Fixtures\DossierMedicalFixtures;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Tests\OxUnitTestCase;

class CDossierMedicalTest extends OxUnitTestCase
{
    public function testCountTraitementsInProgressReturnNumberOfTraitementInProgress(): void
    {
        /** @var CDossierMedical $dossier_medical */
        $dossier_medical = $this->getObjectFromFixturesReference(
            CDossierMedical::class,
            DossierMedicalFixtures::DOSSIER_MEDICAL
        );
        $dossier_medical->countTraitementsInProgress();
        $this->assertEquals(6, $dossier_medical->_count_traitements_in_progress);
    }

    public function testLoadTraitementsInProgressReturnArrayOfTraitementInProgress(): void
    {
        /** @var CDossierMedical $dossier_medical */
        $dossier_medical = $this->getObjectFromFixturesReference(
            CDossierMedical::class,
            DossierMedicalFixtures::DOSSIER_MEDICAL
        );

        /** @var CPrescriptionLineMedicament $line_med_1 */
        $line_med_1 = $this->getObjectFromFixturesReference(
            CPrescriptionLineMedicament::class,
            DossierMedicalFixtures::DOSSIER_MEDICAL_LINE_MEDICAMENT_END_AFTER
        );

        /** @var CPrescriptionLineMedicament $line_med_2 */
        $line_med_2 = $this->getObjectFromFixturesReference(
            CPrescriptionLineMedicament::class,
            DossierMedicalFixtures::DOSSIER_MEDICAL_LINE_MEDICAMENT_WITHOUT_END
        );

        /** @var CPrescriptionLineElement $line_element_1 */
        $line_element_1 = $this->getObjectFromFixturesReference(
            CPrescriptionLineElement::class,
            DossierMedicalFixtures::DOSSIER_MEDICAL_LINE_ELEMENT_END_AFTER
        );

        /** @var CPrescriptionLineElement $line_element_2 */
        $line_element_2 = $this->getObjectFromFixturesReference(
            CPrescriptionLineElement::class,
            DossierMedicalFixtures::DOSSIER_MEDICAL_LINE_ELEMENT_WITHOUT_END
        );

        /** @var CTraitement $traitement_1 */
        $traitement_1 = $this->getObjectFromFixturesReference(
            CTraitement::class,
            DossierMedicalFixtures::DOSSIER_MEDICAL_TRAITEMENT_END_AFTER
        );

        /** @var CTraitement $traitement_2 */
        $traitement_2 = $this->getObjectFromFixturesReference(
            CTraitement::class,
            DossierMedicalFixtures::DOSSIER_MEDICAL_TRAITEMENT_WITHOUT_END
        );

        $dossier_medical->loadTraitementsInProgress();
        $this->assertEquals(3, count($dossier_medical->_traitements_in_progress));
        $this->assertTrue(array_key_exists("medicament", $dossier_medical->_traitements_in_progress));
        $this->assertTrue(array_key_exists("element", $dossier_medical->_traitements_in_progress));
        $this->assertTrue(array_key_exists("traitement", $dossier_medical->_traitements_in_progress));
        $this->assertEquals(2, count($dossier_medical->_traitements_in_progress["medicament"]));
        $this->assertTrue(array_key_exists($line_med_1->_id, $dossier_medical->_traitements_in_progress["medicament"]));
        $this->assertTrue(array_key_exists($line_med_2->_id, $dossier_medical->_traitements_in_progress["medicament"]));
        $this->assertEquals(2, count($dossier_medical->_traitements_in_progress["element"]));
        $this->assertTrue(
            array_key_exists($line_element_1->_id, $dossier_medical->_traitements_in_progress["element"])
        );
        $this->assertTrue(
            array_key_exists($line_element_2->_id, $dossier_medical->_traitements_in_progress["element"])
        );
        $this->assertEquals(2, count($dossier_medical->_traitements_in_progress["traitement"]));
        $this->assertTrue(
            array_key_exists($traitement_1->_id, $dossier_medical->_traitements_in_progress["traitement"])
        );
        $this->assertTrue(
            array_key_exists($traitement_2->_id, $dossier_medical->_traitements_in_progress["traitement"])
        );
    }
}
