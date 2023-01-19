/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Configuration = {
  edit: function(module, inherit, container) {
    var url = new Url("system", "ajax_edit_configuration");

    if (module) {
      url.addParam("module", module);
    }

    if (inherit) {
      url.addParam("inherit[]", inherit, true);
    }

    if (container = $(container)) {
      url.requestUpdate(container);
    }
    else {
      url.requestModal(950, 700);
    }
  }
};
