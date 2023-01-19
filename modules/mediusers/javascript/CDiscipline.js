/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * JS function disciplone
 */
CDiscipline = {
  edit: function(discipline_id, element) {
    if (element) {
      element.up('tr').addUniqueClassName('selected');
    }

    new Url("mediusers", "ajax_edit_discipline")
      .addParam("discipline_id", discipline_id)
      .requestModal(800, 600)
      .modalObject.observe("afterClose", function() {
        getForm('listFilter').onsubmit();
      });
  },

  changePage: function(page) {
    $V(getForm('listFilter').page, page);
  }
}