<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Mapper;

use DateTime;
use Exception;
use Generator;
use Ox\Core\CMbDT;
use Ox\Core\FieldSpecs\CCodeSpec;
use Ox\Import\Framework\Adapter\AdapterInterface;
use Ox\Import\Framework\Configuration\ConfigurableInterface;
use Ox\Import\Framework\Configuration\ConfigurationTrait;
use Ox\Import\Framework\Entity\EntityInterface;

/**
 * Description
 */
abstract class AbstractMapper implements MapperInterface, ConfigurableInterface
{
    use ConfigurationTrait;

    protected const TEL_REGEX   = '/^\d?(\d{2}[\s\.\-]?){5}$/';
    protected const EMAIL_REGEX = '/^[-a-z0-9\._\+]+@[-a-z0-9\.]+\.[a-z]{2,4}$/i';

    /** @var MapperMetadata */
    protected $metadata;

    /** @var AdapterInterface */
    protected $adapter;

    /** @var array */
    protected $conditions = [];

    // 15 minutes in seconds
    private const DURATION_SECOND = 900;

    /**
     * AbstractMapper constructor.
     *
     * @param MapperMetadata|null   $metadata
     * @param AdapterInterface|null $adapter
     */
    public function __construct(?MapperMetadata $metadata = null, ?AdapterInterface $adapter = null)
    {
        $this->metadata = $metadata;
        $this->adapter  = $adapter;
    }

    /**
     * @inheritDoc
     */
    public function retrieve($id): ?EntityInterface
    {
        if (
            $row = $this->adapter->retrieve(
                $this->metadata->getCollectionName(),
                $this->metadata->getIdentifier(),
                $id,
                $this->metadata->getConditions(),
                $this->metadata->getSelect()
            )
        ) {
            return $this->createEntity($row);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function get(int $count = 1, int $offset = 0, $id = null): ?Generator
    {
        // TODO Add condition on ID
        $rows = $this->adapter->get(
            $this->metadata->getCollectionName(),
            $count,
            $offset,
            $this->metadata->getConditions(),
            $this->metadata->getSelect()
        );

        if ($rows) {
            foreach ($rows as $row) {
                yield $this->createEntity($row);
            }
        }
    }

    public function count(): int
    {
        // TODO Add condition on ID
        return $this->adapter->count(
            $this->metadata->getCollectionName(),
            $this->metadata->getConditions(),
        );
    }

    /**
     * @inheritDoc
     */
    public function setMetadata(MapperMetadata $metadata): void
    {
        $this->metadata = $metadata;
    }

    /**
     * @inheritDoc
     */
    public function setAdapter(AdapterInterface $adapter): void
    {
        $this->adapter = $adapter;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(): MapperMetadata
    {
        if (!$this->metadata instanceof MapperMetadata) {
            throw new Exception('no metadata');
        }

        return $this->metadata;
    }

    /**
     * @inheritDoc
     */
    public function getAdapter(): AdapterInterface
    {
        if (!$this->adapter instanceof AdapterInterface) {
            throw new Exception('no adapter');
        }

        return $this->adapter;
    }

    /**
     * @param array $row
     *
     * @return EntityInterface
     */
    abstract protected function createEntity($row): EntityInterface;

    /**
     * @param mixed|null $id
     *
     * @return array
     */
    protected function getConditions($id = null): array
    {
        if ($id) {
            $this->conditions[$this->metadata->getIdentifier()] = "> '{$id}'";
        }

        return $this->conditions;
    }

    public function setConditions(array $conditions): void
    {
        $this->conditions = $conditions;
    }

    /**
     * @param string|null $datetime
     *
     * @return DateTime|null
     * @throws Exception
     */
    protected function convertToDateTime(?string $datetime = null): ?DateTime
    {
        return ($datetime) ? new DateTime($datetime) : null;
    }

    /**
     * @param string $duration_time
     *
     * @return int
     */
    protected function convertToDuration(string $duration_time): int
    {
        return round((CMbDT::durationSecond('00:00:00', $duration_time) / self::DURATION_SECOND));
    }

    protected function sanitizeTel(?string $tel): ?string
    {
        if (!$tel) {
            return null;
        }

        $tel = preg_replace(['/^\+33/', '/\D/'], '', $tel);

        if (strlen($tel) === 9 && strpos($tel, '0') !== 0) {
            $tel = '0' . $tel;
        }

        if (strlen($tel) < 10 || strlen($tel) > 10) {
            return null;
        }

        return $tel;
    }

    /**
     * TODO Avoid using CCodeSpec
     *
     * @param string|null $insee
     *
     * @return bool
     */
    protected function checkInsee(?string $insee): bool
    {
        return !(CCodeSpec::checkInsee($insee));
    }

    protected function getValue(array $row, string $field_name): ?string
    {
        return (isset($row[$field_name]) && $row[$field_name] !== '') ? $row[$field_name] : null;
    }
}
