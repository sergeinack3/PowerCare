<?php

/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Exception;
use Ox\Core\CModelObject;

class DependancesRHSBilan extends CModelObject
{
    public const CATEGORIES = [
        "habillage"     => ["habillage_haut", "habillage_bas"],
        "locomotion"    => [
            "deplacement_transfert_lit_chaise",
            "deplacement_transfert_toilette",
            "deplacement_transfert_baignoire",
            "deplacement_locomotion",
            "deplacement_escalier",
        ],
        "alimentation"  => [
            "alimentation_utilisations_ustensile",
            "alimentation_mastication",
            "alimentation_deglutition",
        ],
        "continence"    => ["continence_controle_miction", "continence_controle_defecation"],
        "comportement"  => ["comportement"],
        "communication" => ["relation_comprehension_communication", "relation_expression_claire"],
    ];

    /** @var int */
    public $habillage;
    /** @var int */
    public $deplacement;
    /** @var int */
    public $alimentation;
    /** @var int */
    public $continence;
    /** @var int */
    public $comportement;
    /** @var int */
    public $relation;

    /**
     * @param int|null $habillage
     * @param int|null $deplacement
     * @param int|null $alimentation
     * @param int|null $continence
     * @param int|null $comportement
     * @param int|null $relation
     *
     * @throws Exception
     */
    public function __construct(
        ?int $habillage,
        ?int $deplacement,
        ?int $alimentation,
        ?int $continence,
        ?int $comportement,
        ?int $relation
    ) {
        parent::__construct();
        $this->habillage    = $habillage;
        $this->deplacement  = $deplacement;
        $this->alimentation = $alimentation;
        $this->continence   = $continence;
        $this->comportement = $comportement;
        $this->relation     = $relation;
    }

    public function getProps(): array
    {
        $props = parent::getProps();

        $degre                 = "enum list|1|2|3|4";
        $props["habillage"]    = $degre;
        $props["deplacement"]  = $degre;
        $props["alimentation"] = $degre;
        $props["continence"]   = $degre;
        $props["comportement"] = $degre;
        $props["relation"]     = $degre;

        return $props;
    }

    /**
     * @throws Exception
     */
    public static function createFromDependancesRHS(CDependancesRHS $dependances): DependancesRHSBilan
    {
        $max_values = [];
        foreach (self::CATEGORIES as $_cat => $items) {
            foreach ($items as $_item) {
                if (!array_key_exists($_cat, $max_values) || $dependances->$_item > $max_values[$_cat]) {
                    $max_values[$_cat] = $dependances->$_item;
                }
            }
        }

        return new self(
            $max_values['habillage'],
            $max_values['locomotion'],
            $max_values['alimentation'],
            $max_values['continence'],
            $max_values['comportement'],
            $max_values['communication']
        );
    }
}
