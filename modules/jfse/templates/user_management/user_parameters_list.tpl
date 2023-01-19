{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
    <thead>
        <tr>
            <th>{{mb_title class=CJfseUserParameter field=name}}</th>
            <th>{{mb_title class=CJfseUserParameter field=value}}</th>
        </tr>
    </thead>
    {{foreach from=$parameters item=parameter}}
      <tr id="CJfseUser-{{$user->id}}-UserParameter-{{$parameter->id}}-row">
          <td>
            {{mb_value object=$parameter field=name}}
          </td>
          <td>
              <form name="edit-CJfseUser-{{$user->id}}-UserParameter-{{$parameter->id}}" method="post" action="?" onsubmit="return false">
                  <button id="unlock-CJfseUser-{{$user->id}}-UserParameter-{{$parameter->id}}" type="button" class="unlock notext" onclick="UserManagement.enableUserParameter('{{$user->id}}', '{{$parameter->id}}');">
                      {{tr}}Unlock{{/tr}}
                  </button>
                  <button id="lock-CJfseUser-{{$user->id}}-UserParameter-{{$parameter->id}}" type="button" class="lock notext" onclick="UserManagement.disableUserParameter('{{$user->id}}', '{{$parameter->id}}');" style="display: none;">
                      {{tr}}Lock{{/tr}}
                  </button>

                  <input type="text" name="value" value="{{$parameter->value}}" disabled="disabled">

                  <button type="button" class="save notext" onclick="UserManagement.editUserParameter('{{$user->id}}', '{{$parameter->id}}');">
                      {{tr}}Save{{/tr}}
                  </button>
                  <button type="button" class="trash notext" onclick="UserManagement.deleteUserParameter('{{$user->id}}', '{{$parameter->id}}');">
                      {{tr}}Delete{{/tr}}
                  </button>
              </form>
          </td>
      </tr>
    {{foreachelse}}
      <tr>
          <td class="empty" colspan="2">
              {{tr}}CJfseUserParameter.none{{/tr}}
          </td>
      </tr>
    {{/foreach}}
</table>
