<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbException;
use Ox\Mediboard\Admin\CMbInvalidCredentialsException;
use Ox\Mediboard\Admin\CSourceLDAP;

/**
 * Description
 */
class CJeeboxLDAPService implements IShortNameAutoloadable {

  /**
   * @var CSourceLDAP The LDAP source
   */
  protected static $source;

  /**
   * @param CJeeboxLDAPRecipient $query The search query
   *
   * @return CJeeboxLDAPRecipient[]
   * @throws CMbException
   * @throws CMbInvalidCredentialsException
   */
  public static function search($query) {
    self::getSource();

    if (!self::$source->_id) {
      return false;
    }

    $link_id = self::$source->ldap_connect();

    if (!$link_id) {
      return false;
    }

    if (!self::$source->ldap_bind($link_id)) {
      return false;
    }

    $data = self::$source->ldap_search($link_id, $query->makeQuery(), $query->getAttributes());
    $results = array();

    for ($i = 0; $i < $data['count']; $i++) {
      $results[] = new CJeeboxLDAPRecipient($data[$i], true);
    }

    return $results;
  }

  /**
   * Return the LDAP source
   *
   * @return CSourceLDAP
   */
  protected static function getSource() {
    if (!self::$source) {
      $source       = new CSourceLDAP();
      $source->name = 'messagerie ldap directory';
      $source->loadMatchingObject();

      if (!$source->_id) {
        $source = self::createSource();
      }
      self::$source = $source;
    }

    return self::$source;
  }
}
