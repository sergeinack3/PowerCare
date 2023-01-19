<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\dPcim10\Tests\Unit;

use Ox\Mediboard\Cim10\Drc\CDRC;
use Ox\Mediboard\Cim10\Drc\CDRCCriticalDiagnosis;
use Ox\Mediboard\Cim10\Drc\CDRCResultClass;
use Ox\Mediboard\Cim10\Drc\CDRCTranscodingCriterion;
use Ox\Tests\OxUnitTestCase;

class CDRCTest extends OxUnitTestCase
{
    public function testLoadCriticalDiagonosis(): void
    {
        $expected               = new CDRCCriticalDiagnosis();
        $expected->diagnosis_id = 1;
        $expected->libelle      = "Abus de médicaments";
        $expected->criticality  = 3;
        $expected->group        = 1;
        $this->assertEquals($expected, new CDRCCriticalDiagnosis(1, CDRC::LOAD_LITE));
    }

    public function testLoadResultClass(): void
    {
        $expected            = new CDRCResultClass();
        $expected->class_id  = 3;
        $expected->text      = "tumeurs";
        $expected->chapter   = "(C00-D48)";
        $expected->libelle   = "Cancers et Tumeurs";
        $expected->beginning = "C00";
        $expected->end       = "D48";
        $this->assertEquals($expected, new CDRCResultClass(3, CDRC::LOAD_LITE));
    }

    public function testLoadTranscodingCriterion(): void
    {
        $expected                           = new CDRCTranscodingCriterion();
        $expected->transcoding_criterion_id = 1;
        $expected->transcoding_id           = 2;
        $expected->criterion_id             = 60;
        $expected->condition                = 1;
        $this->assertEquals($expected, new CDRCTranscodingCriterion(1, CDRC::LOAD_LITE));
    }
}
