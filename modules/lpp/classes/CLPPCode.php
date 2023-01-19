<?php
/**
 * @package Mediboard\Lpp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp;

use Ox\Core\CModelObject;
use Ox\Mediboard\Lpp\Exceptions\LppDatabaseException;
use Ox\Mediboard\Lpp\Repository\LppChapterRepository;
use Ox\Mediboard\Lpp\Repository\LppCodeRepository;
use Ox\Mediboard\Lpp\Repository\LppPricingRepository;

/**
 * Represent a LPP code
 */
class CLPPCode extends CModelObject
{

    /** @var string The LPP code */
    public $code;

    /** @var string The full name of the code */
    public $name;

    /** @var string The date from which the code is not valid anymore */
    public $end_date;

    /** @var integer The maximum age */
    public $max_age;

    /** @var string The type of prestation for the code */
    public $prestation_type;

    /** @var bool Show if there are medical indication for this prestation */
    public $indication;

    /** @var integer The number of the first chapter */
    public $chapter_1;

    /** @var integer The number of the second chapter */
    public $chapter_2;

    /** @var integer The number of the third chapter */
    public $chapter_3;

    /** @var integer The number of the fourth chapter */
    public $chapter_4;

    /** @var integer The number of the fifth chapter */
    public $chapter_5;

    /** @var integer The number of the sixth chapter */
    public $chapter_6;

    /** @var integer The number of the seventh chapter */
    public $chapter_7;

    /** @var integer The number of the eighth chapter */
    public $chapter_8;

    /** @var integer The number of the ninth chapter */
    public $chapter_9;

    /** @var integer The number of the tenth chapter */
    public $chapter_10;

    /** @var integer The rank of the code in its parent chapter */
    public $rank;

    /** @var integer The id of the prosthesis */
    public $prosthesis;

    /** @var integer The first medical reference number */
    public $rmo_1;

    /** @var integer The second medical reference number */
    public $rmo_2;

    /** @var integer The third medical reference number */
    public $rmo_3;

    /** @var integer The fourth medical reference number */
    public $rmo_4;

    /** @var integer The fifth medical reference number */
    public $rmo_5;

    /** @var CLPPDatedPricing[] The dated pricings */
    public $_pricings;

    /** @var CLPPDatedPricing The pricing that's still in effect */
    public $_last_pricing;

    /** @var CLPPCode[] The compatible code */
    public $_compatibilities;

    /** @var CLPPCode[] The incompatible codes */
    public $_incompatibilities;

    /** @var string The id of the parent chapter */
    public $_parent_id;

    /** @var CLPPChapter The parent chapter of the code */
    public $_parent;

    /** @var array The list of the unauthorized expense qualifying */
    public $_unauthorized_expense_qualifying = [];

    /** @var array A conversion table from the db fields to the object fields */
    public static $db_fields = [
        'CODE_TIPS'  => 'code',
        'NOM_COURT'  => 'name',
        'RMO1'       => 'rmo_1',
        'RMO2'       => 'rmo_2',
        'RMO3'       => 'rmo_3',
        'RMO4'       => 'rmo_4',
        'RMO5'       => 'rmo_5',
        'DATE_FIN'   => 'end_date',
        'AGE_MAX'    => 'max_age',
        'TYPE_PREST' => 'prestation_type',
        'INDICATION' => 'indication',
        'ARBO1'      => 'chapter_1',
        'ARBO2'      => 'chapter_2',
        'ARBO3'      => 'chapter_3',
        'ARBO4'      => 'chapter_4',
        'ARBO5'      => 'chapter_5',
        'ARBO6'      => 'chapter_6',
        'ARBO7'      => 'chapter_7',
        'ARBO8'      => 'chapter_8',
        'ARBO9'      => 'chapter_9',
        'ARBO10'     => 'chapter_10',
        'PLACE'      => 'rank',
        'PROTHESE'   => 'prosthesis',
    ];

    /**
     * CLPPCode constructor.
     *
     * @param array $data The data returned from the database
     */
    public function __construct(array $data = [], LppCodeRepository $repository = null)
    {
        parent::__construct();

        foreach ($data as $_column => $_value) {
            if (array_key_exists($_column, self::$db_fields)) {
                $_field = self::$db_fields[$_column];

                if ($_field == 'indication') {
                    $_value = $_value == 'O' ? 1 : 0;
                }

                $this->$_field = $_value;
            }
        }
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['code']            = 'str maxLength|7 minLength|7 notNull';
        $props['name']            = 'str notNull';
        $props['end_date']        = 'date';
        $props['max_age']         = 'num default|0';
        $props['prestation_type'] = 'enum list|A|E|L|P|S|R|V';
        $props['indication']      = 'bool';
        $props['chapter_1']       = 'num';
        $props['chapter_2']       = 'num';
        $props['chapter_3']       = 'num';
        $props['chapter_4']       = 'num';
        $props['chapter_5']       = 'num';
        $props['chapter_6']       = 'num';
        $props['chapter_7']       = 'num';
        $props['chapter_8']       = 'num';
        $props['chapter_9']       = 'num';
        $props['chapter_10']      = 'num';
        $props['rank']            = 'num notNull';
        $props['prosthesis']      = 'num';
        $props['rmo_1']           = 'num';
        $props['rmo_2']           = 'num';
        $props['rmo_3']           = 'num';
        $props['rmo_4']           = 'num';
        $props['rmo_5']           = 'num';
        $props['_parent_id']      = 'ref class|CLPPChapter';

        return $props;
    }

