<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Elastic;

use Ox\Core\Elastic\ElasticObjectMappings;
use Ox\Core\Elastic\Encoding;
use Ox\Core\Elastic\Exceptions\ElasticMappingException;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\OxUnitTestCase;

class ElasticObjectMappingsTest extends OxUnitTestCase
{
    public function testBasicStructure(): void
    {
        $mappings   = new ElasticObjectMappings();
        $baseFields = count($mappings);

        $mappings->setDefaultMapping(false)
            ->addIntField("test")
            ->addDateField("test2")
            ->addArrayField("test3")
            ->addFloatField("test4")
            ->addIntField("test5")
            ->addStringField("test6", Encoding::ISO_8859_1)
            ->addStringField("test7")
            ->addRefField("ref1", CMediusers::class, false)
            ->addRefField("ref2", CUser::class);

        $total = $baseFields + 9;
        self::assertEquals(2, count($mappings->getReferences()));
        self::assertEquals($total, count($mappings));
        self::assertEquals($total, count($mappings->getFields()));
        self::assertFalse($mappings->isDefaultMapping());
    }

    public function testEncodingStringFieldToNone(): void
    {
        $this->expectException(ElasticMappingException::class);

        $mappings = new ElasticObjectMappings();
        $mappings->addStringField("none", Encoding::NONE);
    }
}
