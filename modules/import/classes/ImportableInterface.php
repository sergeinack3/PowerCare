<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework;

use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;

/**
 * Description
 */
interface ImportableInterface
{
    /**
     * @param MatcherVisitorInterface $matcher
     *
     * @return ImportableInterface
     */
    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface;

    /**
     * @param PersisterVisitorInterface $persister
     *
     * @return ImportableInterface
     */
    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface;
}
