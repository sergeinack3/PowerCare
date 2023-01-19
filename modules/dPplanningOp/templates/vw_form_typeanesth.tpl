{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editType" action="?m={{$m}}&tab=vw_edit_typeanesth" method="post"
      onsubmit="return TypeAnesth.submitSaveForm(this);">
  {{mb_class object=$type_anesth}}
  {{mb_key   object=$type_anesth}}
  <input type="hidden" name="del" value="0" />
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$type_anesth}}
    <tr>
      <th>{{mb_label object=$type_anesth field="name"}}</th>
      <td>{{mb_field object=$type_anesth field="name"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$type_anesth field="ext_doc"}}</th>
      <td>{{mb_field object=$type_anesth field="ext_doc" emptyLabel="Choose"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$type_anesth field="duree_postop"}}</th>
      <td>{{mb_field object=$type_anesth field="duree_postop" register=true form=editType canNull=true}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$type_anesth field="group_id"}}</th>
      <td>{{mb_field object=$type_anesth field="group_id" choose="All" options=$groups}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$type_anesth field="actif"}}</th>
      <td>{{mb_field object=$type_anesth field="actif"}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if $type_anesth->_id}}
          <button class="submit">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button"
                  onclick="TypeAnesth.submitRemoveForm(this.form, '{{$type_anesth->name|smarty:nodefaults|JSAttribute}}');">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>