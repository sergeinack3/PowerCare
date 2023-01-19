<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Handlers\Events;

use MyCLabs\Enum\Enum;

/**
 * Handler (Observer) events enumeration
 *
 * @method static static BEFORE_STORE()
 * @method static static AFTER_STORE()
 *
 * @method static static BEFORE_DELETE()
 * @method static static AFTER_DELETE()
 *
 * @method static static BEFORE_MERGE()
 * @method static static AFTER_MERGE()
 * @method static static MERGE_FAILURE()
 *
 * @method static static BEFORE_FILL_LIMITED_TEMPLATE()
 * @method static static AFTER_FILL_LIMITED_TEMPLATE()
 */
class ObjectHandlerEvent extends Enum
{
    private const BEFORE_STORE = 'onBeforeStore';
    private const AFTER_STORE  = 'onAfterStore';

    private const BEFORE_DELETE = 'onBeforeDelete';
    private const AFTER_DELETE  = 'onAfterDelete';

    private const BEFORE_MERGE  = 'onBeforeMerge';
    private const AFTER_MERGE   = 'onAfterMerge';
    private const MERGE_FAILURE = 'onMergeFailure';

    private const BEFORE_FILL_LIMITED_TEMPLATE = 'onBeforeFillLimitedTemplate';
    private const AFTER_FILL_LIMITED_TEMPLATE  = 'onAfterFillLimitedTemplate';
}
