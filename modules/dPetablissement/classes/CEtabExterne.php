<?php

/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Etablissement;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * External group class (Etablissement externe)
 */
class CEtabExterne extends CMbObject
{
    /** @var string */
    public $etab_id;

    // DB Fields
    /** @var string */
    public $nom;
    /** @var string */
    public $raison_sociale;
    /** @var string */
    public $adresse;
    /** @var string */
    public $cp;
    /** @var string */
    public $ville;
    /** @var string */
    public $tel;
    /** @var string */
    public $fax;
    /** @var string */
    public $finess;
    /** @var string */
    public $siret;
    /** @var string */
    public $ape;
    /** @var bool $priority If true, the establishement will be displayed first in the autocomplete */
    public $priority;
    /** @var string */
    public $provenance;
    /** @var string */
    public $destination;

    /**
     * @see parent::getSpec()
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'etab_externe';
        $spec->key   = 'etab_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    public function getProps(): array
    {
        $props                   = parent::getProps();
        $props["nom"]            = "str notNull confidential seekable";
        $props["raison_sociale"] = "str maxLength|50";
        $props["adresse"]        = "text confidential";
        $props["cp"]             = "str length|5";
        $props["ville"]          = "str maxLength|50 confidential";
        $props["tel"]            = "phone";
        $props["fax"]            = "phone";
        $props["finess"]         = "str notNull length|9 confidential mask|9xS9S99999S9";
        $props["siret"]          = "str length|14";
        $props["ape"]            = "str maxLength|6 confidential";
        $props['priority']       = 'bool default|0';
        $props["provenance"]     = "enum list|1|2|3|4|5|6|7|8|R";
        $props["destination"]    = "enum list|0|" . implode("|", CSejour::$destination_values);

        return $props;
    }

    /**
     * @see parent::updateFormFields()
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();
        $this->_view = $this->nom;
    }
}