    /**
     * Load the pricings for this codes
     *
     * @return CLPPDatedPricing[]
     */
    public function loadPricings(): array
    {
        if (!$this->_pricings) {
            try {
                $this->_pricings = LppPricingRepository::getInstance()->loadListFromCode($this->code);
            } catch (LppDatabaseException $e) {
                $this->_pricings = [];
            }
        }

        return $this->_pricings;
    }

    /**
     * Load the latest pricing available at the given date
     *
     * @param string|null $date The date
     *
     * @return CLPPDatedPricing
     */
    public function loadLastPricing(string $date = null): CLPPDatedPricing
    {
        if (!$this->_last_pricing) {
            try {
                $this->_last_pricing = LppPricingRepository::getInstance()->loadLastPricingForCode($this->code, $date);
            } catch (LppDatabaseException $e) {
                $this->_pricings = [];
            }
        }

        return $this->_last_pricing;
    }

    /**
     * Load the code compatible with this one
     *
     * @return CLPPCode[]
     */
    public function loadCompatibilities(): array
    {
        if (!$this->_compatibilities) {
            try {
                $this->_compatibilities = LppCodeRepository::getInstance()->loadCompatibleCodes($this->code);
            } catch (LppDatabaseException $e) {
                $this->_compatibilities = [];
            }
        }

        return $this->_compatibilities;
    }

    /**
     * Load the code incompatible with this one
     *
     * @return CLPPCode[]
     */
    public function loadIncompatibilities(): array
    {
        if (!$this->_incompatibilities) {
            try {
                $this->_incompatibilities = LppCodeRepository::getInstance()->loadIncompatibleCodes($this->code);
            } catch (LppDatabaseException $e) {
                $this->_incompatibilities = [];
            }
        }

        return $this->_incompatibilities;
    }

    /**
     * Get the parent id from the chapter fields
     *
     * @return string
     */
    public function getParentId(): string
    {
        $this->_parent_id = '0';

        for ($i = 1; $i <= 10; $i++) {
            $field = "chapter_$i";
            if (!$this->$field || $this->$field == '0') {
                break;
            }

            switch ($this->$field) {
                case 10:
                    $_chapter = 'A';
                    break;
                case 11:
                    $_chapter = 'B';
                    break;
                case 12:
                    $_chapter = 'C';
                    break;
                case 13:
                    $_chapter = 'D';
                    break;
                case 14:
                    $_chapter = 'E';
                    break;
                case 15:
                    $_chapter = 'F';
                    break;
                default:
                    $_chapter = $this->$field;
            }

            $this->_parent_id .= $_chapter;
        }

        return $this->_parent_id;
    }

    /**
     * Load the parent chapter of the code
     *
     * @return ?CLPPChapter
     */
    public function loadParent(): ?CLPPChapter
    {
        if (!$this->_parent) {
            $this->getParentId();

            /* It is possible that the full chapter code doesn't exists,
             * so we load the levels chapters until we found one that exists */
            $i = strlen($this->_parent_id);
            while (!$this->_parent) {
                $parent_id = $this->_parent_id;
                if ($i != strlen($this->_parent_id)) {
                    $parent_id = substr($this->_parent_id, 0, $i);
                }


                try {
                    $this->_parent = LppChapterRepository::getInstance()->loadChapter($parent_id);
                } catch (LppDatabaseException $e) {
                    $this->_parent = null;;
                }

                $i--;
                if ($i <= 2) {
                    break;
                }
            }
        }

        return $this->_parent;
    }

    /**
     * Get the list of authorized expense qualifying
     *
     * @return array
     */
    public function getQualificatifsDepense(): array
    {
        if ($this->_last_pricing->prestation_code) {
            try {
                $this->_unauthorized_expense_qualifying = LppCodeRepository::getInstance()->getExpenseQualifiersForCode(
                    $this->_last_pricing->prestation_code
                );
            } catch (LppDatabaseException $e) {
                $this->_unauthorized_expense_qualifying = [];
            }
        }
        return $this->_unauthorized_expense_qualifying;
    }
}
