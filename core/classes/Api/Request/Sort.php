<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request;

use Ox\Core\CMbException;

/**
 * Sort object used for building RequestApi.
 */
class Sort
{
    /** @var string */
    public const SORT_ASC = 'asc';

    /** @var string */
    public const SORT_DESC = 'desc';

    /** @var string */
    private $field;

    /** @var string */
    private $order;

    /**
     * @throws CMbException
     */
    public function __construct(string $field, string $order = self::SORT_ASC)
    {
        if (!in_array($order, [static::SORT_ASC, static::SORT_DESC])) {
            throw new CMbException('Sort-Error-Order-must-be-in', [static::SORT_ASC, static::SORT_DESC]);
        }

        $this->field = $field;
        $this->order = $order;
    }

    public function __toString(): string
    {
        return ($this->order === static::SORT_ASC ? '+' : '-') . $this->field;
    }

    public function toSql(): string
    {
        return "`{$this->field}` {$this->order}";
    }
}
