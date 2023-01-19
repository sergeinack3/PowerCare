<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences\Services;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CService;

class PlacementPatientsService implements IShortNameAutoloadable
{
    /** @var CSQLDataSource */
    private $ds;

    /** @var string */
    private $group;

    public function __construct()
    {
        $this->ds    = CSQLDataSource::get("std");
        $this->group = CGroups::get();
    }

    /**
     * Get the emergency rooms placed
     *
     * @param bool $uhcd_service
     */
    public function getEmergencyRooms(bool $uhcd_service = false): array
    {
        $ljoin   = [
            "service" => "service.service_id = chambre.service_id",
        ];

        $where = [
            "annule"           => $this->ds->prepare("= '0'"),
            "service.group_id" => $this->ds->prepare("= ?", $this->group->_id),
        ];

        if (!$uhcd_service) {
            $where[] = "service.urgence " . $this->ds->prepare("= '1'") . " OR service.radiologie " . $this->ds->prepare("= '1'");
        } else {
            $where["service.uhcd"] = $this->ds->prepare("= '1'");
        }

        $chambre = new CChambre();

        return $chambre->loadList($where, null, null, "chambre_id", $ljoin);
    }

    /**
     * @param string $type_service
     * @param array  $topologie
     *
     * @return array
     * @throws CMbException
     */
    public function addBlockedBedRooms(string $type_service, array $topologie): array
    {
        if (!in_array($type_service, ['uhcd', 'urgence'])) {
            throw new CMbException('Type service is not accepted');
        }

        $services = array_filter(
            (new CService())->loadGroupList(),
            function (CService $service) use ($type_service): bool {
                return (bool)$service->{$type_service};
            }
        );

        $date = CMbDT::datetime();
        $affectations = CStoredObject::massLoadBackRefs($services, 'affectations', 'affectation.sortie DESC', [
            "affectation.entree" => "<= '$date'",
            "affectation.sortie" => ">= '$date'",
            "affectation.lit_id" => "IS NOT NULL"
        ]);
        $lits = CStoredObject::massLoadFwdRef($affectations, 'lit_id');
        $chambres = CStoredObject::massLoadFwdRef($lits, 'chambre_id');
        CStoredObject::massLoadBackRefs($chambres, 'emplacement');

        foreach ($services as $_service) {
            $affectations = $_service->loadRefsAffectations(CMbDT::datetime());
            foreach ($affectations as $_affectation) {
                if ($_affectation->sejour_id === null) {
                    $_affectation->loadRefLit()->loadRefChambre()->loadRefEmplacement();
                    $topologie[$type_service][$_affectation->_ref_lit->_ref_chambre->_id] = $_affectation;
                }
            }
        }

        return $topologie;
    }
}
