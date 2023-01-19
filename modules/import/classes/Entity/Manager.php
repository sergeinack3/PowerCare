<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Entity;

use Ox\Import\Framework\Configuration\ConfigurableInterface;
use Ox\Import\Framework\Configuration\Configuration;
use Ox\Import\Framework\Configuration\ConfigurationTrait;
use Ox\Import\Framework\Exception\ImportException;
use Ox\Import\Framework\Strategy\StrategyInterface;

/**
 * Description
 */
final class Manager implements ConfigurableInterface
{
    use ConfigurationTrait;

    /** @var StrategyInterface */
    private $strategy;

    /**
     * Manager constructor.
     *
     * @param StrategyInterface  $strategy
     * @param Configuration|null $configuration
     */
    public function __construct(StrategyInterface $strategy, ?Configuration $configuration = null)
    {
        $this->configuration = ($configuration) ?? new Configuration();

        $this->strategy = $strategy;

        if ($configuration && $this->strategy instanceof ConfigurableInterface) {
            $this->strategy->setConfiguration($configuration);
        }
    }

    public function getMessages(): array
    {
        return $this->strategy->getMessages();
    }

    public function getErrors(): array
    {
        return $this->strategy->getErrors();
    }

    /**
     * @param int        $count
     * @param mixed|null $id
     *
     * @return void
     * @throws ImportException
     */
    public function import(int $count = 1, int $offset = 0, $id = null): int
    {
        return $this->strategy->import($count, $offset, $id);
    }

    /**
     * @return mixed
     */
    public function getLastExternalId()
    {
        return $this->strategy->getLastExternalId();
    }
}
