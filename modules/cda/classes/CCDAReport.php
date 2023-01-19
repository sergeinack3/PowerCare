<?php

/**
 * @package Mediboard\appDeploy
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CStoredObject;
use Ox\Interop\AppDeploy\Resources\CAbstractItem;
use Ox\Interop\AppDeploy\Resources\CItem;
use Ox\Interop\Eai\CItemReport;
use Ox\Interop\Eai\CReport;

class CCDAReport extends CReport
{
    /** @var string */
    private const KEY_FAILED  = 'failed';
    /** @var string */
    private const KEY_STORED  = 'stored';
    /** @var string */
    private const KEY_SKIPPED = 'skipped';

    /** @var array */
    private $items = [
        self::KEY_FAILED  => [],
        self::KEY_SKIPPED => [],
        self::KEY_STORED  => [],
    ];

    /**
     * @param CStoredObject $object
     */
    public function addItemFailed(CStoredObject $object, string $err_msg): void
    {
        $this->items[self::KEY_FAILED][] = [$object, CAppUI::tr($err_msg)];
    }

    /**
     * @param array             $call
     * @param CItem[]|CItem     $items
     * @param array|string|null $errors
     * @param CItem[]|CItem     $impacted_items
     */
    public function addItemsIgnored(?CStoredObject $object, ?string $err_msg = null): void
    {
        $this->items[self::KEY_SKIPPED][] = [$object, $err_msg];
    }

    /**
     * @param array             $call
     * @param CItem[]|CItem     $items
     * @param array|string|null $errors
     * @param CItem[]|CItem     $impacted_items
     */
    public function addItemsStored(CStoredObject $object): void
    {
        $this->items[self::KEY_STORED][] = $object;
    }

    /**
     * @return void
     */
    public function makeReport(): void
    {
        // count stored
        $count_stored = count($this->items[self::KEY_STORED]);
        $this->addData(
            CAppUI::tr('CCDAReport-msg-count items stored', $count_stored),
            $count_stored ? CItemReport::SEVERITY_SUCCESS : CItemReport::SEVERITY_WARNING
        );

        // count loaded
        if ($count_skipped = count($this->items[self::KEY_SKIPPED])) {
            $this->addData(
                CAppUI::tr('CCDAReport-msg-count items skipped', $count_skipped),
                CItemReport::SEVERITY_WARNING
            );
        }

        // count failed
        $count_failed = count($this->items[self::KEY_FAILED]);
        $this->addData(
            CAppUI::tr('CCDAReport-msg-count items failed', $count_failed),
            $count_failed ? CItemReport::SEVERITY_ERROR : CItemReport::SEVERITY_SUCCESS
        );

        // items success
        $data = [];
        /** @var CStoredObject $item */
        foreach ($this->items[self::KEY_STORED] as $item) {
            $data[$item->_class][] = $item->_view;
        }
        $this->makeListItems($data, CItemReport::SEVERITY_SUCCESS);

        // items failed
        $data = [];
        foreach ($this->items[self::KEY_FAILED] as $item) {
            /** @var CStoredObject $object */
            $object = $item[0];
            $data[$object->_class][] = $item[1];
        }
        $this->makeListItems($data, CItemReport::SEVERITY_ERROR);
    }

    /**
     * @param array $data
     * @param int   $severity
     */
    private function makeListItems(array $data, int $severity): void
    {
        foreach ($data as $class => $values) {
            $main_item = new CItemReport(CAppUI::tr($class), $severity);
            foreach ($values as $value) {
                $item = new CItemReport($value, $severity);
                $main_item->addSubItem($item);
            }

            $this->addItem($main_item);
        }
    }
}
