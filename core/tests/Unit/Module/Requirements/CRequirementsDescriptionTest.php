<?php

namespace Ox\Core\Tests\Unit\Module\Requirements;

use Ox\Tests\OxUnitTestCase;

class CRequirementsDescriptionTest extends OxUnitTestCase {

  public function testHasDescription() {
    $requirements_test = new CRequirementsDummy();

    $description = $requirements_test->getDescription();

    $this->assertTrue($description->hasDescription());
  }

  public function testContentDescription() {
    $requirements_test = new CRequirementsDummy();
    $description       = $requirements_test->getDescription();
    $text              = "# title # \n*** \ntest \n* test list \n* test list 2 \n";
    $this->assertEquals($text, $description->render());
  }

  public function testSetDescription() {
    $requirements_test = new CRequirementsDummy();
    $description       = $requirements_test->getDescription();
    $text              = "# title";
    $description->setDescription($text);
    $this->assertEquals($text, $description->render());
  }
}
