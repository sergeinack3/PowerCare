<?php
/**
 * @package Mediboard\Facturation\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation\Tests\Unit;


use Ox\Mediboard\Facturation\CFactureEtablissement;
use Ox\Tests\OxUnitTestCase;

class CFactureEtablissementTest extends OxUnitTestCase {

  /**
   * Test du constructeur de la facture
   */
  public function test__construct() {
    $aide = new CFactureEtablissement();
    $this->assertInstanceOf(CFactureEtablissement::class, $aide);
  }

  /**
   * Test de recherche de la facture
   */
  public function testFindFacture() {
    $facture = new CFactureEtablissement();
    $this->assertInstanceOf(CFactureEtablissement::class, $facture->findFacture(1));
  }
}
