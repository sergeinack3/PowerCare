{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=mode_class value=CModeEntreeSejour}}

<button class="new me-primary" type="button" onclick="ParametrageMode.editModeEntreeSortie('{{$mode_class}}', 0)">
  {{tr}}{{$mode_class}}-title-create{{/tr}}
</button>
<button class="hslip" type="button" onclick="ParametrageMode.importModeEntreeSortie('{{$mode_class}}')">
  {{tr}}{{$mode_class}}-import{{/tr}}
</button>
<button class="hslip" type="button" onclick="ParametrageMode.exportModeEntreeSortie('{{$mode_class}}')">
  {{tr}}{{$mode_class}}-export{{/tr}}
</button>
<table class="main tbl">
  <tr>
    <th>{{mb_title class=$mode_class field=code}}</th>
    <th>{{mb_title class=$mode_class field=libelle}}</th>
    <th>{{mb_title class=$mode_class field=mode}}</th>
    <th>{{mb_title class=$mode_class field=actif}}</th>
  </tr>

  {{foreach from=$list_modes item=_mode}}
    <tr>
      <td>
        <button type="button" class="edit notext me-tertiary" onclick="ParametrageMode.editModeEntreeSortie('{{$mode_class}}', {{$_mode->_id}})">
          {{tr}}Edit{{/tr}}
        </button>
        {{mb_value object=$_mode field=code}}
      </td>
      <td>{{mb_value object=$_mode field=libelle}}</td>
      <td>{{mb_value object=$_mode field=mode}}</td>
      <td>
        <form name="editActif{{$_mode->_guid}}"  method="post" onsubmit="return onSubmitFormAjax(this)">
          {{mb_key object=$_mode}}
          {{mb_class object=$_mode}}
          {{mb_field object=$_mode field="actif" onchange=this.form.onsubmit()}}
        </form>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="4" class="empty">{{tr}}{{$mode_class}}.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
