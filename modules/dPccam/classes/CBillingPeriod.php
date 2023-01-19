<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Link the association rule used by the practitioner to the CCodable
 */
class CBillingPeriod extends CMbObject
{
    /**
     * @var integer Primary key
     */
    public $billing_period_id;

    public $codable_class;
    public $codable_id;
    public $period_start;
    public $period_end;
    public $period_statement;

    /**
     * @var CCodable
     */
    public $_ref_codable;

    /**
     * @see parent::getSpec()
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = 'billing_period';
        $spec->key   = 'billing_period_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['codable_class']    = 'str notNull class';
        $props['codable_id']       = 'ref notNull class|CCodable meta|codable_class back|billing_periods';
        $props['period_start']     = 'date';
        $props['period_end']       = 'date';
        $props['period_statement'] = 'enum notNull list|0|1|2';

        return $props;
    }

    /**
     * Initialisation de l'objet relié
     *
     * @param CStoredObject $object Objet relié
     *
     * @return void
     */
    public function setObject(CStoredObject $object): void
    {
        $this->codable_id    = $object->_id;
        $this->codable_class = $object->_class;
        $this->_ref_codable  = $object;
    }

    /**
     * Load the codable object
     *
     * @param bool $cache Use object cache
     *
     * @return CCodable|CStoredObject
     */
    public function loadCodable(bool $cache = true): ?CCodable
    {
        if (!$this->codable_class || !$this->codable_id) {
            return null;
        }

        return $this->_ref_codable = $this->loadFwdRef('codable_id', $cache);
    }

    /**
     * Check if modification allowed
     *
     * @param CSejour   $sejour Sejour concerned
     * @param CMbObject $context
     *
     * @return mixed
     */
    public static function checkStore(CSejour $sejour, CMbObject $context = null)
    {
        if (!$context) {
            $context = $sejour;
        }

        $sejour->completeField("entree_prevue", "entree_reelle", "sortie_prevue", "sortie_reelle", "uf_medicale_id");

        /** @var CSejour $old */
        $old = $sejour->loadOldObject();

        /** @var CBillingPeriod[] $segments_dossier */
        $segments_dossier = $sejour->loadRefsBillingPeriods();

        $all_billed = true;

        foreach ($segments_dossier as $_segment_dossier) {
            if ($_segment_dossier->period_statement < 1) {
                $all_billed = false;
                break;
            }
        }

        if (
            !$sejour->sortie_reelle
            || !$old->sortie_reelle
            || ($sejour->sortie_reelle >= CMbDT::dateTime())
            || ($old->sortie_reelle >= CMbDT::dateTime())
            || ((!count($segments_dossier) || !$all_billed))
        ) {
            return null;
        }

        $msg = CAppUI::tr("CSejour-Cannot store because segments are all billed");

        switch ($context->_class) {
            default:
            case "CSejour":
                if (
                    $sejour->fieldModified("uf_medicale_id")
                    || $sejour->fieldModified("entree_prevue")
                    || $sejour->fieldModified("entree_reelle")
                    || $sejour->fieldModified("sortie_prevue")
                    || $sejour->fieldModified("sortie_reelle")
                ) {
                    return $msg;
                }

                // Test also with form fields (DHE case)
                $entree_prevue = $sejour->_date_entree_prevue;
                $sortie_prevue = $sejour->_date_sortie_prevue;

                if (
                    ($entree_prevue && CMbDT::date($sejour->entree_prevue) !== $entree_prevue)
                    || ($sortie_prevue && CMbDT::date($sejour->sortie_prevue) !== $sortie_prevue)
                ) {
                    return $msg;
                }

                break;

            case "CAffectation":
            case "CItemLiaison":
                return $msg;
        }

        return null;
    }
}
