<?php
/**
 * @package Mediboard\Lpp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp;

use Ox\Core\CMbDT;
use Ox\Core\CModelObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;

/**
 * Description
 */
class CLPPDatedPricing extends CModelObject
{
    /** @var string The LPP code */
    public $code;

    /** @var string The date of effect */
    public $begin_date;

    /** @var string The date of the end of effect */
    public $end_date;

    /** @var string The prestation code to use */
    public $prestation_code;

    /** @var bool Indicate if a DEP must be asked */
    public $dep;

    /** @var string The date of government act */
    public $act_date;

    /** @var string The date of publication in the official journal */
    public $jo_date;

    /** @var bool Indicate if the price of the code must be on the quote */
    public $quote_pricing;

    /** @var float The price of the code */
    public $price;

    /** @var float The price increase for the DOM Guadeloupe */
    public $maj_guadeloupe;

    /** @var float The price increase for the DOM Martinique */
    public $maj_martinique;

    /** @var float The price increase for the DOM Guyane */
    public $maj_guyane;

    /** @var float The price increase for the DOM Reunion */
    public $maj_reunion;

    /** @var int The maximum authorized quantity */
    public $max_quantity;

    /** @var float The maximum authorized price */
    public $max_price;

    /** @var float The  amount */
    public $settled_price;

    /** @var int The PECP 1 (?) */
    public $pecp1;

    /** @var int The PECP 2 (?) */
    public $pecp2;

    /** @var int The PECP 3 (?) */
    public $pecp3;

    /** @var array A conversion table from the db fields to the object fields */
    public static $db_fields = [
        'CODE_TIPS'  => 'code',
        'DEBUTVALID' => 'begin_date',
        'FINHISTO'   => 'end_date',
        'NAT_PREST'  => 'prestation_code',
        'ENTENTE'    => 'dep',
        'ARRETE'     => 'act_date',
        'JO'         => 'jo_date',
        'PUDEVIS'    => 'quote_pricing',
        'TARIF'      => 'price',
        'MAJO_DOM1'  => 'maj_guadeloupe',
        'MAJO_DOM2'  => 'maj_martinique',
        'MAJO_DOM3'  => 'maj_guyane',
        'MAJO_DOM4'  => 'maj_reunion',
        'MAJO_DOM5'  => 'maj_st_pierre_miquelon',
        'MAJO_DOM6'  => 'maj_mayotte',
        'QTE_MAX'    => 'max_quantity',
        'MT_MAX'     => 'max_price',
        'PUREGLEMEN' => 'settled_price',
        'PECP01'     => 'pecp1',
        'PECP02'     => 'pecp2',
        'PECP03'     => 'pecp3',
    ];

    /**
     * CLPPDatedPricing constructor.
     *
     * @param array $data The data returned from the database
     */
    public function __construct(array $data = [])
    {
        parent::__construct();

        $this->hydrate($data);
    }

    public function hydrate(array $data = []): self
    {
        foreach ($data as $_column => $_value) {
            if (array_key_exists($_column, self::$db_fields)) {
                $_field = self::$db_fields[$_column];

                if ($_field == 'dep') {
                    $_value = $_value == 'O' ? 1 : 0;
                }

                $this->$_field = $_value;
            }
        }

        return $this;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['code']                   = 'num notNull';
        $props['begin_date']             = 'date notNull';
        $props['end_date']               = 'date';
        $props['prestation_code']        = 'str maxLength|3 notNull';
        $props['dep']                    = 'bool';
        $props['act_date']               = 'date';
        $props['jo_date']                = 'date';
        $props['quote_pricing']          = 'bool';
        $props['price']                  = 'currency notNull';
        $props['maj_guadeloupe']         = 'float';
        $props['maj_martinique']         = 'float';
        $props['maj_guyane']             = 'float';
        $props['maj_reunion']            = 'float';
        $props['maj_st_pierre_miquelon'] = 'float';
        $props['maj_mayotte']            = 'float';
        $props['max_quantity']           = 'num';
        $props['max_price']              = 'currency';
        $props['settled_price']          = 'currency';
        $props['pecp1']                  = 'num';
        $props['pecp2']                  = 'num';
        $props['pecp3']                  = 'num';

        return $props;
    }
}
