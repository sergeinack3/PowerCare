<?php

/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr\Test;

use Ox\Core\CMbException;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Populate\Generators\CMediusersGenerator;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class CEvenementSSRTest extends OxUnitTestCase
{
    /**
     * Test to get therapists of the ssr event
     */
    public function testGetTherapeute(): void
    {
        /** @var CEvenementSSR $evt_col */
        $therapeute   = $this->getRandomTherapeute();
        $evt_col                          = CEvenementSSR::getSampleObject();
        $evt_col->therapeute_id           = $therapeute->_id;
        $evt_col->loadRefTherapeute(false);
        if ($msg = $evt_col->store()) {
            throw new CMbException($msg);
        }

        $therapeute_2 = $this->getRandomTherapeute();
        $therapeute_3 = $this->getRandomTherapeute();
        $therapeute_4 = $this->getRandomTherapeute();

        $evt_ssr                = new CEvenementSSR();
        $evt_ssr->therapeute_id = $therapeute_4->_id;
        $evt_ssr->loadRefTherapeute(false);

        // Unique therapeute case
        $this->assertEquals([$therapeute_4->_id], $evt_ssr->getTherapeutes());

        $evt_ssr->therapeute2_id = $therapeute_2->_id;
        $evt_ssr->therapeute3_id = $therapeute_3->_id;

        // Multiple therapeutes case
        $this->assertEquals(
            [$therapeute_4->_id, $therapeute_2->_id, $therapeute_3->_id],
            $evt_ssr->getTherapeutes()
        );

        $evt_ssr->seance_collective_id = $evt_col->_id;
        if ($msg = $evt_ssr->store()) {
            throw new CMbException($msg);
        }

        // Evenement collectif case
        $this->assertContains((string)$therapeute->_id, $evt_ssr->getTherapeutes());
    }

    /**
     * Return a user with a code_intervenant_cdarr
     *
     * @param array $where Optionnal conditions
     */
    private function getRandomTherapeute(): CMediusers
    {
        $kine = (new CMediusersGenerator())->setForce(true)->generate("Rééducateur");

        if ($kine->_id && !$kine->code_intervenant_cdarr) {
            $kine->code_intervenant_cdarr = 12;
            $kine->store();
        }

        return $kine;
    }

    /**
     * Test de la fonction de récupération des collisions pour les evenements SSR (sans plages)
     *
     * @config [CConfiguration] ssr general lock_add_evt_conflit 1
     * @throws TestsException
     */
    public function testGetCollectivesCollisionsEvenementSSR(): void
    {
        $therapeute = $this->getRandomTherapeute();
        $therapeute->loadRefFunction();

        /** @var CEvenementSSR $evenement */
        $evenement                          = CEvenementSSR::getSampleObject();
        $evenement->therapeute_id           = $therapeute->_id;
        $evenement->loadRefTherapeute(false);
        if ($msg = $evenement->store()) {
            throw new CMbException($msg);
        }

        $evenement_2 = new CEvenementSSR();
        $evenement_2->cloneFrom($evenement);

        $collisions = $evenement_2->getCollectivesCollisions(false, false, false, ">", false);

        if (array_keys($collisions)) {
            $this->assertTrue(in_array($evenement->_id, array_keys($collisions)));
        } else {
            $this->assertEmpty($collisions);
        }
    }
}
