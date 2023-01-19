<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Import;

use Exception;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Sample\Entities\CSampleNationality;

/**
 * Import the nationalities from a json file
 */
class SampleNationalityImport extends AbstractObjectImport
{
    public const FILE_PATH = 'resources/Import/sample_nationalities.json';
    public const NAME_NODE = 'name';
    public const ROOT_NODE = 'nationalities';

    /**
     * Import a nationality from the array $data ['name' => $name, 'code' => $code].
     *
     * @throws Exception
     */
    protected function importObject(array $data): bool
    {
        $name = $data['name'] ?? null;
        $code = $data['code'] ?? null;
        if ($name === null || $code === null) {
            return false;
        }

        if (!isset($this->objects_cache[$name])) {
            $nationality = new CSampleNationality();
            $nationality->name = $name;
            $nationality->code = $code;
            if ($msg = $nationality->store()) {
                $this->errors[] = $msg;
            }

            $this->objects_cache[$name] = $nationality;
        }

        return true;
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
    protected function getFileContent(): string
    {
        return file_get_contents(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . self::FILE_PATH);
    }

    /**
     * @inheritDoc
     */
    protected function getObjectInstance(): CStoredObject
    {
        return new CSampleNationality();
    }

    /**
     * @inheritDoc
     */
    protected function getFieldName(): string
    {
        return self::NAME_NODE;
    }
}
