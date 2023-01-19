{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editFormAllaitement" method="post" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="maternite" />
  {{mb_class object=$allaitement}}
  {{mb_key   object=$allaitement}}
  <input type="hidden" name="callback" value="Allaitement.afterEditAllaitement" />
  <input type="hidden" name="del" value="0" />

  {{mb_field object=$allaitement field=patient_id hidden=true}}

  <table class="form">
    <tr>
      {{mb_include module=system template=inc_form_table_header object=$allaitement}}
    </tr>
    <tr>
      <th>
        {{mb_label object=$allaitement field=date_debut}}
      </th>
      <td>
        {{mb_field object=$allaitement field=date_debut form=editFormAllaitement register=true}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$allaitement field=date_fin}}
      </th>
      <td>
        {{mb_field object=$allaitement field=date_fin form=editFormAllaitement register=true}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$allaitement field=grossesse_id}}
      </th>
      <td>
        <select name="grossesse_id">
          <option value="">&mdash; Choisissez une grossesse</option>
          {{foreach from=$grossesses item=_grossesse}}
            <option value="{{$_grossesse->_id}}"
                    {{if $_grossesse->_id == $allaitement->grossesse_id}}selected{{/if}}>{{$_grossesse}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button type="button" class="save"
                onclick="this.form.onsubmit()">{{if $allaitement->_id}}{{tr}}Save{{/tr}}{{else}}{{tr}}Create{{/tr}}{{/if}}</button>
        <button type="button" class="cancel"
                onclick="confirmDeletion(this.form, {objName: '{{$allaitement}}', ajax: 1})">{{tr}}Delete{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
