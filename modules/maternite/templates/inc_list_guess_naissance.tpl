{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  is_cesarienne = function (naissance_id) {
    var form = getForm('change_cesar');
    $V(form.naissance_id, naissance_id);
    $V(form.by_caesarean, "1");
    form.onsubmit();

    //cleanup
    $V(form.naissance_id, '0');
    $V(form.by_caesarean, '0');
  };
</script>

<form method="post" name="change_cesar" onsubmit="return onSubmitFormAjax(this, reloadListCesar);">
  {{mb_class object=$naissance}}
  <input type="hidden" name="naissance_id" value="" />
  <input type="hidden" name="by_caesarean" value="0" />
</form>

<table class="tbl">
  <tr>
    <th>{{tr}}CNaissance{{/tr}}</th>
    <th>{{tr}}COperation{{/tr}}</th>
    <th>{{tr}}CSalle{{/tr}}</th>
    <th>{{tr}}Action{{/tr}}</th>
  </tr>
  {{foreach from=$naissances item=_naissance}}
    <tr>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_naissance->_guid}}');">
          {{$_naissance}}
        </span>
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_naissance->_ref_operation->_guid}}');">
          {{$_naissance->_ref_operation}}
        </span>
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_naissance->_ref_operation->_ref_salle->_guid}}');">
          {{$_naissance->_ref_operation->_ref_salle}}
        </span>
      </td>
      <td>
        <button onclick="is_cesarienne('{{$_naissance->_id}}');">Cesarienne</button>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="4" class="empty">{{tr}}CNaissance.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>