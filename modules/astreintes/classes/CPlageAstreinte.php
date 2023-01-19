<?php

/**
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Astreintes;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObjectSpec;
use Ox\Core\Module\CModule;
use Ox\Core\CPlageCalendaire;
use Ox\Core\CStoredObject;
use Ox\Core\FieldSpecs\CColorSpec;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

class CPlageAstreinte extends CPlageCalendaire
{
    private const TYPE_ASTREINTE_ADMIN   = "admin";
    private const TYPE_ASTREINTE_INFO    = "informatique";
    private const TYPE_ASTREINTE_MED     = "medical";
    private const TYPE_ASTREINTE_PARAMED = "personnelsoignant";
    private const TYPE_ASTREINTE_TECH    = "technique";

    public const TYPES_ASTREINTES = [
        self::TYPE_ASTREINTE_ADMIN,
        self::TYPE_ASTREINTE_INFO,
        self::TYPE_ASTREINTE_MED,
        self::TYPE_ASTREINTE_PARAMED,
        self::TYPE_ASTREINTE_TECH,
    ];

    private const CHOICE_ASTREINTE_PONC = "ponctuelle";
    private const CHOICE_ASTREINTE_REGU = "reguliere";

    public const CHOICES_ASTREINTES = [
        self::CHOICE_ASTREINTE_PONC,
        self::CHOICE_ASTREINTE_REGU,
    ];

    private const TYPE_REPETITION_SIMPLE    = "simple";
    private const TYPE_REPETITION_DOUBLE    = "double";
    private const TYPE_REPETITION_TRIPLE    = "triple";
    private const TYPE_REPETITION_QUADRUPLE = "quadruple";
    private const TYPE_REPETITION_SAME      = "sameweek";

    public const REPETITION_TYPES = [
        self::TYPE_REPETITION_SIMPLE,
        self::TYPE_REPETITION_DOUBLE,
        self::TYPE_REPETITION_TRIPLE,
        self::TYPE_REPETITION_QUADRUPLE,
        self::TYPE_REPETITION_SAME,
    ];

    public const ORDER_END = "end";

    private const COLOR_000 = "000000";
    private const COLOR_FFF = "ffffff";

    public const ASTREINTES_COLORS = [
        self::COLOR_000,
        self::COLOR_FFF,
    ];

    // DB Fields
    /** @var string */
    public $plage_id;
    /** @var string */
    public $libelle;
    /** @var string */
    public $user_id;
    /** @var string */
    public $group_id;
    /** @var string */
    public $type;
    /** @var string */
    public $choose_astreinte;
    /** @var string */
    public $color;
    /** @var string */
    public $phone_astreinte;
    /** @var string */
    public $categorie;

    // available types
    /** @var bool */
    public $locked;

    // Object References
    /** @var string */
    public $_num_astreinte;

    /** @var CMediusers $_ref_user */
    public $_ref_user;
    /** @var CGroups $_ref_group */
    public $_ref_group;
    /** @var CCategorieAstreinte */
    public $_ref_category;

    // Form fields
    /** @var array */
    public $_duree;   //00:00:00
    /** @var float */
    public $_hours;   // 29.5 hours
    /** @var mixed */
    public $_color;
    /** @var int|float */
    public $_font_color;
    /** @var string */
    public $_type_repeat;
    /** @var int */
    public $_repeat_week;
    /** @var int */
    public $_count_duplicated_plages = 0;

    /**
     * @return CMbObjectSpec
     */
    public function getSpec(): CMbObjectSpec
    {
        $specs                 = parent::getSpec();
        $specs->table          = "astreinte_plage";
        $specs->key            = "plage_id";
        $specs->collision_keys = ["type", "user_id"];

        return $specs;
    }

    /**
     * @return array
     */
    public function getProps(): array
    {
        $specs                     = parent::getProps();
        $specs["user_id"]          = "ref class|CMediusers notNull back|astreintes";
        $specs["type"]             = "enum list|" . implode("|", self::TYPES_ASTREINTES) . " notNull";
        $specs["color"]            = "color";
        $specs["choose_astreinte"] = "enum list|ponctuelle|reguliere default|ponctuelle notNull";
        $specs["libelle"]          = "str";
        $specs["group_id"]         = "ref class|CGroups notNull back|group_astreinte";
        $specs["phone_astreinte"]  = "str notNull";
        $specs["categorie"]        = "ref class|CCategorieAstreinte back|shifts";
        $specs["locked"]           = "bool default|0";

        // Form fields
        $specs["_type_repeat"] = "enum list|simple|double|triple|quadruple|sameweek";

        return $specs;
    }

    public function store(): ?string
    {
        // A person can be on call on different services
        $this->_skip_collision_check = true;

        return parent::store();
    }

    /**
     * @throws Exception
     */
    public function loadRefGroup(): CStoredObject
    {
        return $this->_ref_group = $this->loadFwdRef("group_id", true);
    }

    /**
     * loadView
     *
     * @return null
     * @throws Exception
     * @see parent::loadView()
     */
    public function loadView(): void
    {
        parent::loadView();
        $this->getDuree();
        $this->_ref_user = $this->loadRefUser();  //I need the Phone Astreinte
    }

    /**
     * get the duration
     *
     * @return array|null
     */
    public function getDuree(): array
    {
        return $this->_duree = CMbDT::duration($this->start, $this->end);
    }

    /**
     * Load ref user
     *
     * @return CMediusers
     * @throws Exception
     */
    public function loadRefUser(): CMediusers
    {
        /** @var CMediusers $mediuser */
        $mediuser = $this->loadFwdRef("user_id", true);
        $mediuser->loadRefFunction();

        $this->_num_astreinte = $mediuser->_user_astreinte;

        return $this->_ref_user = $mediuser;
    }

    /**
     * @param int $permType
     *
     * @return bool
     * @throws Exception
     */
    public function getPerm($permType): bool
    {
        if (!$this->_ref_user) {
            $this->loadRefUser();
        }

        if (CAppUI::$user->isAdmin()) {
            return true;
        }

        if (CModule::getCanDo('astreintes')->edit || $this->_ref_user->getPerm($permType)) {
            return true;
        }

        return false;
    }

    /**
     * get the number of hours between start & end
     *
     * @return float
     */
    public function getHours(): float
    {
        return $this->_hours = CMbDT::minutesRelative($this->start, $this->end) / 60;
    }

    /**
     * Load color for astreinte
     *
     * @return mixed
     * @throws Exception
     */
    public function loadRefColor(): string
    {
        $color             = CAppUI::gconf("astreintes General astreinte_" . $this->type . "_color");
        $this->_font_color = $this->getFontColor($color);

        if ($this->color) {
            return $this->_color = $this->color;
        }
        if ($this->categorie) {
            self::loadRefCategory();

            return $this->_color = $this->_ref_category->color;
        }

        return $this->_color = str_replace(
            "#",
            "",
            CAppUI::gconf("astreintes General astreinte_" . $this->type . "_color")
        );
    }

    /**
     * Return font color
     */
    public function getFontColor(string $color): string
    {
        return CColorSpec::get_text_color($color) > 130 ? self::COLOR_000 : self::COLOR_FFF;
    }

    /**
     * Loads an 'on call' category object
     * @throws Exception
     */
    public function loadRefCategory(): ?CStoredObject
    {
        return $this->_ref_category = $this->loadFwdRef("categorie");
    }

    /**
     * Load phone for astreinte
     *
     * @return CStoredObject
     * @throws Exception
     */
    public function loadRefPhoneAstreinte(): CStoredObject
    {
        return $this->_num_astreinte = $this->loadFwdRef("_user_astreinte", true);
    }

    /**
     * Find the next plageAstreinte according
     * to the current plageAstreinte parameters
     * return the number of weeks jumped
     *
     * @param int $init_user_id Utilisateur intial
     *
     * @return int
     * @throws Exception
     */
    public function becomeNext(int $init_user_id = null): int
    {
        $week_jumped = 0;
        if (!$this->_type_repeat) {
            $this->_type_repeat = self::TYPE_REPETITION_SIMPLE;
        }

        switch ($this->_type_repeat) {
            case self::TYPE_REPETITION_QUADRUPLE:
                $this->start = CMbDT::dateTime("+4 WEEK", $this->start);
                $this->end   = CMbDT::dateTime("+4 WEEK", $this->end);
                $week_jumped += 4;
                break;
            case self::TYPE_REPETITION_TRIPLE:
                $this->start = CMbDT::dateTime("+3 WEEK", $this->start);
                $this->end   = CMbDT::dateTime("+3 WEEK", $this->end);
                $week_jumped += 3;
                break;
            case self::TYPE_REPETITION_DOUBLE:
                $this->start = CMbDT::dateTime("+2 WEEK", $this->start);
                $this->end   = CMbDT::dateTime("+2 WEEK", $this->end);
                $week_jumped += 2;
                break;
            case self::TYPE_REPETITION_SIMPLE:
            default:
                $this->start = CMbDT::dateTime("+1 WEEK", $this->start);
                $this->end   = CMbDT::dateTime("+1 WEEK", $this->end);
                $week_jumped++;
                break;
        }

        // Stockage des champs modifiés
        $choose_astreinte = $this->choose_astreinte;
        $user_id          = $this->user_id;
        $start            = $this->start;
        $end              = $this->end;
        $libelle          = $this->libelle;
        $categorie        = $this->categorie;
        $group_id         = $this->group_id;
        $color            = $this->color;
        $type             = $this->type;
        $phone_astreinte  = $this->phone_astreinte;

        // Recherche de la plage suivante
        $where = [
            "user_id" => $this->getDS()->prepare("= ?", $init_user_id ?: $this->user_id),
            0         => "`start` = '$this->start' OR `end` = '$this->end'",
        ];

        $plages_astreintes = $this->loadList($where);

        if (count($plages_astreintes) > 0) {
            $this->load(reset($plages_astreintes)->plage_id);
        } else {
            $this->plage_id = null;
        }

        // Remise en place des champs modifiés
        $this->choose_astreinte = $choose_astreinte;
        $this->user_id          = $user_id;
        $this->start            = $start;
        $this->end              = $end;
        $this->libelle          = $libelle;
        $this->categorie        = $categorie;
        $this->group_id         = $group_id;
        $this->color            = $color;
        $this->type             = $type;
        $this->phone_astreinte  = $phone_astreinte;
        $this->updateFormFields();

        return $week_jumped;
    }

    /**
     * Count the number of CPlageAstreinte duplicated from the current CPlageAstreinte
     * @throws Exception
     */
    public function countDuplicatedPlages(): int
    {
        $ds    = $this->getDS();
        $where = [
            'user_id'          => $ds->prepare("= ?", $this->user_id),
            'type'             => $ds->prepare("= ?", $this->type),
            'choose_astreinte' => $ds->prepare("= ?", $this->choose_astreinte),
            'phone_astreinte'  => $ds->prepare("= ?", $this->phone_astreinte),
            'start'            => $ds->prepare("> ?", $this->start),
            'end'              => $ds->prepare("> ?", $this->end),
        ];

        return $this->_count_duplicated_plages = $this->countList($where);
    }
}
