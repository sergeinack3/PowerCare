{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Edit-CPlateauTechnique" action="?m={{$m}}" method="post"
      onsubmit="return onSubmitFormAjax(this, function() {PlateauTechnique.refreshList()});">
  {{mb_class object=$plateau}}
  {{mb_key   object=$plateau}}
  {{mb_field object=$plateau field=group_id hidden=1}}
  <input type="hidden" name="del" value="0" />
  {{if !$plateau->_id}}
    <input type="hidden" name="callback" value="PlateauTechnique.loadForm" />
  {{/if}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$plateau}}
    {{if $plateau->group_id != $g}}
      <tr>
        <th>{{mb_label object=$plateau field=group_id}}</th>
        <td>{{mb_value object=$plateau field=group_id}}</td>
      </tr>
    {{/if}}

    <tr>
      <th>{{mb_label object=$plateau field=nom}}</th>
      <td>{{mb_field object=$plateau field=nom}}</td>
    </tr>

    {{if "psy"|module_active}}
      <tr>
        <th>{{mb_label object=$plateau field=type}}</th>
        <td>
          <select name="type">
            <option value="">&mdash; {{tr}}All{{/tr}}</option>
            {{if $m == "psy"}}
              <option value="psy" {{if $plateau->type == "psy"}}selected="selected"{{/if}}>
                {{tr}}CPlateauTechnique.type.psy{{/tr}}
              </option>
            {{else}}
              <option value="ssr" {{if $plateau->type == "ssr"}}selected="selected"{{/if}}>
                {{tr}}CPlateauTechnique.type.ssr{{/tr}}
              </option>
            {{/if}}
          </select>
        </td>
      </tr>
    {{/if}}

    <tr>
      <th>{{mb_label object=$plateau field=repartition}}</th>
      <td>{{mb_field object=$plateau field=repartition}}</td>
    </tr>

    <tr>
      <td class="button" colspan="4">
        {{if $plateau->_id}}
          <button class="modify me-primary">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button"
                  onclick="confirmDeletion(
                    this.form,
                    {ajax: 1, typeName: 'le plateau ', objName: '{{$plateau->_view|smarty:nodefaults|JSAttribute}}'},
                    function() {PlateauTechnique.refreshList(); PlateauTechnique.loadForm(0);}
                    );">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
{{mb_include module=ssr template=inc_back_plateau}}
