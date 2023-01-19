{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$printers|@count}}
  Pas d'imprimantes
  {{mb_return}}
{{/if}}
<script>
  printEtiquette = function(printer_guid) {
    new Url("hospi", "print_etiquettes")
      .addParam("object_id", '{{$object_id}}')
      .addParam("object_class", '{{$object_class}}')
      .addParam("modele_etiquette_id", '{{$modele_etiquette_id}}')
      .addParam("printer_guid", printer_guid)
      .requestUpdate("systemMsg");
    Control.Modal.close();
  };

  printCompteRendu = function(printer_guid) {
    new Url("compteRendu", "ajax_print")
      .addParam("printer_guid", printer_guid)
      .addParam("file_id", Thumb.file_id)
      .requestUpdate("systemMsg");
    Control.Modal.close();
  }
</script>

<table class="tbl">
  <tr>
    <th {{if !$mode_etiquette}}style="width: 50%"{{/if}}>
      {{tr}}CPrinter{{/tr}}
    </th>
    {{if !$mode_etiquette}}
      <td rowspan="{{math equation="x+y+1" x=$printers|@count y=$other_printers|@count}}">
        <div id="state" class="loading"
          style="width: 100%; height: 100%; background-position: 20%; margin-top: 1em; text-align: center; font-weight: bold;">
          {{tr}}CCompteRendu.generating_pdf{{/tr}}
        </div>
      </td>
    {{/if}}
  </tr>
  {{foreach from=$printers item=_printer}}
  <tr>
    <td style="line-height: 2;">
       <button onclick="
         {{if $mode_etiquette}}
           printEtiquette('{{$_printer->object_class}}-{{$_printer->object_id}}');
         {{else}}
           printCompteRendu('{{$_printer->object_class}}-{{$_printer->object_id}}');
         {{/if}}"
         class="print printer" {{if !$mode_etiquette}}disabled{{/if}}>
         {{$_printer}}
       </button>
    </td>
  </tr>
  {{/foreach}}
  <tr>
    <td style="line_height: 2;">
      <select id="other_printer">
      {{foreach from=$other_printers item=_other_printer}}
        <option value="{{$_other_printer->_guid}}">{{$_other_printer}}</option>
      {{/foreach}}
      </select>
      <button type="button" class="print printer notext" {{if !$mode_etiquette}}disabled{{/if}}
              onclick="
              {{if $mode_etiquette}}
                printEtiquette($('other_printer').value);
              {{else}}
                printCompteRendu($('other_printer').value);
              {{/if}}">{{tr}}Print{{/tr}}</button>
    </td>
  </tr>
</table>
