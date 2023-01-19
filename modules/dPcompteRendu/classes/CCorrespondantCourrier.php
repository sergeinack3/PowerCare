<?php

/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use Exception;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Patients\CMedecinExercicePlace;
use Ox\Mediboard\Patients\MedecinExercicePlaceService;

/**
 * Gestion de correpondants dans les documents
 */
class CCorrespondantCourrier extends CMbObject
{
    /** @var int */
    public $correspondant_courrier_id;

    /** @var int */
    public $compte_rendu_id;

    /** @var int */
    public $object_id;

    /** @var string */
    public $object_class;

    /** @var int */
    public $medecin_exercice_place_id;

    /** @var string */
    public $tag;

    /** @var int */
    public $quantite;

    /** @var CMbObject */
    public $_ref_object;

    /** @var CMedecinExercicePlace */
    public $_ref_medecin_exercice_place;

    /**
     * @inheritDoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'correspondant_courrier';
        $spec->key   = 'correspondant_courrier_id';

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getProps(): array
    {
        $props                    = parent::getProps();
        $props["compte_rendu_id"] = "ref class|CCompteRendu notNull cascade back|correspondants_courrier";
        $props["object_id"]       = "ref notNull class|CStoredObject meta|object_class back|correspondants_courrier";
        $props["object_class"]    = "enum list|CMedecin|CPatient|CCorrespondantPatient|CMediusers notNull";
        $props['medecin_exercice_place_id'] = 'ref class|CMedecinExercicePlace back|correspondants_courrier';
        $props["quantite"]        = "num pos notNull min|1 default|1";
        $props["tag"]             = "str";

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function store(): ?string
    {
        $this->completeField('object_id', 'object_class');

        if ($this->object_class === 'CMedecin') {
            (new MedecinExercicePlaceService($this, 'object_id', 'medecin_exercice_place_id'))
                ->applyFirstExercicePlace();
        }

        return parent::store();
    }

    /**
     * @param CStoredObject $object
     *
     * @return void
     * @todo redefine meta raf
     * @deprecated
     */
    public function setObject(CStoredObject $object): void
    {
        CMbMetaObjectPolyfill::setObject($this, $object);
    }

    /**
     * @param bool $cache
     *
     * @return mixed
     * @throws Exception
     * @deprecated
     * @todo redefine meta raf
     */
    public function loadTargetObject(bool $cache = true): CMbObject
    {
        return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
    }

    /**
     * @inheritDoc
     * @todo remove
     */
    public function loadRefsFwd(): void
    {
        parent::loadRefsFwd();
        $this->loadTargetObject();
    }

    public function loadRefMedecinExercicePlace(): CMedecinExercicePlace
    {
        return $this->_ref_medecin_exercice_place = $this->loadFwdRef('medecin_exercice_place_id', true);
    }
}
