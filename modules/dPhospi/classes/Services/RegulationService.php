<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi\Services;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Hospi\Repository\SejourRepository;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

class RegulationService implements IShortNameAutoloadable
{
    /** @var string|null */
    private $type_log;
    /** @var string|null */
    private $date_min;
    /** @var int|null */
    private $praticien_id;
    /** @var int|null */
    private $function_id;
    /** @var int|null */
    private $service_ids;
    /** @var array */
    private $types;

    /**
     * @param string|null $type_log
     * @param string|null $date_min
     * @param int|null    $praticien_id
     * @param int|null    $function_id
     * @param array       $services_id
     * @param array       $types
     */
    public function __construct(
        ?string $type_log = null,
        ?string $date_min = null,
        ?int $praticien_id = null,
        ?int $function_id = null,
        array $service_ids = [],
        array $types = []
    ) {
        $this->type_log     = $type_log;
        $this->date_min     = $date_min;
        $this->praticien_id = $praticien_id;
        $this->function_id  = $function_id;
        $this->service_ids  = $service_ids;
        $this->types        = $types;
    }

    /**
     * Get Sejours by users action (created or modified) for less than 24 hours
     *
     * @return array
     * @throws Exception
     */
    public function getSejoursByUserAction(): array
    {
        // Rercherche des séjours suivants le critère suivant :
        // - Création ou entrée modifiée depuis moins de 24h
        $ds       = CSQLDataSource::get("std");
        $date_max = CMbDT::dateTime("+24 hours", $this->date_min);

        $sejours_ids = [];

        $ljoin = [
            "user_action_data" => "user_action.user_action_id = user_action_data.user_action_id",
            "object_class"     => "user_action.object_class_id = object_class.object_class_id",
        ];
        $where = [
            "object_class.object_class" => $ds->prepare("= 'CSejour'"),
            "user_action.type"          => $ds->prepare("= 'create'"),
            "user_action.date"          => $ds->prepareBetween($this->date_min, $date_max),
        ];

        $sejour_repository = new SejourRepository($ds);

        if (!$this->type_log || $this->type_log == "create") {
            // Créations
            $sejours_ids = $sejour_repository->findIdsByUserAction($ljoin, $where);
        }
        if (!$this->type_log || $this->type_log == "store") {
            // Modification de l'entrée
            unset($where["user_action.type"]);
            $where["user_action.type"] = $ds->prepare("= 'store'");
            $where[]                   = "(user_action_data.field " . $ds->prepareLike(
                    "%entree_prevue%"
                ) . ") OR (user_action_data.field " . $ds->prepareLike("%entree_reelle%") . ")";

            $sejours_ids += $sejour_repository->findIdsByUserAction($ljoin, $where);
            $sejours_ids = array_unique($sejours_ids);
        }

        $sejour = new CSejour();
        $ljoin  = [];
        $where  = [
            "sejour.annule"    => "= '0'",
            "sejour.sejour_id" => CSQLDataSource::prepareIn($sejours_ids),
        ];
        if (count($this->types)) {
            $where["sejour.type"] = CSQLDataSource::prepareIn($this->types);
        }
        if ($this->praticien_id) {
            $where["sejour.praticien_id"] = $ds->prepare("= ?", $this->praticien_id);
        } elseif ($this->function_id) {
            $ljoin["users_mediboard"]             = "users_mediboard.user_id = sejour.praticien_id";
            $where["users_mediboard.function_id"] = $ds->prepare("= ?", $this->function_id);
        }
        if (count($this->service_ids)) {
            $ljoin["affectation"]            = "sejour.sejour_id = affectation.sejour_id";
            $where["affectation.service_id"] = CSQLDataSource::prepareIn($this->service_ids);
            $where["affectation.entree"]     = $ds->prepare('<= ?', $date_max);
            $where["affectation.sortie"]     = $ds->prepare('>= ?', $this->date_min);
        }
        /** @var CSejour[] $sejours */
        $sejours = $sejour->loadGroupList($where, null, null, null, $ljoin);

        /** @var CPatient[] $patients */
        $patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
        CPatient::massLoadIPP($patients);
        CPatient::massCountPhotoIdentite($patients);
        CSejour::massLoadSurrAffectation($sejours);
        CSejour::massLoadFwdRef($sejours, "praticien_id");
        CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

        foreach ($sejours as $_sejour) {
            $_sejour->loadRefPatient()->loadRefPhotoIdentite();
            $_sejour->loadRefPraticien();
            $_sejour->checkDaysRelative($date_max);
            $_sejour->_ref_patient->updateBMRBHReStatus($_sejour);
        }

        CMbArray::pluckSort($sejours, SORT_ASC, "_ref_patient", "nom");

        return $sejours;
    }
}
