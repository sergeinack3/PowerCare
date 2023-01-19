<?php
/**
 * @package Mediboard\Facturation\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation\Tests\Unit;

use Ox\Core\CMbDT;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Facturation\CFactureCategory;
use Ox\Mediboard\Facturation\CFactureCoeff;
use Ox\Mediboard\Facturation\Tests\Fixtures\FactureFixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\OxUnitTestCase;

class CFactureTest extends OxUnitTestCase {
  private function getAllRoundPossibilities() {
    return array(
      //Arrondis à l'inférieur
      "1.0"  => array(1.01, 1.02),
      //Arrondis au milieu
      "1.05" => array(1.03, 1.04, 1.05, 1.06, 1.07),
      //Arrondis au supérieur
      "1.1"  => array(1.08, 1.09));
  }

  /**
   * Test de l'ensemble des cas d'arrondis des montants de la facture
   */
  public function testRoundValueFrance() {
    foreach ($this->getAllRoundPossibilities() as $valeurs_tests) {
      foreach ($valeurs_tests as $_valeur_test) {
        $result = CFacture::roundValue($_valeur_test, true);
        $this->assertEquals($_valeur_test, $result);
      }
    }
  }

  /**
   * Test de la génération du numéro de controle en fonction du montant de la facture ou du début du numéro de bvr
   */
  public function testGetNoControle() {
    $facture = new CFacture();
    //Utilisation d'un début de numéro de BVR
    $result = $facture->getNoControle("21474836470000000000001096");
    $this->assertEquals(8, $result);

    //Utilisation d'un montant de facture
    $facture->du_patient = 18.610;
    $result = $facture->getNoControle(0);
    $this->assertEquals(6, $result);
  }


  /**
   * Test de chargement du patient
   */
  public function testloadRelPatient() {
    $facture = new CFacture();
    $this->assertInstanceOf(CPatient::class, $facture->loadRelPatient());
  }

  /**
   * Test de chargement du praticien
   */
  public function testloadRefPraticien() {
    $facture = new CFacture();
    $this->assertInstanceOf(CMediusers::class, $facture->loadRefPraticien());
  }

  /**
   * Test de chargement du coefficient de la facture
   */
  public function testloadRefCoeff() {
    $facture = new CFacture();
    $this->assertInstanceOf(CFactureCoeff::class, $facture->loadRefCoeff());
  }

  /**
   * Test de chargement du coefficient de la facture
   */
  public function testloadRefCategory() {
    $facture = new CFacture();
    $this->assertInstanceOf(CFactureCategory::class, $facture->loadRefCategory());
  }

  /**
   * Test de la récupération du premier réglement d'une facture
   */
