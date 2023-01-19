/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Printing =  window.Printing || {

  checkPrint: function(form) {
    if (checkForm(form)) {
      this.popPrint(form);
    }
    else {
      return;
    }
  },

  popPrint: function(form) {
    var url = new Url('pmsi', 'ajax_print_planning');
    url.addFormData(form);
    url.popup(900, 550, 'Planning');
  }
};
