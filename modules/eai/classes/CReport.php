<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Countable;
use Iterator;
use JsonSerializable;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\CompteRendu\CWkHtmlToPDFConverter;
use ReturnTypeWillChange;

/**
 * To allow to generate report after execute an action
 */
class CReport implements Countable, Iterator, JsonSerializable
{
    /** @var string */
    private $title;

    /** @var CItemReport[] */
    private $items = [];

    /** @var int */
    private $position;

    public function __construct(string $title)
    {
        $this->position         = 0;
        $this->title            = $title;
    }

    /**
     * Get title
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Add item to report
     *
     * @param string $data
     * @param int    $severity
     *
     * @return CReport
     */
    public function addData(string $data, int $severity): self
    {
        $this->items[] = new CItemReport($data, $severity);

        return $this;
    }

    /**
     * @param CItemReport $item
     *
     * @return CReport
     */
    public function addItem(CItemReport $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Add item to report
     *
     * @param CItemReport $item
     *
     * @return CItemReport[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Transforme report in JSON string
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this);
    }

    /**
     * Transform json report to a CReport
     *
     * @param string $json
     *
     * @return CReport
     */
    public static function toObject(string $json): CReport
    {
        $data  = json_decode($json, true);
        $title = utf8_decode(CMbArray::get($data, 'title', 'Report'));
        $report = new CReport($title);

        foreach (CMbArray::get($data, 'items', []) as $json_item) {
            $report->addItem((new CItemReport('', 0))->toObject($json_item));
        }

        return $report;
    }


    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        return $this->items[$this->position];
    }

    /**
     * @return void
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }


    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->position = 0;
        $this->items    = array_values($this->items);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }


    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $result = [
            'title' => utf8_encode($this->title),
            'items' => []
        ];
        foreach ($this->getItems() as $item) {
            $result['items'][] = json_encode($item);
        }

        return $result;
    }

    /**
     * @return string
     * @throws CMbException
     */
    public function getContentMail(): string
    {
        $step = intval($this->position) + 1;

        $smarty = new CSmartyDP('modules/eai');
        $smarty->assign('report', $this);
        $smarty->assign('title', CAppUI::tr('CDeployStep') . "({$step})");
        $content = $smarty->fetch('report/inc_report_mail');

        CWkHtmlToPDFConverter::init('CWkHtmlToPDFConverter');
        return CWkHtmlToPDFConverter::convert($content, 'a4', 'portrait');
    }
}
