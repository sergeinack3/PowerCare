<?php
/**
 * @package Mediboard\Maidis
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Maidis\Mapper;

use DateTime;
use Generator;
use Ox\Import\Framework\Adapter\MySqlAdapter;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Mapper\AbstractMapper;

/**
 * Description
 */
abstract class AbstractMaidisMapper extends AbstractMapper
{
    protected function convertDate(?string $date): ?DateTime
    {
        if ($date) {
            return (DateTime::createFromFormat('Ymd', $date)) ?: null;
        }

        return null;
    }

    protected function convertDateTime(?string $datetime): ?DateTime
    {
        if ($datetime) {
            if (preg_match('/^\d{8}\s(\d{2}:?){2}$/', $datetime)) {
                return (DateTime::createFromFormat('Ymd H:i', $datetime)) ?: null;
            }
            elseif (preg_match('/^\d{8}\s(\d{2}:?){3}$/', $datetime)) {
                return (DateTime::createFromFormat('Ymd H:i:s', $datetime)) ?: null;
            }
        }

        return null;
    }

    protected function buildInfosFromMultipleFields(...$remarques): ?string
    {
        $remarques = array_filter($remarques);

        return implode("\n", $remarques);
    }

    protected function sanitizeLine(?string $line): ?string
    {
        return str_replace('$', ' ', $line);
    }

    public function count(): int
    {
        if ($this->adapter instanceof MySqlAdapter) {
            return $this->adapter->count(
                $this->metadata->getCollectionName(),
                $this->metadata->getConditions(),
                $this->metadata->getGroup()
            );
        }

        return parent::count();
    }

    public function retrieve($id): ?EntityInterface
    {
        if ($this->adapter instanceof MySqlAdapter) {
            if ($row = $this->adapter->retrieve(
                $this->metadata->getCollectionName(),
                $this->metadata->getIdentifier(),
                $id,
                $this->metadata->getConditions(),
                $this->metadata->getSelect(),
                $this->metadata->getGroup()
            )) {
                return $this->createEntity($row);
            }

            return null;
        }

        return parent::retrieve($id);
    }

    public function get(int $count = 1, int $offset = 0, $id = null): ?Generator
    {
        if ($this->adapter instanceof MySqlAdapter) {
            $rows = $this->adapter->get(
                $this->metadata->getCollectionName(),
                $count,
                $offset,
                $this->metadata->getConditions(),
                $this->metadata->getSelect(),
                $this->metadata->getGroup()
            );

            if ($rows) {
                foreach ($rows as $row) {
                    yield $this->createEntity($row);
                }
            }
        }

        parent::get($count, $offset, $id);
    }
}
