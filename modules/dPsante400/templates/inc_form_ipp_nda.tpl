{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=trad value=`$object->_class`-`$field`}}

<form name="edit{{$unique}}" method="post" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="dPsante400">
  <input type="hidden" name="del" value="0">
  {{mb_class object=$idex}}
  {{mb_key object=$idex}}
  {{mb_field object=$idex field=tag hidden=true}}
  {{mb_field object=$idex field=object_class hidden=true}}
  {{mb_field object=$idex field=object_id hidden=true}}
  {{mb_field object=$idex field=last_update value=now hidden=true}}
  <table class="form">
    <tr>
      <th style="width: 50%">
        <label title="{{tr}}{{$trad}}-desc{{/tr}}" for="edit{{$unique}}_id400">{{tr}}{{$trad}}{{/tr}}</label>
      </th>
      <td style="width: 50%">
        {{if $idex->id400}}
          {{mb_value object=$idex field=id400}}
        {{else}}
          {{mb_field object=$idex field=id400}}
        {{/if}}
        {{if !$idex->id400}}
          <button type="submit" class="save notext">{{tr}}Save{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>