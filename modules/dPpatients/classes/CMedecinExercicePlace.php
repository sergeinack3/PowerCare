<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Exception;
use Ox\AppFine\Server\Appointment\CHonoraryPlace;
use Ox\AppFine\Server\Appointment\CInformationTarifPlace;
use Ox\AppFine\Server\Appointment\CSchedulePlace;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Description
 */
class CMedecinExercicePlace extends CMbObject
{
    public const TAG_ADDED_MANUALLY = 'added_manually';

    /** @var string */
    public const RESOURCE_TYPE = 'medecinExercicePlace';

    /** @var string */
    public const RELATION_MEDECIN = 'medecin';

    /** @var string */
    public const RELATION_EXERCICE_PLACE = 'exercicePlace';

    /** @var string */
    public const RELATION_HONORARY_PLACE = 'honoraryPlace';

    /** @var string */
    public const RELATION_SCHEDULE_PLACE = 'schedulePlace';

    /** @var string */
    public const RELATION_INFORMATION_TARIF_PLACE = 'informationTarifPlace';

    /** @var string */
    public const RELATION_PRESENTATION = 'presentation';

    /** @var string */
    public const FIELDSET_TARGET = 'target';

    /** @var string */
    public const FIELDSET_IDENTIFIERS = 'identifiers';

    /** @var string string */
    public const FIELDSET_SPECIALITY = 'speciality';

    /** @var int Primary key */
    public $medecin_exercice_place_id;

    /** @var int */
    public $medecin_id;

    /** @var int */
    public $exercice_place_id;

    /** @var string */
    public $adeli;

    /** @var string */
    public $rpps_file_version;

    /** @var string */
    public $type;

    /** @var string */
    public $disciplines;

    /** @var string */
    public $mode_exercice;

    /** @var string */
    public $categorie_pro;

    /** @var string */
    public $mssante_address;

    /** @var bool */
    public $annule;

    /** @var CMedecin */
    public $_ref_medecin;

    /** @var CExercicePlace */
    public $_ref_exercice_place;

    /** @var CMedecin[] */
    public $_ref_medecins;

    /** @var CExercicePlace[] */
    public $_ref_exercice_places;

    /** @var CHonoraryPlace[] */
    public $_ref_honorary_places;

    /** @var CInformationTarifPlace */
    public $_ref_information_tarif_place;

    /** @var bool */
    public $_added_manually = false;

