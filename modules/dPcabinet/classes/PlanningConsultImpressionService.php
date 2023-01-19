<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;

/**
 * Permet de construire un tableau des consultations, plage de conges, de consult... pour l'impression du planning
 */
class PlanningConsultImpressionService extends PlanningConsultSlotService
{
    /** @var array */
    private $contents = [
        "plage_consult" => [],
        "consults"      => [],
    ];
    /** @var string */
    private $date_debut;
    /** @var string */
    private $date_fin;
    /** @var int */
    private $function_id;
    /** @var int */
    private $plage_consult_id;
    /** @var CPlageconsult[] */
    private $plages_consult;
    /** @var string */
    private $libelle;
    /** @var int */
    private $chir;


    public function __construct(
        string $date_debut,
        string $date_fin,
        int    $function_id = null,
        int    $plage_consult_id = null,
        array  $plages_consult = null,
        string $libelle = null,
        int    $chir = null
    ) {
        $this->date_debut       = $date_debut;
        $this->date_fin         = $date_fin;
        $this->function_id      = $function_id;
        $this->plage_consult_id = $plage_consult_id;
        $this->plages_consult   = $plages_consult;
        $this->libelle          = $libelle;
        $this->chir             = $chir;

        $this->contents = [];
        $this->plageConsultContents();
    }

    private function plageConsultContents(): void
    {
        $where = [];
        if ($this->plage_consult_id) {
            $where["plageconsult_id"] = "= '$this->plage_consult_id'";
        } elseif ($this->plages_consult) {
            $where["plageconsult_id"] = CSQLDataSource::prepareIn($this->plages_consult);
        } else {
            $list_prat        = CConsultation::loadPraticiens(PERM_EDIT, $this->function_id);
            $where["date"]    = "BETWEEN '$this->date_debut' AND '$this->date_fin'";
            $where["chir_id"] = CSQLDataSource::prepareIn(array_keys($list_prat), $this->chir);
        }

        if ($this->libelle) {
            $where['libelle'] = " LIKE '%" . utf8_decode($this->libelle) . "%'";
        }

        $order   = [];
        $order[] = "date";
        $order[] = "chir_id";
        $order[] = "debut";
        $plage   = new CPlageconsult();
        /** @var CPlageconsult[] $listPlage */
        $plages        = $plage->loadList($where, $order);
        $consultations = CStoredObject::massLoadBackRefs($plages, "consultations");
        CStoredObject::massLoadFwdRef($consultations, "patient_id");
        CStoredObject::massLoadFwdRef($consultations, "sejour_id");
        CStoredObject::massLoadFwdRef($consultations, "categorie_id");
        CStoredObject::massLoadBackRefs($consultations, "consult_anesth");
        foreach ($plages as $_plage) {
            $this->contents["consults"][$_plage->_id] = [];
            $_plage->loadRefChir();
            $_plage->loadRefsConsultations(false);
            $this->slotContents($_plage);
            $this->contents["plage_consult"][] = $_plage;
        }
    }

    protected function consultContents(CConsultation $consultation, int $plage_id): void
    {
        $consultation->loadRefPatient(1)->loadIPP();
        $consultation->loadRefSejour()->loadRefCurrAffectation(CMbDT::date($consultation->_datetime))->loadView();
        $consultation->loadRefCategorie();
        $consultation->loadRefConsultAnesth();
        $consultation->loadRefPlageConsult();
        $consult_anesth = $consultation->_ref_consult_anesth;
        if ($consult_anesth->operation_id) {
            $consult_anesth->loadRefOperation();
            $consult_anesth->_ref_operation->loadRefPraticien(true);
            $consult_anesth->_ref_operation->loadRefPlageOp(true);
            $consult_anesth->_ref_operation->loadExtCodesCCAM();
            $consult_anesth->_date_op =& $consult_anesth->_ref_operation->_ref_plageop->date;
        }
        $this->contents["consults"][$plage_id][] = $consultation;
    }

    public function getContents(): array
    {
        return $this->contents;
    }

    protected function consultContentsByDate(string $date, CConsultation $consult): void
    {
    }
}
