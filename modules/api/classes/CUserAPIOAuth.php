<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

/**
 * Description
 */
class CUserAPIOAuth extends CUserAPI {
  //db field
  public $token;
  public $token_refresh;
  public $expiration_date;
  public $subscribe;

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                    = parent::getProps();
    $props["token"]           = "str notNull";
    $props["token_refresh"]   = "str notNull";
    $props["expiration_date"] = "dateTime";
    $props["subscribe"]       = "bool default|0";

    return $props;
  }
}
