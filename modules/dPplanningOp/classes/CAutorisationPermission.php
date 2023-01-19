<?php

/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbRange;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Autorisation de permission
 */
class CAutorisationPermission extends CMbObject
{
    /** @var integer Primary key */
    public $autorisation_permission_id;

    // DB Fields
    /** @var CSejour */
    public $sejour_id;
    /** @var CMediusers */
    public $praticien_id;
    /** @var DateTime */
    public $debut;
    /** @var int */
    public $duree;
    /** @var string */
    public $rques;
    /** @var string */
    public $motif;

    // Form fields
    /** @var DateTime */
    public $_fin;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "autorisation_permission";
        $spec->key   = "autorisation_permission_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                 = parent::getProps();
        $props["sejour_id"]    = "ref class|CSejour back|autorisations_permission";
        $props["praticien_id"] = "ref class|CMediusers notNull back|autorisations_permission";
        $props["debut"]        = "dateTime notNull";
        $props["duree"]        = "num notNull min|1";
        $props["rques"]        = "text helped";
        $props["motif"]        = "text helped";

        $props["_fin"] = "dateTime moreThan|debut";

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->_fin = CMbDT::dateTime("+$this->duree hours", $this->debut);

        $this->_view = CAppUI::tr(
            "CAutorisationPermission-Autorisation from %s to %s",
            CMbDT::transform($this->debut, null, CAppUI::conf("datetime")),
            CMbDT::transform($this->_fin, null, CAppUI::conf("datetime"))
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getCollisions(): array
    {
        $collisions = [];
        $sejour     = new CSejour();
        if (!$sejour->load($this->sejour_id)) {
            return $collisions;
        }
        $sejour->loadRefsAutorisationsPermission();
        if (!count($sejour->_ref_autorisations_permission)) {
            return $collisions;
        }
        foreach ($sejour->_ref_autorisations_permission as $_auto_perm) {
            if ($this->_id === $_auto_perm->_id) {
                break;
            }
            if (CMbRange::collides($this->debut, $this->_fin, $_auto_perm->debut, $_auto_perm->_fin, false)) {
                $collisions[$_auto_perm->_id] = $_auto_perm;
            }
        }

        return $collisions;
    }

    /**
     * Surchage du store pour vérifier les collisions entre les autorisations de permissions
     *
     * @return string|null
     * @throws \Exception
     */
    public function store()
    {
        if (!count($this->getCollisions())) {
            return parent::store();
        } else {
            foreach ($this->getCollisions() as $_collision) {
                CAppUI::stepAjax(
                    CAppUI::tr(
                        'CAutorisationPermission-_fin-error-collisions',
                        [
                            "var1" => $_collision,
                        ]
                    ),
                    UI_MSG_ERROR
                );
            }
        }
    }
}
