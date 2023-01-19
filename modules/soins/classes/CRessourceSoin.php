<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;

/**
 * Class CRessourceSoin
 * @package Ox\Mediboard\Soins
 */
class CRessourceSoin extends CMbObject
{
    /** @var int */
    public $ressource_soin_id;

    // DB Fields

    /** @var string */
    public $cout;
    /** @var string */
    public $libelle;
    /** @var float */
    public $code;


    /**
     * @inheritDoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'ressource_soin';
        $spec->key   = 'ressource_soin_id';

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getProps(): array
    {
        $props            = parent::getProps();
        $props["code"]    = "str notNull";
        $props["libelle"] = "str notNull";
        $props["cout"]    = "currency";

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->_view = $this->libelle;
    }
}
