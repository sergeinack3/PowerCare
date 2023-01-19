{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="poste-edit" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$poste}}
  {{mb_key   object=$poste}}

  <input type="hidden" name="del" value="0" />

  {{if !$show_sspi}}
    {{mb_field object=$poste field=sspi_id hidden=true}}
  {{/if}}

  <table class="form">
    <tr>
      {{mb_include module=system template=inc_form_table_header object=$poste}}
    </tr>
    <tr>
      <th>{{mb_label object=$poste field=nom}}</th>
      <td>{{mb_field object=$poste field=nom}}</td>
    </tr>
    {{if $show_sspi}}
      <tr>
        <th>{{mb_label object=$poste field=sspi_id}}</th>
        <td>
          <select name="sspi_id">
            <option value="">{{tr}}None|f{{/tr}}</option>
            {{foreach from=$sspis item=_sspi}}
              <option value="{{$_sspi->_id}}">{{$_sspi->_view}}</option>
            {{/foreach}}
          </select>
        </td>
      </tr>
    {{/if}}
    <tr>
      <th>{{mb_label object=$poste field=type}}</th>
      <td>{{mb_field object=$poste field=type}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$poste field=actif}}</th>
      <td>{{mb_field object=$poste field=actif}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if $poste->_id}}
          <button type="button" class="submit" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
          <button type="button" class="trash"
                  onclick="confirmDeletion(
                    this.form,
                    {typeName:'',objName:'{{$poste->nom|smarty:nodefaults|JSAttribute}}'},
                    Control.Modal.close
                  );">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button type="button" class="new" onclick="this.form.onsubmit();">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>