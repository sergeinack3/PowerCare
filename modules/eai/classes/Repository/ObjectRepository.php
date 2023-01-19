<?php

/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Repository;

use Ox\Core\CMbException;
use Ox\Core\CStoredObject;

abstract class ObjectRepository
{
    /** @var string[] */
    public const STRATEGIES = [];

    /** @var string */
    public const STRATEGY_BEST = 'best';

    /** @var string[] */
    protected array $strategies = [];

    /**
     * @param string ...$strategies
     *
     * @throws CMbException
     */
    public function __construct(string ...$strategies)
    {
        if (!$strategies) {
            $strategies = [self::STRATEGY_BEST];
        }

        foreach ($strategies as $strategy) {
            $strategy = strtolower($strategy);
            if (!in_array($strategy, $this::STRATEGIES)) {
                throw new CMbException('PatientLocator-msg-Strategy given is invalid', $strategy);
            }

            if (!in_array($strategy, $this->strategies)) {
                $this->strategies[] = $strategy;
            }
        }
    }

    /** Find object */
    abstract public function find(): ?CStoredObject;

    /** Get object if already found or try to find it */
    abstract public function getOrFind(): ?CStoredObject;
}
