{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Printer = {
    editPrinter:        function (id) {
      new Url("hospi", "ajax_edit_printer")
        .addParam("printer_id", id)
        .requestUpdate("edit_printer");
    },
    refreshList:        function (id) {
      new Url("hospi", "ajax_list_printers")
        .addNotNullParam("printer_id", id)
        .requestUpdate("list_printers");
    },
    after_edit_printer: function (id) {
      Printer.refreshList(id);
      Printer.editPrinter(id);
    }
  };

  Main.add(function () {
    Printer.refreshList();
    Printer.editPrinter('{{$printer_id}}');
  });
</script>

<div class="me-margin-top-4">
  <button class="new me-primary" onclick="removeSelected(); Printer.editPrinter(0)">
    {{tr}}CPrinter-title-create{{/tr}}
  </button>
</div>

<table class="main">
  <tr>
    <td id="list_printers" style="width: 45%;"></td>
    <!-- Création / Modification de l'imprimante -->
    <td id="edit_printer"></td>
  </tr>
</table>