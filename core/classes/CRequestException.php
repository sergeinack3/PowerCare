<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * Description
 */
class CRequestException extends CMbException {
  /**
   * @param string $group_by
   *
   * @return self
   */
  public static function nonAllowedGroupBy(string $group_by): self {
    return new self("CRequestException-error-Non allowed GROUP BY expression: '%s'", $group_by);
  }

  /**
   * @param string $order_by
   *
   * @return self
   */
  public static function nonAllowedOrderBy(string $order_by): self {
    return new self("CRequestException-error-Non allowed ORDER BY expression: '%s'", $order_by);
  }

  /**
   * @param string $limit
   *
   * @return self
   */
  public static function nonAllowedLimit(string $limit): self {
    return new self("CRequestException-error-Non allowed LIMIT expression: '%s'", $limit);
  }
}
