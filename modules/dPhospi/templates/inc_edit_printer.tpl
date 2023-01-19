{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$sources|@count}}
  <div class="info">
    {{tr}}CPrinter.no_sources{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{if !$printer->_id}}
  <script>
    Main.add(function () {
      var oForm = getForm("editPrinter");
      $V(oForm.object_class, oForm.object_id.options[oForm.object_id.selectedIndex].get('object_class'));
    });
  </script>
{{/if}}

<form name="editPrinter" onsubmit="return onSubmitFormAjax(this);" method="post">
  {{mb_class object=$printer}}
  {{mb_key object=$printer}}
  <input type="hidden" name="callback" value="Printer.after_edit_printer" />
  <input type="hidden" name="del" value="0" />
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$printer}}

    <tr>
      <th>
        {{mb_label object=$printer field=function_id}}
      </th>
      <td>
        <select name="function_id">
          {{foreach from=$functions item=_function}}
            <option value='{{$_function->_id}}'
                    {{if $printer->function_id == $_function->_id}}selected{{/if}}>{{$_function->_view}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$printer field=object_class}}
      </th>
      <td>
        <select name="object_id" onchange="$V(this.form.object_class, this.options[this.selectedIndex].get('object_class'));">
          {{foreach from=$sources item=_source}}
            <option value="{{$_source->_id}}" data-object_class="{{$_source->_class}}"
                    {{if $printer->object_id == $_source->_id && $printer->object_class == $_source->_class}}selected{{/if}}>
              {{$_source}}
            </option>
          {{/foreach}}
        </select>
        <input type="hidden" name="object_class" value="{{$printer->object_class}}" />
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$printer field=label}}</th>
      <td>{{mb_field object=$printer field=label}}</td>
    </tr>
    <tr>
    <tr>
      <td colspan="4" style="text-align: center">
        <button class="modify me-primary">{{tr}}Save{{/tr}}</button>
        {{if $printer->_id}}
          <button class="cancel" onclick="confirmDeletion(this.form, {
            typeName: 'l\'imprimante',
            objName:'{{$printer->_view|smarty:nodefaults|JSAttribute}}',
            ajax: true})" type="button">{{tr}}Delete{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>