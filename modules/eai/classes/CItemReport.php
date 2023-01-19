<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use JsonSerializable;
use Ox\Core\CMbArray;
use ReturnTypeWillChange;

/**
 * Represent item in the report
 */
class CItemReport implements JsonSerializable
{

    public const SEVERITY_ERROR   = 1;
    public const SEVERITY_WARNING = 2;
    public const SEVERITY_SUCCESS = 3;
    /** @var string */
    private $severity;
    /** @var string */
    private $data;
    /** @var CItemReport[] */
    private $sub_items = [];

    public function __construct(string $data, int $severity)
    {
        $this->severity = $severity;
        $this->data     = $data;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public static function toObject(string $data): self
    {
        $data      = json_decode($data, true);
        $severity  = CMbArray::get($data, 'severity', self::SEVERITY_ERROR);
        $data_item = utf8_decode(CMbArray::get($data, 'data', ''));
        $item      = new self($data_item, $severity);

        foreach (CMbArray::get($data, 'sub_items', []) as $sub_item) {
            $item->sub_items[] = (new self('', 1))->toObject($sub_item);
        }

        return $item;
    }

    /**
     * Get data
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * Get severity
     * @return int
     */
    public function getSeverity(): int
    {
        return $this->severity;
    }

    /**
     * @param CItemReport $item
     *
     * @return $this
     */
    public function addSubItem(CItemReport $item): self
    {
        $this->sub_items[] = $item;

        return $this;
    }

    /**
     * @param string $data
     * @param int    $severity
     *
     * @return $this
     */
    public function addSubData(string $data, int $severity): self
    {
        $this->sub_items[] = new CItemReport($data, $severity);

        return $this;
    }

    /**
     * @return CItemReport[]
     */
    public function getSubItems(): array
    {
        return $this->sub_items;
    }


    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $result = [
            'data'     => utf8_encode($this->data),
            'severity' => $this->severity,
        ];

        foreach ($this->sub_items as $sub_item) {
            $result['sub_items'][] = json_encode($sub_item);
        }

        return $result;
    }
}
