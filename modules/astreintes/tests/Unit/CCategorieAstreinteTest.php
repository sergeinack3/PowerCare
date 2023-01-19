<?php

/**
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Astreintes\Tests\Unit;

use Exception;
use Ox\Mediboard\Astreintes\CCategorieAstreinte;
use Ox\Mediboard\Astreintes\Tests\Fixtures\AstreintesFixtures;
use Ox\Tests\OxUnitTestCase;

class CCategorieAstreinteTest extends OxUnitTestCase
{
    /**
     * Test to get the array of the categories names
     * @throws Exception
     * @dataProvider categoriesAstreintesProvider
     */
    public function testGetPrefCategories(CCategorieAstreinte $categorie_astreinte): void
    {
        $cat_names = CCategorieAstreinte::getPrefCategories();

        $this->assertArrayHasKey($categorie_astreinte->_id, $cat_names);
    }

    /**
     * Test to get the categorie name
     *
     * @param CCategorieAstreinte $categorie
     * @param string              $expected
     *
     * @throws Exception
     * @dataProvider categoriesAstreintesProvider
     */
    public function testGetName(CCategorieAstreinte $categorie, string $expected): void
    {
        $actual = CCategorieAstreinte::getName($categorie->_id);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param array $expected
     *
     * @dataProvider listCategoriesProvider
     * @throws Exception
     */
    public function testLoadListCategoriesContainsHasKey(CCategorieAstreinte $expected): void
    {
        $actual = CCategorieAstreinte::loadListCategories();

        $this->assertArrayHasKey($expected->_id, $actual);
    }

    /**
     * @throws Exception
     */
    public function categoriesAstreintesProvider(): array
    {
        /** @var CCategorieAstreinte $lorem */
        $lorem = $this->getObjectFromFixturesReference(
            CCategorieAstreinte::class,
            AstreintesFixtures::TAG_CAT_LOREM
        );

        /** @var CCategorieAstreinte $ipsum */
        $ipsum = $this->getObjectFromFixturesReference(
            CCategorieAstreinte::class,
            AstreintesFixtures::TAG_CAT_IPSUM
        );

        return [
            "lorem" => [
                $lorem,
                AstreintesFixtures::TAG_CAT_LOREM,
            ],
            "ipsum" => [
                $ipsum,
                AstreintesFixtures::TAG_CAT_IPSUM,
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function listCategoriesProvider(): array
    {
        $cat_lorem = $this->getObjectFromFixturesReference(
            CCategorieAstreinte::class,
            AstreintesFixtures::TAG_CAT_LOREM
        );
        $cat_ipsum = $this->getObjectFromFixturesReference(
            CCategorieAstreinte::class,
            AstreintesFixtures::TAG_CAT_IPSUM
        );

        return [
            "cat_lorem" => [$cat_lorem],
            "cat_ipsum" => [$cat_ipsum],
        ];
    }
}
