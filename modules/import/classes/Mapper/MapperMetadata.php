<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Mapper;

use Ox\Import\Framework\Configuration\ConfigurableInterface;
use Ox\Import\Framework\Configuration\Configuration;
use Ox\Import\Framework\Configuration\ConfigurationTrait;

/**
 * Description
 */
class MapperMetadata implements ConfigurableInterface
{
    use ConfigurationTrait;

    /** @var string */
    private $collection_name;

    /** @var string|null */
    private $identifier;

    // TODO Create an array of Condition Object
    /** @var array */
    private $conditions = [];

    /** @var array */
    private $select = [];

    /** @var array  */
    private $group = [];

    /**
     * MapperMetadata constructor.
     *
     * @param string             $collection_name
     * @param string|null        $identifier
     * @param Configuration|null $configuration
     */
    public function __construct(
        string $collection_name,
        ?string $identifier = null,
        ?Configuration $configuration = null,
        array $conditions = [],
        array $select = [],
        array $group = []
    ) {
        $this->collection_name = $collection_name;
        $this->identifier      = $identifier;
        $this->configuration   = ($configuration) ?? new Configuration();
        $this->conditions      = $conditions;
        $this->select          = $select;
        $this->group           = $group;
    }

    /**
     * @param string      $collection_name
     * @param string|null $identifier
     *
     * @return self
     */
    public static function create(string $collection_name, ?string $identifier = null): self
    {
        return new self($collection_name, $identifier);
    }

    /**
     * @return string
     */
    public function getCollectionName(): string
    {
        return $this->collection_name;
    }

    /**
     * @return string|null
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function getSelect(): array
    {
        return $this->select;
    }

    public function getGroup(): array
    {
        return $this->group;
    }
}
