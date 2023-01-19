<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use Ox\Mediboard\Maternite\Tests\Fixtures\TrackingPregnancyFixtures;
use Ox\Tests\OxUnitTestCase;
use Ox\Tests\TestsException;

class CDepistageGrossesseTest extends OxUnitTestCase
{
    /**
     * Create depistage grossesse
     *
     * @return CDepistageGrossesse
     * @throws TestsException
     */
    public function testCreateDepistage(): CDepistageGrossesse
    {
        $grossesse                   = $this->getObjectFromFixturesReference(
            CGrossesse::class,
            TrackingPregnancyFixtures::TAG_PREGNANCY_TRACKING
        );
        $depistage                   = new CDepistageGrossesse();
        $depistage->grossesse_id     = $grossesse->_id;
        $depistage->date             = "now";
        $depistage->_libelle_customs = [0 => "Test", 1 => "Test2"];
        $depistage->_valeur_customs  = [0 => 15, 1 => 20];
        $depistage->store();

        $this->assertNotNull($depistage->_id);

        return $depistage;
    }

    /**
     * Test du calcul de la date en semaines d'aménorrhée
     *
     * @param CDepistageGrossesse $depistage Dépistage
     *
     * @depends testCreateDepistage
     * @throws TestsException
     */
    public function testCalculSemaineAmenorrheeSA(CDepistageGrossesse $depistage): void
    {
        $sa = $depistage->getSA();
        $this->assertIsNumeric($sa);
    }

    /**
     * Test de la construction du tableau des dépistages additionnels
     *
     * @param CDepistageGrossesse $depistage Dépistage
     *
     * @depends testCreateDepistage
     * @throws TestsException
     */
    public function testConstructionAdditionnelDepistage(CDepistageGrossesse $depistage): void
    {
        $depistage->updateFormFields();
        $this->assertCount(3, $depistage->_depistage_custom_ids);
    }
}
