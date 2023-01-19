<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Import;

use Exception;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Sample\Entities\CSampleCategory;

/**
 * Import the categories from a json file.
 */
class SampleCategoryImport extends AbstractObjectImport
{
    public const FILE_PATH = 'resources/Import/sample_categories.json';
    public const ROOT_NODE = 'categories';
    public const NAME_NODE = 'name';

    /**
     * Get the content of the json file.
     * This method is protected for the unit tests to be able to mock it and don't relie on files.
     */
    protected function getFileContent(): string
    {
        return file_get_contents(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . self::FILE_PATH);
    }

    /**
     * Import a single category identified by $category_name if it is not in the cache array.
     * After creating a category add it to the cache.
     *
     * @throws Exception
     */
    protected function importObject(array $data): bool
    {
        if (($name = $data[self::NAME_NODE] ?? null) === null) {
            return false;
        }

        if (!isset($this->objects_cache[$name])) {
            $category         = new CSampleCategory();
            $category->name   = $name;
            $category->color  = $this->getRandomColor();
            $category->active = '1';
            if ($msg = $category->store()) {
                $this->errors[] = $msg;

                return false;
            }

            $this->objects_cache[$name] = $category;
        }

        return true;
    }

    /**
     * Get a random color to add to the category.
     */
    private function getRandomColor(): string
    {
        return sprintf('%02X%02X%02X', rand(0, 255), rand(0, 255), rand(0, 255));
    }

    /**
     * @inheritDoc
     */
    protected function getNameNode(): string
    {
        return self::NAME_NODE;
    }

    /**
     * @inheritDoc
     */
    protected function getRootNode(): string
    {
        return self::ROOT_NODE;
    }

    /**
     * @inheritDoc
     */
    protected function getObjectInstance(): CStoredObject
    {
        return new CSampleCategory();
    }

    /**
     * @inheritDoc
     */
    protected function getFieldName(): string
    {
        return self::NAME_NODE;
    }
}
