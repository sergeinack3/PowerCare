/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Mouvement = {
  json:      {},
  selectAll: function (valeur) {
    $('list_mvts_stock').select('input[class=mvt_stock]').each(function (e) {
      $V(e, valeur);
    });
  },

  checkCountElts: function () {
    var checked = 0;
    var count = 0;
    $('list_mvts_stock').select('input[class=mvt_stock]').each(function (e) {
      count++;
      if ($V(e)) {
        checked++;
      }
    });
    var element = $('check_all_mvts');
    element.checked = '';
    element.style.opacity = '1';
    if (checked > 0) {
      element.checked = 'checked';
      $V(element, 1);
      if (checked < count) {
        element.style.opacity = '0.5';
      }
    }
  },
  send:           function (form) {
    $V(form.mvts_ids, Object.toJSON(Mouvement.json));
    return onSubmitFormAjax(form, refreshMvts);
  }
};