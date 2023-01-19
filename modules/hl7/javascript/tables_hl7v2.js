/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Tables_hl7v2 = {
  editTableDescription: function (table_id, element) {
    if (element) {
      element.up('tr').addUniqueClassName('selected');
    }

    new Url("hl7", "ajax_edit_table_description")
      .addParam("table_id", table_id)
      .requestModal(500, 300);
  },

  loadEntries: function (table_number, element) {
    if (element) {
      element.up('tr').addUniqueClassName('selected');
    }

    new Url("hl7", "ajax_refresh_table_entries")
      .addParam("table_number", table_number)
      .requestModal("90%", "90%");
  },

  loadTables: function () {
    new Url("hl7", "ajax_refresh_tables")
      .requestUpdate("tables-hl7v2", Control.Modal.close());
  },

  changePage: function (page) {
    $V(getForm('listFilter').page, page);
  },

  refreshModalTableHL7Submit: function (table_number) {
    new Url("hl7", "ajax_refresh_hl7v2_table")
      .addParam("table_number", table_number)
      .requestUpdate("refreshModalTableHL7v2");
  }
}