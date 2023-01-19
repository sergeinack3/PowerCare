<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Exception;
use Ox\Core\CModelObject;

/**
 * @description Code CCAM class for API Call
 */
class CApiCodeCCAM extends CModelObject
{
    public const RESOURCE_TYPE = 'ApiCodeCCAM';

    /** @var string CCAM Code */
    public $code;

    /** @var string CCAM short wording */
    public $libelle_court;

    /** @var string CCAM long wording */
    public $libelle_long;

    /** @var array CCAM act type (number + title) */
    public $type_acte;

    /** @var array CCAM insurance */
    public $assurances;

    /** @var string CCAM refundable */
    public $remboursement;

    /** @var string CCAM rate */
    public $forfait;

    /** @var string CCAM prior agreement */
    public $entente_prealable;

    /** @var string CCAM note */
    public $remarque;

    /** @var array CCAM chapters */
    public $chapitres = [];

    /** @var array CCAM speciality */
    public $specialites = [];

    /** @var array CCAM activities */
    public $activites = [];

    /** @var array CCAM incompatibles code(s) */
    public $incompatibles = [];

    /** @var array CCAM associates code(s) */
    public $associes = [];

    /** @var array CCAM PSMI Extensions */
    public $extensions = [];

    /** @var CDatedCodeCCAM Reference of ApiCodeCCAM data, don't expose in API */
    private $dated_code;

    /**
     * Constructor for ApiCodeCCAM
     *
     * @param CDatedCodeCCAM $dated_code
     *
     * @throws Exception
     */
    public function __construct(CDatedCodeCCAM $dated_code)
    {
        parent::__construct();

        $this->dated_code = $dated_code;

        $this->code              = $dated_code->code;
        $this->libelle_court     = $dated_code->libelleCourt;
        $this->libelle_long      = $dated_code->libelleLong;
        $this->remboursement     = $dated_code->remboursement;
        $this->entente_prealable = $dated_code->entente_prealable;
        $this->remarque          = $dated_code->remarques;
        $this->chapitres         = $dated_code->chapitres;
        $this->specialites       = $dated_code->specialites;
        $this->incompatibles     = $dated_code->incomps;
        $this->associes          = $dated_code->assos;

        $this->getActType();
        $this->getActivities();
        $this->getInsurance();
        $this->getExtensions();
    }

    /**
     * @inheritDoc
     */
    public function getProps()
    {
        $props                      = parent::getProps();
        $props["code"]              = "str fieldset|default";
        $props["libelle_court"]     = "str fieldset|default";
        $props["libelle_long"]      = "str fieldset|default";
        $props["type_acte"]         = "str fieldset|default";
        $props["assurances"]        = "str fieldset|default";
        $props["remboursement"]     = "str fieldset|default";
        $props["forfait"]           = "str fieldset|default";
        $props["entente_prealable"] = "str fieldset|default";
        $props["remarque"]          = "str fieldset|default";
        $props["chapitres"]         = "str fieldset|default";
        $props["specialites"]       = "str fieldset|default";
        $props["incompatibles"]     = "str fieldset|default";
        $props["associes"]          = "str fieldset|default";
        $props["activites"]         = "str fieldset|default";
        $props["extensions"]        = "str fieldset|default";

        return $props;
    }

    /**
     * Get Act type of CCAM Code
     *
     * @return void
     */
    private function getActType(): void
    {
        $code_ccam = $this->dated_code->_ref_code_ccam;

        if ($code_ccam->code != "-") {
            $this->type_acte = [
                "type"   => $code_ccam->_type_acte,
                "numero" => $code_ccam->type_acte,
            ];
        }
    }

    /**
     * Get Activities of CCAM Code
     *
     * @return void
     */
    private function getActivities(): void
    {
        $dated_code = $this->dated_code;

        foreach ($dated_code->activites as $_activite) {
            $modifiers = [];
            foreach ($_activite->modificateurs as $_modificateur) {
                $modifiers[$_modificateur->code] = [
                    "code"     => $_modificateur->code,
                    "libelle"  => $_modificateur->libelle,
                    "forfait"  => $dated_code->getAllForfaits($_modificateur->code, $dated_code->date)
                ];
            }

            $phases = [];
            foreach ($_activite->phases as $_phase) {
                $dents_incomp = [];
                foreach ($_phase->dents_incomp as $_dent_incomp) {
                    $dents_incomp[] = [
                        "date_debut"   => $_dent_incomp->_ref_dent->date_debut,
                        "date_fin"     => $_dent_incomp->_ref_dent->date_fin,
                        "localisation" => $_dent_incomp->_ref_dent->localisation,
                        "_libelle"     => $_dent_incomp->_ref_dent->_libelle,
                    ];
                }

                $phases[] = [
                    "phase"        => $_phase->phase,
                    "libelle"      => $_phase->libelle,
                    "nb_dents"     => $_phase->nb_dents,
                    "dents_incomp" => $dents_incomp,
                    "charges"      => $_phase->charges,
                    "tarifs"       => $this->getPriceList($_phase)
                ];
            }

            $this->activites[] = [
                "numero"        => $_activite->numero,
                "type"          => $_activite->type,
                "libelle"       => $_activite->libelle,
                "classif"       => $_activite->classif,
                "modificateurs" => $modifiers,
                "phases"        => $phases,
            ];
        }
    }

    /**
     * Get Insurance of CCAM Code
     *
     * @return void
     */
    private function getInsurance(): void
    {
        $code_ccam = $this->dated_code->_ref_code_ccam;

        if ($code_ccam->code != "-") {
            foreach ($code_ccam->assurance as $_assurance) {
                if (is_array($_assurance) && isset($_assurance["db"]) && isset($_assurance["libelle"])) {
                    $this->assurances[] = [
                        "db"      => $_assurance["db"],
                        "libelle" => $_assurance["libelle"],
                    ];
                }
            }
        }
    }

    /**
     * Get PMSI Extensions of CCAM Code
     *
     * @return void
     */
    private function getExtensions(): void
    {
        foreach ($this->dated_code->extensions as $_extension) {
            $this->extensions[] = [
                "extension"   => $_extension->extension,
                "description" => $_extension->description,
            ];
        }
    }

    /**
     * Get the price list according to a phase
     *
     * @param object $phase
     *
     * @return array
     */
    private function getPriceList(object $phase): array
    {
        $price_list = [];
        for ($index = 1; $index <= 16; $index++) {
            $grid  = str_pad($index, 2, "0", STR_PAD_LEFT);
            $price = "tarif_g" . $grid;

            $price_list[] = [
                "grille" => $grid,
                "tarif"  => $phase->$price,
            ];
        }

        return $price_list;
    }
}
