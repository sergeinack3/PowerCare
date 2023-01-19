<?php

namespace Ox\Mediboard\Facturation\Tests\Unit;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Mediboard\Cabinet\CBanque;
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\Facturation\CDebiteur;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CFactureEtablissement;
use Ox\Mediboard\Facturation\Tests\Fixtures\FactureFixtures;
use Ox\Mediboard\Patients\CPatient;
use Ox\Tests\OxUnitTestCase;

class CReglementTest extends OxUnitTestCase {
  /**
   * Test de chargement de la banque
   */
  public function testLoadRefBanque() {
    $reglement = new CReglement();
    $this->assertInstanceOf(CBanque::class, $reglement->loadRefBanque());
  }

  /**
   * Test de chargement du débiteur
   */
  public function testLoadRefDebiteur() {
    $reglement = new CReglement();
    $this->assertInstanceOf(CDebiteur::class, $reglement->loadRefDebiteur());
  }

  /**
   * Test de chargement de la facture de cabinet
   */
  public function testLoadRefFactureCabinet() {
    $reglement               = new CReglement();
    $reglement->object_class = "CFactureCabinet";
    $this->assertInstanceOf(CFactureCabinet::class, $reglement->loadRefFacture());
  }

  /**
   * Test de chargement de la facture d'établissement
   */
  public function testLoadRefFactureEtablissement() {
    $reglement               = new CReglement();
    $reglement->object_class = "CFactureEtablissement";
    $this->assertInstanceOf(CFactureEtablissement::class, $reglement->loadRefFacture());
  }

  /**
   * Test le check du montant des réglements
   *
   * @throws \Ox\Tests\TestsException
   */
  public function testCheckMontant() {
    $reglement                = $this->prepareReglement();
    $reglement->montant       = 50;
    $reglement->_old          = new CReglement();
    $reglement->_old->montant = 100;
    $this->assertNull($reglement->check());
    $reglement->montant = 0;
    $this->assertEquals(
      CAppUI::tr("CReglement-msg-The amount of the payment must not be zero"),
      $reglement->check()
    );
  }

  /**
   *  Test de l'acquittement de facture depuis les règlements
   */
  public function testAcquiteFacture() {
    $base_montant                  = 1000;
    $regl_montant                  = 500;
    $acquittement_date             = CMbDT::date();
    $reglement                     = $this->prepareReglement();
    $facture                       = $reglement->_ref_facture;
    $facture->montant_total        = $base_montant;
    $facture->du_patient           = $base_montant;
    $reglement->montant            = $regl_montant;
    $reglement->_acquittement_date = $acquittement_date;
    $reglement->store();
    $this->assertEquals("", $facture->patient_date_reglement);

    $reglement->montant  = $base_montant;
    $facture->du_patient = $base_montant;
    $reglement->store();
    $this->assertEquals($acquittement_date, $facture->patient_date_reglement);

    $facture->du_patient = 0;
    $facture->du_tiers   = $base_montant;
    $reglement->emetteur = "tiers";
    $reglement->montant  = $regl_montant;
    $reglement->store();
    $this->assertEquals("", $facture->tiers_date_reglement);

    $reglement->montant = $base_montant;
    $facture->du_tiers  = $base_montant;
    $reglement->store();
    $this->assertEquals($acquittement_date, $facture->tiers_date_reglement);
  }

  /**
   * Prépare un object Reglemnet pour les tests
   *
   * @return CReglement
   * @throws \Ox\Tests\TestsException
   */
  public function prepareReglement() {
    $patient             = $this->getObjectFromFixturesReference(CPatient::class, FactureFixtures::TAG_PATIENT);
    $facture             = new CFactureCabinet();
    $facture->patient_id = $patient->_id;
    $facture->ouverture  = CMbDT::dateTime();
    $facture->store();
    $reglement               = new CReglement();
    $reglement->date         = CMbDT::dateTime();
    $reglement->emetteur     = "patient";
    $reglement->mode         = "virement";
    $reglement->object_class = $facture->_class;
    $reglement->object_id    = $facture->_id;
    $reglement->_ref_object  = $reglement->_ref_facture = $facture;

    return $reglement;
  }
}
