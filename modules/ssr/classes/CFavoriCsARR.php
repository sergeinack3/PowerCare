<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\Cache;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Description
 */
class CFavoriCsARR extends CMbObject {
  const GET_FOR_CACHE = 'CFavoriCsARR.getFor';

  /** @var integer Primary key */
  public $favoris_csarr_id;

  /** @var integer The user's id */
  public $user_id;

  /** @var string The CsARR code */
  public $code;

  /** @var CActiviteCsARR */
  public $_ref_activite_csarr;

  /** @var CMediusers */
  public $_ref_user;

  /**
   * @inheritdoc
   */
  public function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "favoris_csarr";
    $spec->key   = "favori_csarr_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  public function getProps() {
    $props = parent::getProps();

    $props['user_id'] = 'ref notNull class|CMediusers back|favoris_csarr';
    $props['code']    = 'str notNull maxLength|7 seekable';

    return $props;
  }

  /**
   * @inheritdoc
   */
  public function store() {
    /* Delete the cached favoris of the user uppon creation of a favori */
    if (!$this->_id || $this->fieldModified('code')) {
      self::resetFavorisCache($this->user_id);
    }

    return parent::store();
  }

  /**
   * @inheritdoc
   */
  public function delete() {
    /* Delete the cached favoris of the user uppon deletion of a favori */
    self::resetFavorisCache($this->user_id);

    return parent::delete();
  }

  /**
   * Load the CsARR activity
   *
   * @return CActiviteCsARR
   */
  public function loadRefActiviteCsarr() {
    return $this->_ref_activite_csarr = CActiviteCsARR::get($this->code);
  }

  /**
   * Load the linked user
   *
   * @return CMediusers
   */
  public function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef('user_id');
  }

  /**
   * Load the favoris for the given user (with the given tag if set)
   *
   * @param CMediusers $user   The user
   * @param integer    $tag_id An optional tag id
   *
   * @return CFavoriCsARR[]
   */
  public static function getFor($user, $tag_id = null) {
    $favori = new self;

    $cache = new Cache(self::GET_FOR_CACHE, array($user->_id, $tag_id), Cache::INNER_OUTER);

    $favoris = array();

    if ($cache->exists()) {
      $favoris = $cache->get();
    }
    else {
      $where = array(
        'user_id' => " = '$user->_id'"
      );

      $ljoin = array();
      if ($tag_id) {
        $ljoin['tag_item']              = 'tag_item.object_id = favori_csarr_id';
        $where['tag_item.tag_id']       = " = '$tag_id'";
        $where['tag_item.object_class'] = " = 'CFavoriCsARR'";
      }

      $favoris = $favori->loadList($where, 'code', 100, 'code', $ljoin);
      $cache->put($favoris);
    }

    return $favoris;
  }

  /**
   * Get the favoris with the given code for the given user
   *
   * @param string     $code The code
   * @param CMediusers $user The user
   *
   * @return CFavoriCsARR
   */
  public static function getFromCode($code, $user) {
    $favori          = new self;
    $favori->code    = $code;
    $favori->user_id = $user->_id;
    $favori->loadMatchingObject();

    if ($favori->_id) {
      $favori->loadRefsTagItems();
    }

    return $favori;
  }

  /**
   * Search among the given user's favorites CsARR codes
   *
   * @param CMediusers $user      The user
   * @param string     $keywords  The keywords to find
   * @param string     $code      Search only in the code field
   * @param string     $hierarchy A hierarchy code (can be partial)
   * @param int        $start     The starting offset
   * @param int        $rows      The number of results to return
   *
   * @return CActiviteCsARR[]
   */
  public static function findCodes($user, $keywords = null, $code = null, $hierarchy = null, $start = 0, $rows = 20) {
    $favoris = self::getFor($user);

    $codes = array();

    if (count($favoris)) {
      $where = array('`code` ' . CSQLDataSource::prepareIn(CMbArray::pluck($favoris, 'code')));
      $codes = CActiviteCsARR::findCodes($keywords, $code = null, $hierarchy = null, $where, $start, $rows);

      foreach ($codes as $code) {
        foreach ($favoris as $key => $favori) {
          if ($code->code == $favori->code) {
            $code->_favori_id = $favori->_id;
            unset($favoris[$key]);
            break;
          }
        }
      }
    }

    return $codes;
  }

  /**
   * Delete the cached favori for the given user
   *
   * @param integer $user_id The id of the Mediuser
   * @param integer $tag_id  An optional tag id
   *
   * @return void
   */
  public static function resetFavorisCache($user_id, $tag_id = null) {
    $cache = new Cache(self::GET_FOR_CACHE, array($user_id, $tag_id), Cache::INNER_OUTER);
    if ($cache->exists()) {
      $cache->rem();
    }
  }
}
