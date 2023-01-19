<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

/**
 * Les banques permettent aux règlements d'être regroupés pour produire des borderaux
 */
class CBanque extends CMbObject
{
    public $banque_id;

    // DB fields
    public $nom;
    public $description;
    public $departement;
    public $boite_postale;
    public $adresse;
    public $cp;
    public $ville;
    public $group_id;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'banque';
        $spec->key   = 'banque_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                  = parent::getProps();
        $props["nom"]           = "str notNull seekable";
        $props["description"]   = "str seekable";
        $props["departement"]   = "str";
        $props["boite_postale"] = "str";
        $props["adresse"]       = "text confidential";
        $props["ville"]         = "str confidential seekable|begin";
        $props["group_id"]      = "ref class|CGroups back|banques";
        [$min_cp, $max_cp] = CPatient::getLimitCharCP();
        $props["cp"] = "str minLength|$min_cp maxLength|$max_cp confidential";

        return $props;
    }

    public function store()
    {
        // Group_id only if new bank
        if (!$this->_id) {
            $this->group_id = CGroups::loadCurrent()->_id;
        }

        return parent::store();
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->_view = $this->nom;
    }

    /**
     * Charge l'ensemble des banques existantes
     *
     * @return CBanque[]
     */
    static function loadAllBanques()
    {
        $banque = new self;

        return $banque->loadList([
                                     'group_id' => ' = ' . CGroups::loadCurrent()->_id . ' OR group_id IS NULL',
                                 ], [
                                     "group_id DESC",
                                     "nom ASC",
                                 ]);
    }
}
