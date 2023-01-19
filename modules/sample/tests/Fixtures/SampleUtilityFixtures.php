<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Tests\Fixtures;


use Ox\Mediboard\Sample\Entities\CSampleCategory;
use Ox\Mediboard\Sample\Entities\CSampleNationality;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\GroupFixturesInterface;

class SampleUtilityFixtures extends Fixtures implements GroupFixturesInterface
{
    public const CATEGORY    = 'sample_category';
    public const CATEGORY_2  = 'sample_category_2';
    public const NATIONALITY = 'sample_nationality';

    public function load()
    {
        $category       = new CSampleCategory();
        $category->name = uniqid();
        $this->store($category, self::CATEGORY);

        $category       = new CSampleCategory();
        $category->name = uniqid();
        $this->store($category, self::CATEGORY_2);

        $nationality = new CSampleNationality();
        $nationality->name = 'Test nationality';
        $nationality->code = 'test';
        $this->store($nationality, self::NATIONALITY);
    }

    public static function getGroup(): array
    {
        return ['sample_fixtures', 300];
    }

}
