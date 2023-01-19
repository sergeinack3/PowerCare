<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic\QueryBuilder;

use MyCLabs\Enum\Enum;

/**
 * Elasticsearch operators Enumeration for range filter
 * @description This enum is used to restrict the different operators
 *              to avoid searching all the possibilities in the Elasticsearch documentation.
 *              And provides autocomplete for range filters.
 *
 * Todo: Replace by Enum in PHP 8.1
 * @method static static GREATER_THAN_OR_EQUAL_TO()
 * @method static static GREATER_THAN()
 * @method static static LESS_THAN_OR_EQUAL_TO()
 * @method static static LESS_THAN()
 */
class RangeOperator extends Enum
{
    private const GREATER_THAN_OR_EQUAL_TO = 'gte';
    private const GREATER_THAN             = 'gt';
    private const LESS_THAN_OR_EQUAL_TO    = 'lte';
    private const LESS_THAN                = 'lt';
}
