<?php

/**
 * @package Mediboard\Ucum
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ucum\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CView;
use Ox\Core\CLegacyController;
use Ox\Mediboard\Ucum\Ucum;
use Psr\SimpleCache\InvalidArgumentException;

class UcumLegacyController extends CLegacyController
{
    public function demo(): void
    {
        $this->renderSmarty(
            "vw_results",
            [
                'sourceSearch' => Ucum::getSource('UcumSearch')->host,
            ]
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function conversion(): void
    {
        $quantity = CView::get('quantity', 'str', true);
        $from     = CView::get('from', 'str', true);
        $to       = CView::get('to', 'str', true);
        $cache    = CView::get('cache', 'bool default|0');

        CView::checkin();

        $conversion = null;

        if ($quantity && $from && $to) {
            $conversion = (new Ucum())->callConversion($quantity, $from, $to, $cache);
        }

        $this->renderSmarty(
            'inc_vw_conversion.tpl',
            [
                'quantity'     => $quantity,
                'from'         => $from,
                'to'           => $to,
                'sourceSearch' => Ucum::getSource('UcumSearch')->host,
                'conversion'   => $conversion,
            ]
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function validation(): void
    {
        $unit  = CView::get('unit', 'str', true);
        $cache = CView::get('cache', 'bool default|0');

        CView::checkin();

        $validation = false;

        if ($unit) {
            $validation = (new Ucum())->callValidation($unit, $cache);
        }

        $this->renderSmarty(
            'inc_vw_valid.tpl',
            [
                'isValid'      => $unit,
                'sourceSearch' => Ucum::getSource('UcumSearch')->host,
                'validation'   => $validation == 'true' ? CAppUI::tr(
                    'Yes'
                ) : CAppUI::tr(
                    'No'
                ),
            ]
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function toBase(): void
    {
        $units = CView::get('units', 'str', true);
        $cache = CView::get('cache', 'bool default|0');

        CView::checkin();

        $this->renderSmarty(
            'inc_vw_to_base.tpl',
            [
                'toBaseUnit'   => $units,
                'sourceSearch' => Ucum::getSource('UcumSearch')->host,
                'toBase'       => (new Ucum())->callToBase($units, $cache),
            ]
        );
    }
}
