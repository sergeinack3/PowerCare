<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Adapter;

use DirectoryIterator;
use Generator;
use Ox\Core\CSQLDataSource;
use Ox\Import\Framework\Configuration\ConfigurableInterface;
use Ox\Import\Framework\Configuration\ConfigurationTrait;

/**
 * Description
 */
class XmlAdapter implements AdapterInterface, ConfigurableInterface
{
    use ConfigurationTrait;

    /** @var string */
    public static $current_file;

    /** @var CSQLDataSource */
    private $dir_path;

    /** @var int */
    private $start = 0;

    /** @var DirectoryIterator */
    private $dir_iterator;

    public function __construct(string $dir_path, int $start = 0)
    {
        $this->dir_path = $dir_path;
        $this->start    = $start;
    }

    /**
     * @inheritDoc
     */
    public function retrieve(string $collection, string $identifier, $id, array $conditions = [], array $select = [])
    {
        if (!$id) {
            return [];
        }

        if ($collection === 'utilisateur') {
            return ['external_id' => $id];
        }

        return static::$current_file;
    }

    /**
     * @inheritDoc
     */
    public function get(
        string $collection,
        int $count = 1,
        int $offset = 0,
        ?array $conditions = [],
        ?array $select = []
    ): ?Generator {
        $this->dir_iterator = new DirectoryIterator($this->dir_path);

        while ($this->dir_iterator->isDot()) {
            $this->dir_iterator->next();
        }

        for ($i = 0; $i < $this->start; $i++) {
            $this->dir_iterator->next();
        }

        $xml_file_name = $this->getConfiguration()['xml_file_name'];

        $current_count = 0;
        while ($current_dir = $this->dir_iterator->current()) {
            if ($current_count++ >= $count) {
                break;
            }

            $xml_file = $current_dir->getPathname() . '/' . $xml_file_name;

            if (!is_file($xml_file)) {
                continue;
            }

            static::$current_file = $xml_file;

            yield $xml_file;

            $this->dir_iterator->next();
        }
    }

    public function count(string $collection, ?array $conditions = []): int
    {
        return 0;
    }
}
