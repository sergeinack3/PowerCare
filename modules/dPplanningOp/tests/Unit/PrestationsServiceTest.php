<?php

/**
 * @package Mediboard\Patient\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp\Tests\Unit;

use Exception;
use Ox\Core\CMbDT;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CItemLiaison;
use Ox\Mediboard\Hospi\CItemPrestation;
use Ox\Mediboard\Hospi\CPrestationJournaliere;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanningOp\Services\PrestationsService;
use Ox\Mediboard\Populate\Generators\CGroupsGenerator;
use Ox\Mediboard\Populate\Generators\CMediusersGenerator;
use Ox\Mediboard\Populate\Generators\CPatientGenerator;
use Ox\Tests\OxUnitTestCase;

class PrestationsServiceTest extends OxUnitTestCase
{
    /** @var PrestationsService */
    protected static $prestation_service;
    /** @var CGroups */
    protected static $group;

    static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$prestation_service = new PrestationsService();
        static::$group = (new CGroupsGenerator())->generate();
    }

    /**
     * @param COperation  $operation
     * @param string|null $expected
     *
     */
    public function testGetLibellesActesPrevus(): void
    {
        $item_destination = new CItemLiaison();
        $item_destination->loadRefItem();
        $item_destination->loadRefItemRealise();
        $item_destination->loadRefSousItem();

        $item_source      = $this->createItemLiaison();

        static::$prestation_service->copyLiaison($item_destination, $item_source);

        $this->assertEquals($item_destination->_ref_item->nom, $item_source->_ref_item->nom);
        $this->assertEquals($item_destination->_ref_item_realise->nom, $item_source->_ref_item_realise->nom);
        $this->assertEquals($item_destination->_ref_sous_item->nom, $item_source->_ref_sous_item->nom);
    }

    /**
     * Create item liaison object
     *
     * @throws Exception
     */
    public function createItemLiaison(): CItemLiaison
    {
        $prestation_journaliere           = new CPrestationJournaliere();
        $prestation_journaliere->nom      = "prestation test";
        $prestation_journaliere->group_id = static::$group->_id;
        $prestation_journaliere->M        = 1;
        $prestation_journaliere->C        = 1;
        $prestation_journaliere->O        = 1;
        $prestation_journaliere->SSR      = 1;
        $prestation_journaliere->store();

        if ($msg = $prestation_journaliere->store()) {
            self::fail($msg);
        }

        $item_prestation                  = new CItemPrestation();
        $item_prestation->nom             = "item prestation test";
        $item_prestation->actif           = 1;
        $item_prestation->rank            = 1;
        $item_prestation->color           = "3d85c6";
        $item_prestation->facturable      = 1;
        $item_prestation->chambre_double  = 0;
        $item_prestation->object_class    = $prestation_journaliere->_class;
        $item_prestation->object_id       = $prestation_journaliere->_id;

        if ($msg = $item_prestation->store()) {
            self::fail($msg);
        }

        $sejour = static::createSejour();

        $item_liaison                  = new CItemLiaison();
        $item_liaison->sejour_id       = $sejour->_id;
        $item_liaison->item_souhait_id = $item_prestation->_id;
        $item_liaison->item_realise_id = $item_prestation->_id;
        $item_liaison->prestation_id   = $prestation_journaliere->_id;
        $item_liaison->date            = CMbDT::date();
        $item_liaison->quantite        = 0;

        if ($msg = $item_liaison->store()) {
            self::fail($msg);
        }

        $item_liaison->loadRefItem();
        $item_liaison->loadRefItemRealise();
        $item_liaison->loadRefSousItem();

        return $item_liaison;
    }

    /**
     * Create Sejour object
     *
     * @throws Exception
     */
    protected static function createSejour(): CSejour
    {
        $sejour                = new CSejour();
        $sejour->patient_id    = (new CPatientGenerator())->setForce(true)->generate()->_id;
        $sejour->praticien_id  = (new CMediusersGenerator())->generate()->_id;
        $sejour->group_id      = static::$group->_id;
        $sejour->type          = "comp";
        $sejour->entree_prevue = CMbDT::dateTime();
        $sejour->sortie_prevue = CMbDT::dateTime("+ 3 DAYS");

        if ($msg = $sejour->store()) {
            self::fail($msg);
        }

        return $sejour;
    }
}