//  public function testGetFirstReglement() {
//    $this->markTestSkipped('Undefined index: avoirs');
//    $new_reglement           = new CReglement();
//    $new_reglement->_id      = $new_reglement->reglement_id = 1;
//    $new_reglement->date     = CMbDT::dateTime();
//    $new_reglement->montant  = 100;
//    $old_reglement           = $new_reglement;
//    $old_reglement->_id      = $old_reglement->reglement_id = 2;
//    $old_reglement->date     = CMbDT::dateTime("-1 DAY", $old_reglement->date);
//    $old_reglement->montant  += 50;
//
//    $facture                  = new CFacture();
//    $facture->_ref_reglements = array($new_reglement, $old_reglement);
//    $reglement_montant        = $facture->getFirstReglement();
//    $reglement                = $facture->getFirstReglement(true);
//    $this->assertEquals($reglement_montant, $old_reglement->montant);
//    $this->assertEquals($reglement, $old_reglement);
//  }

  /**
   * Test de la récupération de la période de traitement des séjour d'une facture
   */
  public function testGetTraitementPeriodeSejours() {
    $facture = new CFacture();

    $dates_sejour1 = array(
      "entree" => CMbDT::dateTime("-10 DAYS"),
      "sortie" => CMbDT::dateTime("-8 DAYS")
    );
    $dates_sejour2 = array(
      "entree" => CMbDT::dateTime("-5 DAYS"),
      "sortie" => CMbDT::dateTime("-3 DAYS")
    );
    $expected_dates = array(
      $dates_sejour1["entree"],
      $dates_sejour2["sortie"],
    );
    $sejour1 = new CSejour();
    $sejour1->_id = 1;
    $sejour1->entree_prevue = $dates_sejour1["entree"];
    $sejour1->entree_reelle = $sejour1->entree = $sejour1->entree_prevue;
    $sejour1->sortie_prevue = $dates_sejour1["sortie"];
    $sejour1->sortie_reelle = $sejour1->sortie = $sejour1->sortie_prevue;
    $facture->_ref_sejours[$sejour1->_id] = $facture->_ref_first_sejour = $facture->_ref_last_sejour = $sejour1;

    $sejour2 = new CSejour();
    $sejour2->_id = 2;
    $sejour2->entree_prevue = $dates_sejour2["entree"];
    $sejour2->entree_reelle = $sejour2->entree = $sejour2->entree_prevue;
    $sejour2->sortie_prevue = $dates_sejour2["sortie"];
    $sejour2->sortie_reelle = $sejour2->sortie = $sejour2->sortie_prevue;
    $facture->_ref_first_sejour = $facture->_ref_first_sejour ?: $sejour2;
    $facture->_ref_sejours[$sejour2->_id] = $facture->_ref_last_sejour = $sejour2;

    $facture->_ref_consults = array(new CConsultation());
    $facture->_ref_evts = array(new CEvenementPatient());
    $this->assertEquals($expected_dates, $facture->getTraitementPeriode());
  }

  /**
   * Test de la récupération de la période de traitement de constulation ou d'evt patient d'une facture
   */
  public function testGetTraitementPeriodeConsultationsEvts() {
    // Test lié aux consultations
    $facture = new CFacture();
    $facture->_ref_sejours = array(new CSejour());
    $facture->_ref_evts = array(new CEvenementPatient());

    $date_element1 = CMbDT::date("-10 DAYS");
    $date_element2 = CMbDT::date("-5 DAYS");
    $expected_dates = array(
      $date_element1,
      $date_element2,
    );

    $consultation1 = new CConsultation();
    $consultation1->_id = 1;
    $consultation1->_date = $date_element1;
    $facture->_ref_consults[$consultation1->_id] = $facture->_ref_first_consult = $consultation1;

    $consultation2 = new CConsultation();
    $consultation2->_id = 2;
    $consultation2->_date = $date_element2;
    $facture->_ref_consults[$consultation2->_id] = $facture->_ref_last_consult = $consultation2;

    $this->assertEquals($expected_dates, $facture->getTraitementPeriode());

    // Test lié aux evenements patient
    $facture = new CFacture();
    $facture->_ref_sejours = array(new CSejour());
    $facture->_ref_evts = array(new CEvenementPatient());

    $evt1 = new CEvenementPatient();
    $evt1->_id = 1;
    $evt1->date = $date_element1;
    $facture->_ref_evts[$evt1->_id] = $facture->_ref_first_evt = $evt1;

    $evt2 = new CEvenementPatient();
    $evt2->_id = 2;
    $evt2->date = $date_element2;
    $facture->_ref_evts[$evt2->_id] = $facture->_ref_last_evt = $evt2;

    $this->assertEquals($expected_dates, $facture->getTraitementPeriode());
  }

  /**
   * Test de la récupération et de l'enregistrement d'une facture en fonction d'une consultation
   * !! Fonction essentielle au bon fonctionnement de la facturation !!
   */
  public function testSaveConsult() {
    //Création de la facture
    $consultation = $this->getObjectFromFixturesReference(CConsultation::class, FactureFixtures::TAG_CONSULTATION);
    $msg = CFacture::save($consultation);
    $this->assertNull($msg);

    //Vérification du bon chargement des consultations
    $facture = $consultation->loadRefFacture();
    $facture->loadRefsObjects();
    $this->assertEquals(1, count($facture->_ref_consults));
    $this->assertEquals($consultation->_id, $facture->_ref_first_consult->_id);

  }

  /**
   * Test de la récupération et de l'enregistrement d'une facture en fonction d'un événement patient
   * !! Fonction essentielle au bon fonctionnement de la facturation !!
   */
  public function testSaveEvt() {
    //Création de la facture
    $evt = $this->getObjectFromFixturesReference(CEvenementPatient::class, FactureFixtures::TAG_EVENEMENT);
    $msg = CFacture::save($evt);
    $this->assertNull($msg);

    //Vérification du bon chargement de l'évenement
    $facture = $evt->loadRefFacture();
    $facture->loadRefsObjects();
    $this->assertEquals(1, count($facture->_ref_evts));
    $this->assertEquals($evt->_id, $facture->_ref_first_evt->_id);
  }

  /**
   * Test de la récupération et de l'enregistrement d'une facture en fonction d'un séjour
   * !! Fonction essentielle au bon fonctionnement de la facturation !!
   *
   * @config [CConfiguration] dPplanningOp CFactureEtablissement use_facture_etab 1
   */
  public function testSaveSejour() {
    $sejour = $this->getObjectFromFixturesReference(CSejour::class, FactureFixtures::TAG_SEJOUR);
    $msg = CFacture::save($sejour);
    $this->assertNull($msg);

    //Vérification du bon chargement de l'évenement
    $facture = $sejour->loadRefFacture();
    $facture->loadRefsObjects();
    $this->assertEquals(1, count($facture->_ref_sejours));
    $this->assertEquals($sejour->_id, $facture->_ref_first_sejour->_id);
  }
}
