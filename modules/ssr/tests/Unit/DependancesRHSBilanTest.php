<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr\Test;


use Ox\Mediboard\Ssr\CDependancesRHS;
use Ox\Mediboard\Ssr\DependancesRHSBilan;
use Ox\Tests\OxUnitTestCase;

class DependancesRHSBilanTest extends OxUnitTestCase
{

    /**
     * @throws \Exception
     */
    public function testCreateFromDependancesRHS()
    {
        $dependances                                       = new CDependancesRHS();
        $dependances->habillage_haut                       = 1;
        $dependances->habillage_bas                        = 3;
        $dependances->deplacement_transfert_lit_chaise     = 1;
        $dependances->deplacement_transfert_toilette       = 1;
        $dependances->deplacement_transfert_baignoire      = 2;
        $dependances->deplacement_locomotion               = 1;
        $dependances->deplacement_escalier                 = 1;
        $dependances->alimentation_utilisations_ustensile  = 2;
        $dependances->alimentation_mastication             = 2;
        $dependances->alimentation_deglutition             = 3;
        $dependances->continence_controle_miction          = 1;
        $dependances->continence_controle_defecation       = 1;
        $dependances->relation_comprehension_communication = 2;
        $dependances->relation_expression_claire           = 3;
        $dependances->comportement                         = 4;

        $resultats = DependancesRHSBilan::createFromDependancesRHS($dependances);
        $expected  = new DependancesRHSBilan(3, 2, 3, 1, 4, 3);
        $this->assertEquals($expected, $resultats);
    }
}
