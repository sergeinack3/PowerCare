<?php
/**
 * @package Mediboard\Facturation\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation\Tests\Unit;

use Ox\Core\CAppUI;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CRelance;
use Ox\Tests\OxUnitTestCase;

class CRelanceTest extends OxUnitTestCase {


  /**
   * Test de chargement de la facture
   */
  public function testloadRefFacture() {
    $relance = new CRelance();
    $this->assertInstanceOf(CFacture::class, $relance->loadRefFacture());
  }

  /**
   * Test de la récupération de l'état de la relance précédent la relance courante
   */
  public function testPreviousRelanceState() {
    $facture = new CFactureCabinet();
    $relance = new CRelance();
    $relance->_ref_object = $facture;
    $this->assertNull($relance->previousRelanceState());

    $previous_relance = new CRelance();
    $previous_relance->_id = 1;
    $previous_relance->statut = "inactive";
    $facture->_ref_last_relance = $previous_relance;
    $this->assertEquals(CRelance::$PREVIOUS_STATE_INACTIVE, $relance->previousRelanceState());

    $previous_relance->statut = "first";
    $previous_relance->etat   = "regle";
    $this->assertEquals(CRelance::$PREVIOUS_STATE_REGLEE, $relance->previousRelanceState());

    $previous_relance->etat   = "emise";
    $this->assertEquals(CRelance::$PREVIOUS_STATE_EN_ATTENTE, $relance->previousRelanceState());
  }

  /**
   * Test de la fonction SetStatutByNumber de CRelance : Assignation du statut et application du surplus en configuration
   *
   * @config [CConfiguration] dPfacturation CRelance add_first_relance 50
   * @config [CConfiguration] dPfacturation CRelance add_second_relance 100
   * @config [CConfiguration] dPfacturation CRelance add_third_relance 200
   */
  public function testSetStatutByNumber() {
    $add_first_relance = CAppUI::gconf("dPfacturation CRelance add_first_relance");
    $add_second_relance = CAppUI::gconf("dPfacturation CRelance add_second_relance");
    $add_third_relance = CAppUI::gconf("dPfacturation CRelance add_third_relance");
    $base_montant = 10;

    $relance = new CRelance();
    $relance->du_patient = $base_montant;
    $relance->setStatutByNumber(1);
    $this->assertEquals($base_montant + $add_first_relance, $relance->du_patient);
    $this->assertEquals("first", $relance->statut);

    $relance->du_patient = $base_montant;
    $relance->setStatutByNumber(2);
    $this->assertEquals($base_montant + $add_second_relance, $relance->du_patient);
    $this->assertEquals("second", $relance->statut);

    $relance->du_patient = $base_montant;
    $relance->setStatutByNumber(3);
    $this->assertEquals($base_montant + $add_third_relance, $relance->du_patient);
    $this->assertEquals("third", $relance->statut);
  }
}
