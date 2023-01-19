<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Description
 */
class CPlageConsultCategorie extends CMbObject {
    /** @var string  */
    public const RESOURCE_TYPE = 'plageConsultCategory';

    /** @var string */
    public const FIELDSET_TARGET = 'target';

    /** @var string */
    public const FIELDSET_APPFINE = 'appfine';

    /** @var int Primary key */
    public $plage_consult_categorie_id;

    // DB References
    public $plage_id;
    public $consult_categorie_id;
    public $praticien_id;

    // DB fields
    public $sync_appfine;

    /** @var CPlageconsult */
    public $_ref_plage;
    /** @var CConsultationCategorie */
    public $_ref_consult_categorie;
    /** @var CMediusers */
    public $_ref_praticien;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = 'plage_consult_categorie';
        $spec->key   = 'plage_consult_categorie_id';
        $spec->uniques["plage_consult_categorie"] = array("plage_id", "consult_categorie_id", "praticien_id");

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props["praticien_id"]         = "ref class|CMediusers cascade back|prat_plage_cat_liaisons fieldset|target";
        $props["plage_id"]             = "ref class|CPlageconsult cascade back|plage_consult_cat_liaisons fieldset|target";
        $props["consult_categorie_id"] = "ref class|CConsultationCategorie cascade back|categorie_plage_consult_liaisons fieldset|target";
        $props["sync_appfine"]         = "bool default|0 fieldset|appfine";

        return $props;
    }

    /**
     * Chargement du praticien
     *
     * @return CMediusers
     * @throws \Exception
     */
    function loadRefPraticien() {
        return $this->_ref_praticien = $this->loadFwdRef("praticien_id", true);
    }

    /**
     * Chargement de la plage
     *
     * @return CPlageconsult
     * @throws \Exception
     */
    function loadRefPlage() {
        return $this->_ref_plage = $this->loadFwdRef("plage_id", true);
    }

    /**
     * Chargement de la catégorie de consultation
     *
     * @return CConsultationCategorie
     * @throws \Exception
     */
    function loadRefConsultCategorie() {
        return $this->_ref_consult_categorie = $this->loadFwdRef("consult_categorie_id", true);
    }
}
