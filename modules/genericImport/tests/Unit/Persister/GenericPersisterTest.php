<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Tests\Unit\Persister;

use Ox\Core\Cache;
use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Import\Framework\Configuration\Configuration;
use Ox\Import\GenericImport\Persister\GenericPersister;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class GenericPersisterTest extends OxUnitTestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testPersistPatient(): CPatient
    {
        $cache = new Cache('CPatient.getTagIPP', [CGroups::loadCurrent()->_id], Cache::INNER);
        $cache->put("tag_ipp");

        // creer patient sans le store -> champs _IPP rempli
        $patient       = CPatient::getSampleObject();
        $patient->_IPP = "IPPTEST";

        $genericPersister = new GenericPersister();
        $genericPersister->setConfiguration(
            new Configuration(["generate_ipp" => false,])
        );

        // store le patient via generic persister
        $patient_after = $this->invokePrivateMethod($genericPersister, 'persistPatient', $patient);


        // vider le champs _IPP de ce patient
        $patient->_IPP = null;
        // sur le patient -> loadIPP pour retrouver l'ipp associé à ce patient en base
        $patient->loadIPP();

        // vérifier que _IPP est bien rempli avec celui attendu
        $this->assertEquals($patient->_IPP, $patient_after->_IPP);

        return $patient;
    }
    /**
     * @depends testPersistPatient
     *
     * @runInSeparateProcess
     */
    public function testPersistSejour(CPatient $patient): void
    {
        $cache = new Cache('CSejour.getTagNDA', [CGroups::loadCurrent()->_id], Cache::INNER);
        $cache->put("tag_nda");

        // creer sejour sans le store -> champs _NDA rempli
        $sejour = $this->buildSejour($patient);
        $sejour->_NDA = "NDATEST";

        $genericPersister = new GenericPersister();
        $genericPersister->setConfiguration(
            new Configuration(["generate_nda" => false,])
        );

        // store le sejour via generic persister
        $sejour_after = $this->invokePrivateMethod($genericPersister, 'persistSejour', $sejour);

        // vider le champs _NDA de ce sejour
        $sejour->_NDA = null;
        // sur le patient -> loadNDA pour retrouver le nda associé à ce sejour en base
        $sejour->loadNDA();

        // vérifier que _NDA est bien rempli avec celui attendu
        $this->assertEquals($sejour->_NDA, $sejour_after->_NDA);
    }

    /**
     * @throws CModelObjectException
     */
    private function buildSejour(CPatient $patient): CSejour
    {
        /** @var CSejour $sejour */
        $sejour                = CSejour::getSampleObject();
        $sejour->patient_id    = $patient->_id;
        $sejour->praticien_id  = $this->getObjectFromFixturesReference(CMediusers::class, UsersFixtures::REF_USER_MEDECIN)->_id;
        $sejour->group_id      = CMediusers::get()->loadRefFunction()->group_id;
        $sejour->entree_prevue = $sejour->entree_reelle = $sejour->entree = CMbDT::dateTime("-1 hours");
        $sejour->sortie_prevue = $sejour->sortie_reelle = $sejour->sortie = CMbDT::dateTime("+2 hours");

        return $sejour;
    }
}