    /** @var array */
    public $_mssante_addresses = [];

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec                           = parent::getSpec();
        $spec->table                    = "medecin_exercice_place";
        $spec->key                      = "medecin_exercice_place_id";
        $spec->uniques['medecin_place'] = ['medecin_id', 'exercice_place_id'];
        $spec->loggable                 = CMbObjectSpec::LOGGABLE_HUMAN;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props['medecin_id']        = 'ref class|CMedecin notNull back|medecins_exercices_places fieldset|target';
        $props['exercice_place_id'] = 'ref class|CExercicePlace back|exercice_places fieldset|target';
        $props['adeli']             = "code confidential mask|9*S*S99999S9 adeli fieldset|identifiers";
        $props['rpps_file_version'] = 'str loggable|0 fieldset|identifiers';
        $props['type']              = 'enum list|' . implode(
            '|',
            CMedecin::$types
        ) . '|pharmacie|maison_medicale|autre default|medecin fieldset|speciality';
        $props['disciplines']       = 'text fieldset|speciality';
        $props['mode_exercice']     = 'enum list|liberal|salarie|benevole default|liberal fieldset|speciality';
        $props['categorie_pro']     = 'enum list|civil|militaire|etudiant default|civil fieldset|speciality';
        $props['mssante_address']   = 'str confidential';
        $props['annule']            = 'bool default|0';

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->_mssante_addresses = explode("\n", $this->mssante_address ?? '');
    }

    /**
     * @return CMedecin|CStoredObject
     * @throws Exception
     */
    public function loadRefMedecin(): CMedecin
    {
        return $this->_ref_medecin = $this->loadFwdRef("medecin_id", true);
    }

    /**
     * @return CExercicePlace|CStoredObject
     * @throws Exception
     */
    public function loadRefExercicePlace(): CExercicePlace
    {
        return $this->_ref_exercice_place = $this->loadFwdRef("exercice_place_id", true);
    }

    /**
     * Loads Honorary Place references.
     *
     * @param string[] $where
     *
     * @return CHonoraryPlace[]|CStoredObject[]
     * @throws Exception
     */
    public function loadRefHonoraryPlaces(array $where = []): array
    {
        return $this->_ref_honorary_places = $this->loadBackRefs('honorary_places', [], [], [], [], [], '', $where);
    }

    /**
     * Loads Information Tarif Place reference.
     *
     * @return CInformationTarifPlace|CStoredObject
     * @throws Exception
     */
    public function loadRefInformationTarifPlace(): CInformationTarifPlace
    {
        return $this->_ref_information_tarif_place = $this->loadUniqueBackRef('information_tarif_place');
    }

    /**
     * Loads Exercice Place reference for API response.
     *
     * @return Item|null
     * @throws Exception
     */
    public function getResourceExercicePlace(): ?Item
    {
        $exercice_place = $this->loadRefExercicePlace();

        if (!$exercice_place || !$exercice_place->_id) {
            return null;
        }

        return new Item($exercice_place);
    }

    /**
     * Loads Honorary Place references for API response.
     *
     * @return Collection
     * @throws Exception
     */
    public function getResourceHonoraryPlace(): ?Collection
    {
        // Building SQL WHERE criteria
        $where           = [];
        $where['active'] = $this->getDS()->prepare(' = ?', 1);

        // Fetching CHonoraryPlace objects from datasource
        $honorary_places = $this->loadRefHonoraryPlaces($where);

        if (!$honorary_places) {
            return null;
        }

        return new Collection($honorary_places);
    }

    /**
     * @return Collection|null
     * @throws Exception
     */
    public function getResourceSchedulePlace(): ?Collection
    {
        $schedule_place               = new CSchedulePlace();
        $schedule_place->object_id    = $this->_id;
        $schedule_place->object_class = $this->_class;

        $schedule_places = $schedule_place->loadMatchingList();

        if (empty($schedule_places)) {
            return null;
        }

        return new Collection($schedule_places);
    }

    /**
     * Loads Honorary Place references for API response.
     *
     * @return Item|null
     * @throws Exception
     */
    public function getResourceInformationTarifPlace(): ?Item
    {
        $information_tarif_place = $this->loadRefInformationTarifPlace();

        if (!$information_tarif_place || !$information_tarif_place->_id) {
            return null;
        }

        return new Item($information_tarif_place);
    }

    /**
     * @param string $object_class
     * @param int    $object_id
     *
     * @return CMedecinExercicePlace|null
     * @throws Exception
     */
    public function loadMedecinExercicePlaces(string $object_class, int $object_id): ?array
    {
        switch ($object_class) {
            case 'CMedecin':
                $this->medecin_id = $object_id;

                break;
            case 'CExercicePlace':
                $this->exercice_place_id = $object_id;

                break;
            default:
                return null;
        }

        $medecin_exercice_places = $this->loadMatchingList();

        if (!$medecin_exercice_places) {
            return null;
        }

        /** @var CMedecinExercicePlace $_medecin_exercice_place */
        foreach ($medecin_exercice_places as $_medecin_exercice_place) {
            $_medecin_exercice_place->loadRefExercicePlace();
            $_medecin_exercice_place->loadRefMedecin();

            if ($object_class == 'CExercicePlace') {
                $idex = CIdSante400::getMatch(
                    $_medecin_exercice_place->_class,
                    CMedecinExercicePlace::TAG_ADDED_MANUALLY,
                    1,
                    $_medecin_exercice_place->_id
                );
                if ($idex && $idex->_id) {
                    $_medecin_exercice_place->_added_manually = true;
                }
            }
        }

        return $medecin_exercice_places;
    }

    /**
     * Found Exercice Place
     *
     * @param string $exercice_place_name
     * @param string $cp
     * @param string $city
     * @param string $rpps
     * @param string $strict
     * @param int    $step
     * @param int    $page
     * @return array
     * @throws Exception
     */
    public static function foundExercicePlaces(
        string $exercice_place_name,
        string $cp,
        string $city,
        string $rpps,
        ?string $strict,
        int $step,
        int $page
    ): array {
        // Parsing slashes
        $exercice_place_name = stripslashes($exercice_place_name);
        $city = stripslashes($city);

        $exercice_place = new CExercicePlace();
        $exercice_place_ds = $exercice_place->getDS();
        $ljoin = null;
        $where = [];

        if (!$exercice_place_name && !$cp && !$city && !$rpps) {
            return [
                'exercice_places' => [],
                'count'           => 0
            ];
        }

        if ($exercice_place_name) {
            $where['exercice_place.raison_sociale'] = $strict
                ? $exercice_place_ds->prepare('= ?', $exercice_place_name)
                : $exercice_place_ds->prepareLike("%$exercice_place_name%");
        }

        if ($cp) {
            $where['exercice_place.cp'] = $strict
                ? $exercice_place_ds->prepare('= ?', $cp)
                : $exercice_place_ds->prepareLike("%$cp%");
        }

        if ($city) {
            $where['exercice_place.commune'] = $strict
                ? $exercice_place_ds->prepare('= ?', $city)
                : $exercice_place_ds->prepareLike("%$city%");
        }

        if ($rpps) {
            $ljoin = [
                'medecin_exercice_place' =>
                    'medecin_exercice_place.exercice_place_id = exercice_place.exercice_place_id',
                'medecin'                => 'medecin.medecin_id = medecin_exercice_place.medecin_id',
            ];
            $where['medecin.rpps'] = $exercice_place_ds->prepare('= ?', $rpps);
        }

        $exercice_places = $exercice_place->loadList($where, null, "$page, $step", null, $ljoin);
        $count           = $exercice_place->countList($where, null, $ljoin);

        return [
            'exercice_places' => $exercice_places,
            'count'           => $count
        ];
    }

    /**
     * Add doctor to exercice place
     *
     * @param int $exercice_id
     * @param int $medecin_id
     * @return string|null
     */
    public static function addDoctorToExercicePlace(int $exercice_id, int $medecin_id): ?string
    {
        $medecin_exercice_place = new CMedecinExercicePlace();
        $medecin_exercice_place->medecin_id        = $medecin_id;
        $medecin_exercice_place->exercice_place_id = $exercice_id;
        if ($msg = $medecin_exercice_place->store()) {
            return $msg;
        }

        $idex = CIdSante400::getMatch(
            $medecin_exercice_place->_class,
            CMedecinExercicePlace::TAG_ADDED_MANUALLY,
            1,
            $medecin_exercice_place->_id
        );
        if (!$idex->_id) {
            if ($msg = $idex->store()) {
                return $msg;
            }
        }

        return null;
    }

    /**
     * @param int $medecin_exercice_place_id
     * @return array
     * @throws Exception
     */
    public static function removeDoctorToExercicePlace(int $medecin_exercice_place_id): array
    {
        $medecin_exercice_place = new CMedecinExercicePlace();
        $medecin_exercice_place->load($medecin_exercice_place_id);

        if (!$medecin_exercice_place || !$medecin_exercice_place->_id) {
            return [
                'msg' => 'AppFine-msg-Medecin exercice place not found',
                'error' => true
            ];
        }

        $idex = CIdSante400::getMatch(
            $medecin_exercice_place->_class,
            CMedecinExercicePlace::TAG_ADDED_MANUALLY,
            1,
            $medecin_exercice_place->_id
        );
        if (!$idex || !$idex->_id) {
            return [
                'msg' => 'AppFine-msg-Medecin exercice place idex not found',
                'error' => true
            ];
        }

        $idex->delete();

        if ($msg = $medecin_exercice_place->delete()) {
            return [
                'msg' => $msg,
                'error' => true
            ];
        }

        return [
            'msg' => 'AppFine-msg-Doctor deleted to exercice place',
            'error' => false
        ];
    }
}
