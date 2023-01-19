{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Edit-CEcheance" action="?m={{$m}}" method="post" onsubmit="return Echeance.submit(this);">
  {{mb_key    object=$echeance}}
  {{mb_class  object=$echeance}}
  <input type="hidden" name="del" value="0"/>
  <input type="hidden" name="object_id"    value="{{$echeance->object_id}}"/>
  <input type="hidden" name="object_class" value="{{$echeance->object_class}}"/>
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$echeance}}
    <tr>
      <th>{{mb_label object=$echeance field=date}}</th>
      <td>{{mb_field object=$echeance field=date form="Edit-CEcheance" canNull="false" register=true}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$echeance field=montant}}</th>
      <td>{{mb_field object=$echeance field=montant}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$echeance field=description}}</th>
      <td>{{mb_field object=$echeance field=description area=true}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if $echeance->_id}}
          <button class="submit" type="button" onclick="Echeance.submit(this.form);">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="reset" onclick="Echeance.delete(this.form);">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="button" onclick="Echeance.submit(this.form);">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>