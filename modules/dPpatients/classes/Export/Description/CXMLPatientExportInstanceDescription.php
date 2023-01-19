<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Export\Description;

use ArrayAccess;
use Countable;
use Iterator;
use Ox\Core\CClassMap;
use Ox\Core\CItemsIteratorTrait;
use Ox\Core\CMbException;
use Ox\Core\CStoredObject;

/**
 * Description of an instance
 */
class CXMLPatientExportInstanceDescription implements ArrayAccess, Countable, Iterator
{
    use CItemsIteratorTrait {
        offsetSet as traitOffsetSet;
    }

    /** @var string */
    private $short_class_name;

    /** @var string */
    private $path;

    /** @var CStoredObject */
    private $instance;

    /** @var CXMLPatientExportFieldDescription[] */
    private $items = [];

    public function __construct(CStoredObject $instance)
    {
        $this->instance         = $instance;
        $this->short_class_name = CClassMap::getSN($instance);
        $this->path = $this->buildPath();
    }

    /**
     * @throws CMbException
     */
    public function add(string $field_name): void
    {
        $this[$field_name] = new CXMLPatientExportFieldDescription($this->instance, $field_name);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @param $offset
     * @param $value
     *
     * @return void
     * @throws CMbException
     */
    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof CXMLPatientExportFieldDescription) {
            throw new CMbException(
                'CXMLPatientExportInstanceDescription-Error-Value is not a CXMLPatientExportFieldDescription'
            );
        }

        $this->traitOffsetSet($offset, $value);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getShortClassName(): string
    {
        return $this->short_class_name;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    private function buildPath(): string
    {
        return sprintf('//object[@class="%s"]', $this->short_class_name);
    }
}
