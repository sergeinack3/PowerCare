<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10;

use Ox\Core\Cache;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Ccam\CFavoriCCAM;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Class CFavoriCIM10
 * @package Ox\Mediboard\Cim10
 */
class CFavoriCIM10 extends CMbObject
{
    const GET_LIST_FAVORIS_CACHE = 'CFavoriCIM10.getListFavoris';

    public $favoris_id;
    public $favoris_code;
    public $favoris_user;

    /** @var CCodeCIM10 */
    public $_code;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'cim10favoris';
        $spec->key   = 'favoris_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                 = parent::getProps();
        $props["favoris_user"] = "ref notNull class|CUser back|favoris_CIM10";
        $props["favoris_code"] = "str notNull maxLength|16 seekable";

        return $props;
    }

    /**
     * Load the code CIM10's object
     *
     * @return CCodeCIM10
     */
    public function getCode()
    {
        return $this->_code = CCodeCIM10::get($this->favoris_code);
    }

    /**
     * @inheritdoc
     */
    public function store()
    {
        if (!$this->_id) {
            self::resetListFavoris($this->favoris_user);
        }

        return parent::store();
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        self::resetListFavoris($this->favoris_user);

        return parent::delete();
    }

    /**
     * Returns the tag items tree with all the favoris
     *
     * @param int $user_id User id
     *
     * @return array
     */
    static function getTree($user_id)
    {
        return CFavoriCCAM::getTreeGeneric($user_id, "CFavoriCIM10");
    }

    /**
     * Find the codes with the given keyword from the favoris CIM of the given user(s)
     *
     * @param CMediusers[]|CMediusers|int $users       The users
     * @param string                      $code        The code query
     * @param string                      $keywords    The keywords for the search
     * @param string                      $chapter     Recherche par chapitre
     * @param string                      $category    Recherche par categorie
     * @param int                         $tag_id      A tag id
     * @param string                      $sejour_type Le type de séjour (mco, ssr ou psy) pour déterminer si le code
     *                                                 est autorisé
     * @param string                      $field_type  Le type de champ (dp, dr, da, fppec, mmp, ae, das) pour
     *                                                 déterminer si le code est autorisé
     *
     * @return CCodeCIM10[]
     */
    public static function findCodes(
        $users,
        $code = null,
        $keywords = null,
        $chapter = null,
        $category = null,
        $tag_id = null,
        $sejour_type = null,
        $field_type = null
    ) {
        if (is_array($users)) {
            $favoris = [];
            foreach ($users as $user) {
                $favoris = array_merge($favoris, self::getListFavoris($user, $tag_id));
            }
        } else {
            $favoris = self::getListFavoris($users, $tag_id);
        }

        $where = CCodeCIM10::getCodeField() . " " . CSQLDataSource::prepareIn($favoris);

        $codes = CCodeCIM10::findCodes(
            $code,
            $keywords,
            $chapter,
            $category,
            null,
            $where,
            null,
            $sejour_type,
            $field_type
        );

        /* If we load the favoris for one user, we get the CFavoriCIM10's id */
        if ((is_object($users) && $users instanceof CMediusers) || (is_array($users) && count($users) == 1)) {
            $user = $users;
            if (is_array($users)) {
                $user = reset($users);
            }

            foreach ($codes as $code) {
                $code->isFavori($user);
            }
        }

        return $codes;
    }

    /**
     * Return the list of favorites codes for the given user(s)
     *
     * @param CMediusers|int $users  The users
     * @param int            $tag_id A tag id
     *
     * @return array
     */
    public static function getListFavoris($users, $tag_id = null)
    {
        $favori = new self();

        if (is_object($users) && $users instanceof CMediusers) {
            $users_id = $users->_id;
        } else {
            $users_id = $users;
        }

        $codes = [];
        $where = [
            'favoris_user' => CSQLDataSource::prepareIn([$users_id]),
        ];
        $ljoin = [];

        if ($tag_id) {
            $where['tag_item.tag_id']       = " = '$tag_id'";
            $where['tag_item.object_class'] = " = 'CFavoriCIM10'";
            $ljoin['tag_item']              = "tag_item.object_id = favoris_id";
        }

        $favoris = $favori->loadList($where, null, 100, 'favoris_code', $ljoin);
        $codes   = CMbArray::pluck($favoris, 'favoris_code');

        return $codes;
    }

    /**
     * Reset the favoris list
     *
     * @param CMediusers|int $users  The users
     * @param int            $tag_id A tag id
     *
     * @return void
     */
    public static function resetListFavoris($users, $tag_id = null)
    {
        if (is_object($users) && $users instanceof CMediusers) {
            $users_id = $users->_id;
        } else {
            $users_id = $users;
        }

        // Todo: A supprimer ? (Non utilisé)
        $cache = new Cache(self::GET_LIST_FAVORIS_CACHE, [$users_id, $tag_id], Cache::INNER_OUTER);
        if ($cache->exists()) {
            $cache->rem();
        }
    }

    /**
     * Get the favoris with the given code for the given user
     *
     * @param string     $code The code
     * @param CMediusers $user The user
     *
     * @return CFavoriCIM10
     */
    public static function getFromCode($code, $user)
    {
        $favori               = new self();
        $favori->favoris_code = $code;
        $favori->favoris_user = $user->_id;
        $favori->loadMatchingObject();

        if ($favori->_id) {
            $favori->loadRefsTagItems();
        }

        return $favori;
    }

    public function equals(self $other): bool
    {
        return $this->_id === $other->_id
            && $this->favoris_code === $other->favoris_code
            && $this->favoris_user === $other->favoris_user;
    }
}
