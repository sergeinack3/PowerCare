<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp\Tests\Unit;

use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Lpp\Repository\LppChapterRepository;
use Ox\Tests\OxUnitTestCase;

class LppChapterRepositoryTest extends OxUnitTestCase
{
    public function testGetLoadChapterQuery(): void
    {
        LppChapterRepository::setDatasource(CSQLDataSource::get('std'));
        $id = '01';

        $expected = "SELECT *
FROM `arborescence`
WHERE (`ID` = '{$id}')";

        $this->assertEquals($expected, LppChapterRepository::getInstance()->getLoadChapterQuery($id)->makeSelect());
    }

    public function testGetChaptersFromParentQuery(): void
    {
        LppChapterRepository::setDatasource(CSQLDataSource::get('std'));
        $id = '01';

        $expected = "SELECT *
FROM `arborescence`
WHERE (`PARENT` = '{$id}')
ORDER BY `INDEX` ASC";

        $this->assertEquals(
            $expected,
            LppChapterRepository::getInstance()->getChaptersFromParentQuery($id)->makeSelect()
        );
    }
}
