/**
 * @package Mediboard\Lpp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

LPP = {
    viewCode: function (code) {
        new Url('lpp', 'viewCode')
            .addParam('code', code)
            .requestModal();
    }
};
