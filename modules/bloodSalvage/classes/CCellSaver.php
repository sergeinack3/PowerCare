<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\BloodSalvage;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;

/**
 * CCellSaver
 */
class CCellSaver extends CMbObject
{
    /** @var int */
    public $cell_saver_id;

    //DB Fields
    /** @var string */
    public $marque;
    /** @var string */
    public $modele;

    /**
     * @see parent::getSpec()
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'cell_saver';
        $spec->key   = 'cell_saver_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    public function getProps(): array
    {
        $props           = parent::getProps();
        $props["marque"] = "str notNull maxLength|50";
        $props["modele"] = "str notNull maxLength|50";

        return $props;
    }

    /**
     * @see parent::updateFormFields()
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();
        $this->_view = "$this->marque $this->modele";
    }
}
