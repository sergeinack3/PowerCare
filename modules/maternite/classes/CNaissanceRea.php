<?php

/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;

class CNaissanceRea extends CMbObject
{
    // DB Table key
    /** @var int */
    public $naissance_rea_id;

    // DB Fields
    /** @var string */
    public $rea_par;
    /** @var int */
    public $rea_par_id;
    /** @var int */
    public $naissance_id;

    /** @var CNaissance|null */
    public $_ref_naissance;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'naissance_rea';
        $spec->key   = 'naissance_rea_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                 = parent::getProps();
        $props['naissance_id'] = 'ref class|CNaissance back|naissances_rea notNull';
        $props['rea_par_id']   = 'ref class|CMediusers back|naissances_rea notNull';
        $props['rea_par']      = 'enum list|sf|ped|anesth|samu|obst|ide|aux';

        return $props;
    }

    /**
     * @return CNaissance|null
     * @throws Exception
     */
    public function loadRefNaissance(): ?CStoredObject
    {
        return $this->_ref_naissance = $this->loadFwdRef('naissance_id');
    }

    /**
     * @return string|null
     * @throws Exception
     * @see parent::store()
     */
    public function store(): ?string
    {
        $naissance     = $this->loadRefNaissance();
        $resuscitators = $naissance->loadRefsResuscitators();
        if (!$this->_id && in_array($this->rea_par_id, CMbArray::pluck($resuscitators, 'rea_par_id'))) {
            return CAppUI::tr('CNaissanceRea-already_exists');
        }

        return parent::store();
    }
}
