<?php
/**
 * @package Mediboard\Patients\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit;

use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\Constants\CAbstractConstant;
use Ox\Mediboard\Patients\Constants\CConstantException;
use Ox\Mediboard\Patients\Constants\CConstantReleve;
use Ox\Mediboard\Patients\Constants\CConstantSpec;
use Ox\Mediboard\Patients\Constants\CValueInt;
use Ox\Mediboard\Patients\CPatient;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
//class CConstantReleveTest extends OxUnitTestCase {
//
//  protected function setUp() {
//    $this->markTestSkipped("Failed asserting that null is an instance of class \"Ox\Mediboard\Patients\CConstantValue\"");
//    $this->patient_id = $this->getRandomObjects(CPatient::class)->_id;
//    $user             = $this->getRandomObjects(CMediusers::class);
//    $this->user_id    = $user->user_id;
//  }
//
//  public $patient_id;
//  public $user_id;
//
//  /**
//   * Test creation of 1 releve and 1 constant
//   *
//   * @return array
//   */
//  public function testAddConstant() {
//    $data   = array(
//      array("spec_code" => "weight",
//            "value"     => 80000,
//            "datetime"  => CMbDT::roundTime(null, CMbDT::ROUND_DAY),
//            "validated" => 0,
//            "source"    => CConstantReleve::FROM_MEDIBOARD,
//      )
//    );
//    $report = CConstantReleve::storeReleveAndConstants($data, $this->patient_id, $this->user_id);
//    $this->assertEquals(1, CMbArray::getRecursive($report, "report constant_saved"), "constant saved");
//    $this->assertEquals(1, CMbArray::getRecursive($report, "report releve_saved"), "releve saved");
//
//    $constant_ids = CMbArray::getRecursive($report, "report constant_ids");
//    $constant_guid = reset($constant_ids);
//    /** @var CConstantValue $constant */
//    $constant = CMbObject::loadFromGuid($constant_guid);
//    self::assertInstanceOf(CConstantValue::class, $constant);
//    self::assertEquals($this->patient_id, $constant->patient_id);
//    self::assertNotNull($constant->_id);
//
//    $releve = $constant->loadRefReleve();
//    self::assertNotNull($releve->_id);
//    self::assertEquals($this->patient_id, $releve->patient_id);
//
//    return array("releve_id" => $releve->_id, "constant_id" => $constant->_id);
//  }
//
//  /**
//   * @depends testAddConstant
//   *
//   * @param array $ids releve and constant identifiers
//   *
//   * @throws \Ox\Mediboard\Patients\CConstantException
//   */
//  public function testUpddateConstants($ids) {
//    list($releve_id, $constant_id) = $this->getIds($ids);
//    $releve = new CConstantReleve();
//    $releve->load($releve_id);
//    self::assertNotNull($releve->_id);
//
//    $weight_expected = 70000;
//    $data            = array(
//      array("spec_code" => "weight",
//            "value"     => $weight_expected,
//            "datetime"  => CMbDT::roundTime(null, CMbDT::ROUND_DAY),
//            "validated" => 0,
//            "source"    => CConstantReleve::FROM_MEDIBOARD,
//      )
//    );
//    $result          = CConstantReleve::storeReleveAndConstants($data, $releve->patient_id, $releve->user_id);
//    // report est correct
//    self::assertEquals("1", CMbArray::getRecursive($result, "report constant_updated"), "constant_updated");
//    self::assertEquals("1", CMbArray::getRecursive($result, "report releve_loaded"), "releve_loaded");
//    $constant_ids = CMbArray::getRecursive($result, "report constant_ids");
//    self::assertEquals(1, count($constant_ids));
//    $constant_guid = reset($constant_ids);
//    /** @var CConstantValue $constant */
//    $constant = CMbObject::loadFromGuid($constant_guid);
//    // derniere constante est bien celle qu'on a enregistrée
//    self::assertNotNull($constant);
//    self::assertInstanceOf(CValueInt::class, $constant);
//    self::assertEquals($weight_expected, $constant->getValue());
//
//    $old_constant = new CValueInt();
//    $old_constant->load($constant_id);
//    // ancienne constante inactive
//    self::assertNotNull($old_constant);
//    self::assertEquals(0, $old_constant->active);
//  }
//
//  /**
//   * @param array $ids identifier releve and constant
//   *
//   * @return array
//   */
//  public function getIds($ids) {
//    $releve_id = CMbArray::get($ids, "releve_id");
//    self::assertNotNull($releve_id);
//    $constant_id = CMbArray::get($ids, "constant_id");
//    self::assertNotNull($constant_id);
//
//    return array($releve_id, $constant_id);
//  }
//
//  /**
//   * @param array $ids identifier releve and constants
//   *
//   * @depends testAddConstant
//   * @throws \Exception
//   */
//  public function testUpddateReleve($ids) {
//    list($releve_id) = $this->getIds($ids);
//    $releve = new CConstantReleve();
//    $releve->load($releve_id);
//    self::assertNotNull($releve->_id);
//
//    $data   = array(
//      array(
//        "spec_code" => "weight",
//        "releve_id" => $releve_id,
//        "datetime"  => CMbDT::roundTime(null, CMbDT::ROUND_DAY),
//        "validated" => 1,
//        "source"    => CConstantReleve::FROM_MEDIBOARD,
//      ));
//    $result = CConstantReleve::storeReleveAndConstants($data, $releve->patient_id, $releve->user_id);
//    // test result
//    self::assertEquals("1", CMbArray::getRecursive($result, "report releve_updated"), "releve_updated");
//    self::assertEquals("0", CMbArray::getRecursive($result, "report constant_saved"), "constant_saved");
//    self::assertEquals("1", CMbArray::getRecursive($result, "exceptions CConstantException-4"), "value not found");
//    $releve->load($releve_id);
//    // test update
//    self::assertNotNull($releve->_id);
//    self::assertEquals(1, $releve->validated, "validated");
//  }
//
//  /**
//   * Store inactive releve, all constants in this releve have to be inactive
//   */
//  public function testStoreInactiveReleve() {
//    /** @var CConstantReleve $releve */
//    list($result, $releve) = $this->createNConstants(array(array("spec_code" => "weight"), array("spec_code" => "heartrate")));
//    self::assertEquals(2, CMbArray::getRecursive($result, "report constant_saved"), "constant_saved");
//    self::assertEquals(1, CMbArray::getRecursive($result, "report releve_saved"), "releve_saved");
//    self::assertEquals(1, CMbArray::getRecursive($result, "report releve_loaded"), "releve_loaded");
//    self::assertEquals(2, count($releve->_ref_all_values));
//
//    $releve->storeInactive();
//    self::assertEquals(0, $releve->active);
//
//    /** @var CConstantValue $_constant */
//    // verrif que toutes les constantes sont inactives
//    foreach ($releve->loadAllValues() as $_constant) {
//      self::assertEquals(0, $_constant->active);
//    }
//    self::assertEquals(0, count($releve->loadAllValues(array("active" => "= '1'"))), "count active");
//  }
//
//  /**
//   * Create 2 constants in function of parameters
//   *
//   * @param array $data_spec data
//   *
//   * @return array
//   */
//  public function createNConstants($data_spec) {
//    $data = array();
//    foreach ($data_spec as $_data_spec) {
//      $_data = array();
//      foreach ($_data_spec as $_key => $value) {
//        $_data[$_key] = $value;
//      }
//      if (!CMbArray::get($_data, "datetime")) {
//        $_data["datetime"] = CMbDT::roundTime(null, CMbDT::ROUND_DAY);
//      }
//      if (!CMbArray::get($_data, "source")) {
//        $_data["source"] = CConstantReleve::FROM_MEDIBOARD;
//      }
//      if (!CMbArray::get($_data, "validated")) {
//        $_data["validated"] = 0;
//      }
//      if (!CMbArray::get($_data, "value")) {
//        $_data["value"] = 1;
//      }
//      $data[] = $_data;
//    }
//
//    $report        = CConstantReleve::storeReleveAndConstants($data, $this->patient_id, $this->user_id);
//    $constant_ids = CMbArray::getRecursive($report, "report constant_ids");
//    $constant_guid = reset($constant_ids);    /** @var CConstantValue $constant */
//    $constant = CMbObject::loadFromGuid($constant_guid);
//    self::assertInstanceOf(CConstantValue::class, $constant);
//    self::assertEquals($this->patient_id, $constant->patient_id);
//    self::assertNotNull($constant->_id);
//
//    $releve = $constant->loadRefReleve();
//    $releve->loadAllValues();
//    self::assertNotNull($releve->_id);
//    self::assertEquals($this->patient_id, $releve->patient_id);
//
//    return array($report, $releve);
//  }
//
//  /**
//   * Store inactive constant
//   */
//  public function testStoreInactiveConstant() {
//    list(, $releve) = $this->createNConstants(array(array("spec_code" => "weight"), array("spec_code" => "heartrate")));
//    /** @var CConstantReleve $releve */
//    /** @var CConstantValue $constant */
//    $constant = reset($releve->_ref_all_values);
//    self::assertInstanceOf(CConstantValue::class, $constant);
//    $constant->storeInactive();
//
//    /** @var CConstantValue $_constant */
//    foreach ($releve->loadAllValues() as $_constant) {
//      if ($_constant->_id === $constant->_id) {
//        self::assertEquals(0, $_constant->active);
//      }
//      else {
//        self::assertEquals(1, $_constant->active);
//      }
//    }
//    self::assertEquals(1, $releve->active);
//  }
//
//  /**
//   * Store 2 constants which generate 1 calculated constant
//   */
//  public function testCalculatedConstants() {
//    list($result, $releve) = $this->createNConstants(array(array("spec_code" => "weight"), array("spec_code" => "height")));
//    self::assertEquals(3, CMbArray::getRecursive($result, "report constant_saved"), "constant_saved");
//    self::assertEquals(1, CMbArray::getRecursive($result, "report calculated_constant_stored"), "calculated stored");
//    /** @var CConstantReleve $releve */
//    self::assertEquals(3, count($releve->loadAllValues()));
//    self::assertEquals("imc", end($releve->_ref_all_values)->getRefSpec()->code);
//  }
//
//  /**
//   * Test calculated constant which depends of calculated constant
//   *
//   * @throws \Ox\Mediboard\Patients\CConstantException
//   */
//  public function testCalculatedConstantsInCascade() {
//    // creation of spec testformula = (weight + imc) + remduration?0
//    $spec              = new CConstantSpec();
//    $spec->name        = "Test";
//    $spec->period      = 86400;
//    $spec->unit        = "m|cm /100";
//    $spec->category    = "physio";
//    $spec->value_class = "CValueInt";
//    $spec->code        = "testformula";
//    $spec->active      = 1;
//    $spec->formule     = '[$weight] + [$imc] + [$remduration]?0';
//    $msg               = $spec->store();
//    self::assertEquals("", $msg);
//    self::assertEquals(0, $spec->_warning_formule);
//    self::assertEquals("", $spec->_warning_formule_error);
//
//
//    // creation of spec testformula2 = test + heartrate
//    $spec              = new CConstantSpec();
//    $spec->name        = "Test2";
//    $spec->period      = 86400;
//    $spec->unit        = "cm|m *100";
//    $spec->category    = "physio";
//    $spec->value_class = "CValueInt";
//    $spec->code        = "testformula2";
//    $spec->active      = 1;
//    $spec->formule     = '[$testformula] + [$heartrate]';
//    $msg               = $spec->store();
//    self::assertEquals("", $msg);
//    self::assertEquals(0, $spec->_warning_formule);
//    self::assertEquals("", $spec->_warning_formule_error);
//
//    list($result, $releve) = $this->createNConstants(
//      array(array("spec_code" => "weight", "value" => 70000), array("spec_code" => "height", "value" => 176))
//    );
//    /** @var CConstantReleve $releve */
//    self::assertEquals(4, CMbArray::getRecursive($result, "report constant_saved"), "constant_saved");
//    self::assertEquals(2, CMbArray::getRecursive($result, "report calculated_constant_stored"), "calculated_constant_stored");
//    self::assertEquals(1, CMbArray::getRecursive($result, "report calculated_constant_failed"), "calculated_constant_failed");
//    list($result, $releve) = $this->createNConstants(array(array("spec_code" => "heartrate", "value" => 100)));
//    self::assertEquals(2, CMbArray::getRecursive($result, "report constant_saved"), "constant_saved");
//    self::assertEquals(1, CMbArray::getRecursive($result, "report calculated_constant_stored"), "calculated_constant_stored");
//    self::assertEquals(1, CMbArray::getRecursive($result, "report releve_loaded"), "releve_loaded");
//  }
//
//
//  /**
//   * Test limit store for CValueInt
//   */
//  public function testExceptionValueNotInRange() {
//    // min failed limit
//    list($result) = $this->createConstant("heartrate", array("value" => -1));
//    self::assertEquals(1, CMbArray::getRecursive($result, "report releve_saved"));
//    self::assertEquals(0, CMbArray::getRecursive($result, "report constant_saved"));
//    self::assertEquals(1, CMbArray::getRecursive($result, "exceptions CConstantException-13"));
//    // min failed
//    list($result) = $this->createConstant("heartrate", array("value" => -10));
//    self::assertEquals(1, CMbArray::getRecursive($result, "report releve_loaded"));
//    self::assertEquals(0, CMbArray::getRecursive($result, "report constant_saved"));
//    self::assertEquals(1, CMbArray::getRecursive($result, "exceptions CConstantException-13"));
//    // max failed
//    list($result) = $this->createConstant("heartrate", array("value" => 400));
//    self::assertEquals(1, CMbArray::getRecursive($result, "report releve_loaded"));
//    self::assertEquals(0, CMbArray::getRecursive($result, "report constant_saved"));
//    self::assertEquals(1, CMbArray::getRecursive($result, "exceptions CConstantException-14"));
//    // max failed limit
//    list($result) = $this->createConstant("heartrate", array("value" => 251));
//    self::assertEquals(1, CMbArray::getRecursive($result, "report releve_loaded"));
//    self::assertEquals(0, CMbArray::getRecursive($result, "report constant_saved"));
//    self::assertEquals(1, CMbArray::getRecursive($result, "exceptions CConstantException-14"));
//
//    // min success limit
//    list($result) = $this->createConstant("heartrate", array("value" => 0));
//    self::assertEquals(1, CMbArray::getRecursive($result, "report releve_loaded"));
//    self::assertEquals(1, CMbArray::getRecursive($result, "report constant_saved"));
//    self::assertEquals(null, CMbArray::getRecursive($result, "exceptions CConstantException-13"));
//    // max success limit
//    list($result) = $this->createConstant("heartrate", array("value" => 250));
//    self::assertEquals(1, CMbArray::getRecursive($result, "report releve_loaded"));
//    self::assertEquals(1, CMbArray::getRecursive($result, "report constant_updated"));
//    self::assertEquals(null, CMbArray::getRecursive($result, "exceptions CConstantException-14"));
//    // success
//    list($result) = $this->createConstant("heartrate", array("value" => 100));
//    self::assertEquals(1, CMbArray::getRecursive($result, "report releve_loaded"));
//    self::assertEquals(1, CMbArray::getRecursive($result, "report constant_updated"));
//  }
//
//  /**
//   * Create constant
//   *
//   * @param string $spec  code spec
//   * @param array  $value key => value (string|int|array)
//   *
//   * @return array
//   */
//  public function createConstant($spec, $value) {
//    $key    = key($value);
//    $data   = array(
//      array("spec_code" => "$spec",
//            "$key"      => CMbArray::get($value, $key),
//            "datetime"  => CMbDT::roundTime(null, CMbDT::ROUND_DAY),
//            "validated" => 0,
//            "source"    => CConstantReleve::FROM_MEDIBOARD,
//      )
//    );
//    $result = CConstantReleve::storeReleveAndConstants($data, $this->patient_id, $this->user_id);
//    $releve = null;
//    if (CMbArray::getRecursive($result, "report constant_saved") > 0 && CMbArray::getRecursive($result, "report releve_saved") > 0) {
//      $objects       = CValue::sessionAbs('objects');
//      $constant_guid = end($objects);
//      /** @var CConstantValue $constant */
//      $constant = CMbObject::loadFromGuid($constant_guid);
//      self::assertInstanceOf(CConstantValue::class, $constant);
//      self::assertEquals($this->patient_id, $constant->patient_id);
//      self::assertNotNull($constant->_id);
//
//      $releve = $constant->loadRefReleve();
//      $releve->loadAllValues();
//      self::assertNotNull($releve->_id);
//      self::assertEquals($this->patient_id, $releve->patient_id);
//    }
//
//    return array($result, $releve);
//  }
//
//}
