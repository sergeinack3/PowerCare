{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  updateSelected = function (id) {
    removeSelected();
    var printer = $("printer-" + id);
    printer.addClassName("selected");
  };

  removeSelected = function () {
    var printer = $$(".oprinter.selected")[0];
    if (printer) {
      printer.removeClassName("selected");
    }
  };
</script>

<table class="tbl printerlist">
  <tr>
    <th class="title" colspan="3">
      {{tr}}CPrinter.list{{/tr}}
    </th>
  </tr>
  <tr>
    <th class="category">
      {{tr}}CPrinter-object_class{{/tr}}
    </th>
    <th class="category">
      {{tr}}CPrinter-function_id{{/tr}}
    </th>
    <th class="category">
      {{tr}}CPrinter-label{{/tr}}
    </th>
  </tr>
  {{foreach from=$printers item=_printer}}
    <tr id='printer-{{$_printer->_id}}' class="oprinter {{if $_printer->_id == $printer_id}}selected{{/if}}">
      <td>
        <a href="#1" onclick="Printer.editPrinter('{{$_printer->_id}}'); updateSelected('{{$_printer->_id}}');">
          {{$_printer->_view}}
        </a>
      </td>
      <td>
        {{$_printer->_ref_function->text}}
      </td>
      <td>
        {{mb_value object=$_printer field=label}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="3" class="empty">
        {{tr}}CPrinter.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>