<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences\Services;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CPDODataSource;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Urgences\CRPU;

class RPUService implements IShortNameAutoloadable
{
    public const STEP = 15;

    /** @var CPDODataSource|CSQLDataSource  */
    private $ds;
    /** @var CRPU  */
    private $rpu;

    /** @var array */
    private $where = [];
    /** @var array */
    private $ljoin = [];
    /** @var string */
    private $group;
    /** @var string */
    private $order;

    public function __construct()
    {
        $this->rpu = new CRPU();
        $this->ds  = $this->rpu->getDS();
    }


    public function joinSejour(): void
    {
        $this->ljoin['sejour'] = 'rpu.sejour_id = sejour.sejour_id';
    }

    public function joinExtract(): void
    {
        $this->ljoin['rpu_passage']      = 'rpu.rpu_id = rpu_passage.rpu_id';
        $this->ljoin['extract_passages'] = 'rpu_passage.extract_passages_id = extract_passages.extract_passages_id';
        $this->group = 'rpu.rpu_id';
    }

    /**
     * @param string $patient_ipp
     */
    public function addPatientIPPFilter(string $patient_ipp): void
    {
        $patient = new CPatient();
        $patient->getByIPPNDA($patient_ipp, null);

        if ($patient->_id) {
            $this->where['sejour.patient_id'] = $this->ds->prepare('= ?', $patient->_id);
        }
    }

    /**
     * @param string $patient_id
     */
    public function addPatientFilter(string $patient_id): void
    {
        $this->where['sejour.patient_id'] = $this->ds->prepare('= ?', $patient_id);
    }

    /**
     * @param string $nda
     */
    public function addNDAFilter(string $nda): void
    {
        $object_id = '';
        $tag = CSejour::getTagNDA();
        if ($tag) {
            $idex = CIdSante400::getMatch('CSejour', $tag, $nda);
            if ($idex->_id) {
                $object_id = $idex->object_id;
            }
        }
        $this->where['rpu.sejour_id'] = $this->ds->prepare('=?', $object_id);
    }

    public function addSentFilter(): void
    {
        $this->where['rpu.rpu_id'] = $this->ds->prepare('NOT IN (SELECT rpu_id FROM rpu_passage)');
    }

    /**
     * @param string $date_min
     * @param string $date_max
     */
    public function addSejourDatesFilter(string $date_min, string $date_max): void
    {
        $this->where = [
            'sejour.entree' => $this->ds->prepare('>= ?1 AND sejour.sortie <= ?2', $date_min . ' 00:00:00', $date_max . ' 23:59:59'),
        ];
    }

    /**
     * @param string $order_col
     * @param string $order_way
     */
    public function computePagination(string $order_col, string $order_way): void
    {
        switch ($order_col) {
            case '_first_extract_passages':
                $this->order = "extract_passages.date_extract $order_way";
                break;
            case '_count_extract_passages':
                $this->order = "COUNT(extract_passages.extract_passages_id) $order_way";
                break;
            default:
                $this->order = "sejour.entree $order_way";
        }
    }


    /**
     * @param int $page
     *
     * @return CRPU[]|null
     * @throws Exception
     */
    public function loadRPUList(int $page): ?array
    {
        return $this->rpu->loadList($this->where, $this->order, "$page, " . self::STEP, $this->group, $this->ljoin, null, null, false);
    }

    /**
     * @return int|null
     * @throws Exception
     */
    public function getTotal(): ?int
    {
        $ljoin = ['sejour' => 'rpu.sejour_id = sejour.sejour_id'];
        return $this->rpu->countList($this->where, null, $ljoin);
    }

    /**
     * @return string
     */
    public function getOrder(): string
    {
        return $this->order;
    }
}
