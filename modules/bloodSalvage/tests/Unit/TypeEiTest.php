<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\BloodSalvage\tests\Unit;

use Exception;
use Ox\Core\CStoredObject;
use Ox\Mediboard\BloodSalvage\CTypeEi;
use Ox\Mediboard\BloodSalvage\Tests\Fixtures\BloodSalvageFixtures;
use Ox\Mediboard\Qualite\CEiItem;
use Ox\Tests\OxUnitTestCase;

/**
 * @group schedules
 */
class TypeEiTest extends OxUnitTestCase
{
    /**
     * @throws Exception
     * @dataProvider loadRefItemsProvider
     */
    public function testLoadRefItems(array $items, string $evenements = null): void
    {
        /** @var CTypeEi $type_ei */
        $type_ei = $this->getTypeEi();

        if ($evenements) {
            $type_ei->evenements = $evenements;
        }

        $type_ei->updateFormFields();
        $type_ei->loadRefItems();
        $this->assertEquals($items, $type_ei->_ref_items);
    }

    /**
     * @throws Exception
     */
    public function loadRefItemsProvider(): array
    {
        return [
            'ok'                => [
                $this->getEiItems(),
            ],
            'not_existing_item' => [
                [new CEiItem()],
                uniqid(),
            ],
        ];
    }


    /**
     * @throws Exception
     */
    protected function getTypeEi(): CStoredObject
    {
        return $this->getObjectFromFixturesReference(CTypeEi::class, BloodSalvageFixtures::TAG_TYPE_EI);
    }

    /**
     * @throws Exception
     */
    protected function getEiItems(): array
    {
        return [
            $this->getObjectFromFixturesReference(CEiItem::class, BloodSalvageFixtures::TAG_ITEM_EI1),
            $this->getObjectFromFixturesReference(CEiItem::class, BloodSalvageFixtures::TAG_ITEM_EI2),
        ];
    }
}
