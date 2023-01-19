<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Search;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Ccam\CCodeCCAM;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Medicament\CMedicamentClasseATC;

class CSearchTargetEntry extends CMbObject
{
    /**
     * @var integer Primary key
     */
    public $search_thesaurus_entry_target_id;

    public $search_thesaurus_entry_id;
    public $object_class;
    public $object_id;

    public $_ref_target;

    public function getSpec(): CMbObjectSpec
    {
        $spec                  = parent::getSpec();
        $spec->table           = "search_thesaurus_entry_target";
        $spec->key             = "search_thesaurus_entry_target_id";
        $spec->uniques["code"] = ["search_thesaurus_entry_id", "object_class", "object_id"];

        return $spec;
    }

    public function getProps(): array
    {
        $props                              = parent::getProps();
        $props["search_thesaurus_entry_id"] = "ref class|CSearchThesaurusEntry cascade back|target_entry";
        $props["object_class"]              = "str maxLength|50";
        $props["object_id"]                 = "str maxLength|50";

        return $props;
    }

    /**
     * Method to load the target object of thesaurus target
     *
     * @return CActeNGAP|CCodeCCAM|CCodeCIM10
     */
    public function loadRefTarget()
    {
        if ($this->object_class && $this->object_id) {
            switch ($this->object_class) {
                case "CCodeCIM10":
                    $this->_ref_target = CCodeCIM10::get($this->object_id);
                    break;
                case "CCodeCCAM":
                    $object = new CCodeCCAM($this->object_id);
                    $object->load();
                    $this->_ref_target = $object;
                    break;
                case "CActeNGAP":
                    $object       = new CActeNGAP();
                    $object->code = $this->object_id;
                    $object->loadMatchingObject();
                    $this->_ref_target = $object;
                    break;
                case "CMedicamentClasseATC":
                    $object = new CMedicamentClasseATC();
                    $niveau = $object->getNiveau($this->object_id);
                    $object->loadClasseATC($niveau, $this->object_id);
                    $this->_ref_target = $object;
                    break;
                default:
                    // nothing to do
                    break;
            }
        }

        return $this->_ref_target;
    }
}
