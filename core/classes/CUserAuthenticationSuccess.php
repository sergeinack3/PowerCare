<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * User authentication success exception
 */
class CUserAuthenticationSuccess extends CMbException {
  /** @var int User ID to authenticate */
  public $user_id;

  /** @var string Authentication method */
  public $auth_method;

  /** @var bool Restrict to only one script */
  public $restricted = true;
}
