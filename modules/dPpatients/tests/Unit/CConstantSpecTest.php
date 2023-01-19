<?php
/**
 * @package Mediboard\Patients\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit;

use Ox\Mediboard\Patients\Constants\CConstantSpec;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CConstantSpecTest extends OxUnitTestCase {

  public $specs = array(
    1  => "height",
    2  => "heartrate",
    3  => "dailyactivity",
    4  => "hourlyactivity",
    5  => "dailysleep",
    6  => "hourlysleep",
    7  => "lightsleepduration",
    8  => "deepsleepduration",
    9  => "wakeupduration",
    10 => "weight",
    11 => "remduration",
    12 => "imc",
    13 => "dailyheartrate",
    14 => "heartrateinterval",
    15 => "dailydistance",
  );

  /**
   * Get or create constant spec corresponding to $code
   *
   * @param string $code code spec
   *
   * @return CConstantSpec|CConstantSpec[]
   * @throws \Exception
   */
  public static function getOrCreate($code) {
    if ($spec = CConstantSpec::getListSpecByCode($code)) {
      return $spec;
    }
    $spec       = self::initSpec();
    $spec->code = $code;
    $spec->store();

    return $spec;
  }

  /**
   * @return CConstantSpec
   */
  public static function initSpec() {
    $spec              = new CConstantSpec();
    $spec->name        = "Test Name";
    $spec->period      = 86400;
    $spec->unit        = "m|cm /100";
    $spec->category    = "physio";
    $spec->value_class = "CValueInt";
    $spec->code        = "testheight";
    $spec->active      = 1;

    return $spec;
  }

  /**
   * Test list of constant spec already created
   */
  public function testUnModifiedXMLSpec() {
    CConstantSpec::resetListConstants();
    // test xml code and id spec not changed
    foreach ($this->specs as $_id => $_code) {
      $spec_code = CConstantSpec::getSpecByCode($_code);
      $spec_id   = CConstantSpec::getSpecById($_id);
      self::assertNotNull($spec_id, $_code);
      self::assertNotNull($spec_code, $_code);
      self::assertEquals($spec_id, $spec_code, $_code);
    }
  }

  /**
   * Test list of constant spec
   */
  public function testListXMLSpec() {
    $codes = array("heartrate", "weight", "hourlyactivity", "remduration");
    $specs = CConstantSpec::getSpecsByCodes($codes);
    /** @var CConstantSpec $_spec */
    self::assertEquals(count($specs), count($codes));
    // test foreach spec needed is correct
    foreach ($specs as $_index => $_spec) {
      self::assertNotNull($_spec);
      self::assertNotNull($_spec->code);
      self::assertEquals(CConstantSpec::getSpecByCode($_spec->code), $_spec);
    }
  }

//  /**
//   * Test creation of constant spec
//   *
//   * @throws \Exception
//   */
//  public function testCreateSpec() {
    // test object empty
//    $spec = new CConstantSpec();
//    $msg  = $spec->store();
//    self::assertNotNull($msg);
//    self::assertNotEquals('', $msg);
//
 //   // test code already exist
 //   $spec       = self::initSpec();
 //   $spec->code = 'height';
 //   $msg        = $spec->store();
 //   self::assertNotNull($msg);
 //   self::assertNotEquals('', $msg);
//
 //   // test creation if not already create
 //   $_const_name = uniqid('test_height_', true);
 //   dump($_const_name);
 //   if (!CConstantSpec::getSpecByCode($_const_name)) {
 //     $spec->code = $_const_name;
 //     $msg        = $spec->store();
 //     self::assertEquals('', $msg);
 //     CConstantSpec::resetListConstants();
//
 //     $values = array_map(function ($spec) {
 //       return $spec->code;
 //     }, CConstantSpec::getListSpecByCode(0));
//
 //   }
//  }

  /**
   * Test formula for calculated constant
   */
  public function testFormulaSpec() {
    $spec            = self::initSpec();
    $constants_specs = array();
    CConstantSpec::addFormulaOnSpec($spec, $constants_specs, 'pow(2,2)');
    $spec->isValidFormule();
    self::assertEquals(0, $spec->_warning_formule);
    self::assertEquals('pow(2,2)', $spec->formule);
    self::assertEquals("", $spec->_warning_formule_error);

    CConstantSpec::addFormulaOnSpec($spec, $constants_specs, '[$weight]*10');
    $spec->isValidFormule();
    self::assertEquals(0, $spec->_warning_formule);
    self::assertEquals('$weight*10', $spec->formule);
    self::assertEquals("", $spec->_warning_formule_error);

    CConstantSpec::addFormulaOnSpec($spec, $constants_specs, '[$weight]*10 + [$height]?0');
    $spec->isValidFormule();
    self::assertEquals(0, $spec->_warning_formule);
    self::assertEquals('$weight*10 + $height?0', $spec->formule);
    self::assertEquals("", $spec->_warning_formule_error);


    // error function not valid
    CConstantSpec::addFormulaOnSpec($spec, $constants_specs, 'piw(2,2)');
    $spec->isValidFormule();
    self::assertEquals('piw(2,2)', $spec->formule);
    self::assertEquals(1, $spec->_warning_formule);
    self::assertEquals("CConstantSpec-msg-error this function doesn t exist", $spec->_warning_formule_error);

    // error constant not find
    CConstantSpec::addFormulaOnSpec($spec, $constants_specs, '[$heigh] + [$testheight]');
    $spec->isValidFormule();
    self::assertEquals('$heigh + $testheight', $spec->formule);
    self::assertEquals(1, $spec->_warning_formule);
    self::assertEquals("CConstantSpec-msg-error this variable doesn t known", $spec->_warning_formule_error);
  }
}
