<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps\Tests\Unit\Entity;

use Exception;
use Ox\Core\CMbString;
use Ox\Core\CModelObjectException;
use Ox\Core\CSQLDataSource;
use Ox\Import\Rpps\CExternalMedecinBulkImport;
use Ox\Import\Rpps\Entity\CAbstractExternalRppsObject;
use Ox\Import\Rpps\Entity\CMssanteInfos;
use Ox\Import\Rpps\Entity\CPersonneExercice;
use Ox\Import\Rpps\Tests\Fixtures\MssanteInfosFixtures;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CMedecinExercicePlace;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CMssanteInfosTest extends OxUnitTestCase
{
    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->ds = CSQLDataSource::get(CExternalMedecinBulkImport::DSN, true);
        if (!$this->ds) {
            $this->markTestSkipped('Datasource RPPS is not available');
        }

        if (!$this->ds->hasTable('mssante_info')) {
            // Create schema for tables to be available
            $import = new CExternalMedecinBulkImport();
            $import->createSchema();
            $this->markTestSkipped('mssante_info table not existing');
        }
    }

    public function testSynchronizeEmpty(): void
    {
        $mssante_info = new CMssanteInfos();
        $this->assertEquals(new CMedecin(), $mssante_info->synchronize());
    }

    /**
     * Test if a mssante address is added to the correct medecin_exercice_place
     * @throws TestsException
     * @throws Exception
     */
    public function testAddMSSanteAddress(): void
    {
        /** @var CMedecinExercicePlace $mep */
        $mep = $this->getObjectFromFixturesReference(
            'CMedecinExercicePlace',
            MssanteInfosFixtures::REF_MSSANTE_INFOS_MEDECIN_EXERCICE_PLACE_WITH_EXERCICE_PLACE
        );

        $unique = CMbString::createLuhn(random_int(1000000000, 9999999999));

        $personne_exercice = $this->makePersonneExercice($unique);

        $mssante_infos = $this->makeMssanteInfos($personne_exercice->identifiant);

        $mep = $mssante_infos->synchronizeMssante($mep);

        $addresses = explode("\n", $mep->mssante_address);

        $this->assertTrue(in_array($mssante_infos->email, $addresses));
    }

    /**
     * Test if function correctly deduplicate addresses
     * @throws TestsException
     * @throws Exception
     */
    public function testAddMSSanteAddressDeduplicate(): void
    {
        /** @var CMedecinExercicePlace $mep */
        $mep = $this->getObjectFromFixturesReference(
            'CMedecinExercicePlace',
            MssanteInfosFixtures::REF_MSSANTE_INFOS_MEDECIN_EXERCICE_PLACE_WITH_DUPLICATE
        );

        $unique = CMbString::createLuhn(random_int(1000000000, 9999999999));

        $personne_exercice = $this->makePersonneExercice($unique);

        $mssante_infos = $this->makeMssanteInfosDuplicate($personne_exercice->identifiant);

        $mep = $mssante_infos->sanitizeMedecinExercicePlaceMSSante($mep);

        $addresses = explode("\n", $mep->mssante_address);

        $this->assertEquals(1, count($addresses));
    }

    /**
     * Test if function is clearing bad addresses allocation
     * @throws TestsException
     * @throws Exception
     */
    public function testAddMSSanteAddressClearBadMatching(): void
    {
        /** @var CMedecinExercicePlace $mep */
        $mep = $this->getObjectFromFixturesReference(
            'CMedecinExercicePlace',
            MssanteInfosFixtures::REF_MSSANTE_INFOS_MEDECIN_EXERCICE_PLACE_WITHOUT_EXERCICE_PLACE
        );

        $unique = CMbString::createLuhn(random_int(1000000000, 9999999999));

        $personne_exercice = $this->makePersonneExercice($unique);

        $mssante_infos = $this->makeMssanteInfos($personne_exercice->identifiant);

        $mep = $mssante_infos->sanitizeMedecinExercicePlaceMSSante($mep);

        $this->assertEmpty($mep->mssante_address);
    }

    /**
     * @param string $identifiant
     *
     * @return CMssanteInfos
     * @throws CModelObjectException
     */
    private function makeMssanteInfos(string $identifiant): CMssanteInfos
    {
        $mssante_infos                   = CMssanteInfos::getSampleObject();
        $mssante_infos->email            = 'mail@mssantemail.com';
        $mssante_infos->id_structure     = '123456789';
        $mssante_infos->type_identifiant = CAbstractExternalRppsObject::TYPE_IDENTIFIANT_RPPS;
        $mssante_infos->identifiant      = $identifiant;

        return $mssante_infos;
    }

    /**
     * @param string $identifiant
     *
     * @return CMssanteInfos
     * @throws CModelObjectException
     */
    private function makeMssanteInfosDuplicate(string $identifiant): CMssanteInfos
    {
        $mssante_infos                   = CMssanteInfos::getSampleObject();
        $mssante_infos->email            = 'jean.dupont@aquitaine.mssante.fr';
        $mssante_infos->id_structure     = '122334455';
        $mssante_infos->type_identifiant = CAbstractExternalRppsObject::TYPE_IDENTIFIANT_RPPS;
        $mssante_infos->identifiant      = $identifiant;

        return $mssante_infos;
    }

    /**
     * @param string $identifiant
     *
     * @return CPersonneExercice
     * @throws CModelObjectException
     */
    private function makePersonneExercice(string $identifiant): CPersonneExercice
    {
        $personne_exercice                        = CPersonneExercice::getSampleObject();
        $personne_exercice->finess_etab_juridique = '123456789';
        $personne_exercice->type_identifiant      = CAbstractExternalRppsObject::TYPE_IDENTIFIANT_RPPS;
        $personne_exercice->identifiant           = $identifiant;

        return $personne_exercice;
    }
}
