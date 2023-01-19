<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Import;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Sample\Entities\CSampleCategory;

/**
 * Description
 */
abstract class AbstractObjectImport
{
    protected $objects_cache = [];
    protected $errors        = [];

    /**
     * Get the name of the "name" node.
     * The name node is the node used for massloading.
     */
    abstract protected function getNameNode(): string;

    /**
     * Get the name of the root node.
     * The root node is the root node of the collection of objects.
     */
    abstract protected function getRootNode(): string;

    /**
     * Get the content of the file as json.
     */
    abstract protected function getFileContent(): string;

    /**
     * Get an instance of the object to import.
     */
    abstract protected function getObjectInstance(): CStoredObject;

    /**
     * Get the name of the "name" field used for massLoading.
     */
    abstract protected function getFieldName(): string;

    /**
     * Import an instance of object if it's not in the $object_cache array.
     */
    abstract protected function importObject(array $data): bool;

    /**
     * Import the objects from a file.
     * When the file is loaded objects are massloaded to avoir unitary queries (eg. loadMatching).
     *
     * @throws Exception
     */
    public function import(): int
    {
        $count = 0;
        $datas = $this->extractData();

        $this->massLoadObjectsByName(CMbArray::pluck($datas, $this->getNameNode()));

        foreach ($datas as $data) {
            if (isset($data[$this->getNameNode()])) {
                if ($this->importObject($data)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Extract the data from the json file and convert it to an array.
     */
    protected function extractData(): array
    {
        $json_content = $this->getFileContent();

        $decoded = json_decode($json_content, true);

        return isset($decoded[$this->getRootNode()])
            ? CMbArray::mapRecursive('utf8_decode', $decoded[$this->getRootNode()])
            : [];
    }

    /**
     * Load all the categories with a name in the $names array in one query.
     * Add all the categories to $this->objects_cache which is a cache to avoid making too many queries.
     *
     * @throws Exception
     */
    private function massLoadObjectsByName(array $datas): void
    {
        $datas = CMbArray::mapRecursive('addslashes', $datas);

        $field_name = $this->getFieldName();

        $instance = $this->getObjectInstance();
        $where    = [$field_name => $instance->getDS()->prepareIn($datas)];

        /** @var CStoredObject $obj */
        foreach ($instance->loadList($where) as $obj) {
            if (!isset($this->objects_cache[$obj->{$field_name}])) {
                $this->objects_cache[$obj->{$field_name}] = $obj;
            }
        }
    }

    /**
     * Get the errors that happenned during the store of categories.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
