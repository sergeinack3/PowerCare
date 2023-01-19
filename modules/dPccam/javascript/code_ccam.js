/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// $Id: $

CodeCCAM = {
  show: function(code, object_class) {
    var url = new Url("dPccam", "viewCcamCode");
    url.addParam("_codes_ccam", code);
    url.addParam("object_class", object_class);
    url.addParam("hideSelect", "1");
    url.modal();
  }
};
