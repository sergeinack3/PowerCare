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
trait RequestTrait {
  // Constants are not allowed in Traits

  /** @var string */
  private static $group_by_regexp = '^((`?)((?<=`)[\w\s]+(?=`)|\w+)\2\.)?(`?)\w+\4$';

  /** @var string */
  private static $order_by_regexp = '^\-?((`?)((?<=`)[\w\s]+(?=`)|\w+)\2\.)?((`?)\w+\5)(\s+(ASC|DESC))?$';

  /** @var string */
  private static $limit_regexp = '^\d+((\s*\,\s*\d+)|\s+OFFSET\s+\d+)?$';

  /**
   * @param string $group_by
   *
   * @return void
   * @throws CRequestException
   */
  protected function checkGroupBy(string $group_by): void {
    $_group_by_array = explode(',', $group_by);

    foreach ($_group_by_array as $_group) {
      $_group = trim($_group);

      if (!preg_match('/' . self::$group_by_regexp . '/i', $_group)) {
        throw CRequestException::nonAllowedGroupBy($group_by);
      }
    }
  }

  /**
   * @param string $order_by
   *
   * @return void
   * @throws CRequestException
   */
  protected function checkOrderBy(string $order_by): void {
    $_order_by_array = explode(',', $order_by);

    foreach ($_order_by_array as $_order) {
      $_order = trim($_order);

      if (!preg_match('/' . self::$order_by_regexp . '/i', $_order)) {
        throw CRequestException::nonAllowedOrderBy($order_by);
      }
    }
  }

  /**
   * @param string $limit
   *
   * @return void
   * @throws CRequestException
   */
  protected function checkLimit(string $limit): void {
    if (!preg_match('/' . self::$limit_regexp . '/i', trim($limit))) {
      throw CRequestException::nonAllowedLimit($limit);
    }
  }
}
