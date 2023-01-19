{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=duree_max value="personnel CRemplacement duree_max"|gconf}}
{{if $duree_max}}
  <div class="small-info">
    {{tr var1=$duree_max}}CRemplacement-depassement_duree_max %s{{/tr}}
  </div>
{{/if}}

{{assign var=name_form value="edit_`$remplacement->_guid`"}}
<form name="{{$name_form}}" action="#" method="post" onsubmit="return Remplacement.onSubmit(this);">
  {{mb_class object=$remplacement}}
  {{mb_key   object=$remplacement}}
  <input type="hidden" name="del" value="0"/>
  <input type="hidden" name="callback" value="Remplacement.afterStore" />
  <input type="hidden" name="user_id" value="{{$user->_id}}" />
  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$remplacement}}
    <tr>
      <th>{{mb_label object=$remplacement field=libelle}}</th>
      <td>{{mb_field object=$remplacement field=libelle}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$remplacement field=debut}}</th>
      <td>{{mb_field object=$remplacement field=debut register=true form=$name_form}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$remplacement field=fin}}</th>
      <td>{{mb_field object=$remplacement field=fin register=true form=$name_form}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$remplacement field=remplace_id}}</th>
      <td>
        <select name="remplace_id" class="{{$remplacement->_specs.remplace_id}}">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$replacers selected=$remplacement->remplace_id}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$remplacement field=remplacant_id}}</th>
      <td>
        <select name="remplacant_id" class="{{$remplacement->_specs.remplacant_id}}">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$replacers selected=$remplacement->remplacant_id}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$remplacement field=description}}</th>
      <td>{{mb_field object=$remplacement field=description}}</td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button type="button" class="save" onclick="return Remplacement.onSubmit(this.form);">{{tr}}Save{{/tr}}</button>
        {{if $remplacement->_id}}
          <button type="button" class="trash" onclick="Remplacement.askDelete(this.form);">{{tr}}Delete{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>