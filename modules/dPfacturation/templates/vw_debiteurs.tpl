{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=debiteur ajax=true}}

<div id="list_debiteurs">
  <table class="main tbl">
    <tr>
      <th colspan="7" class="title">
        <button type="button" class="add me-float-none me-margin-right-4" onclick="Debiteur.edit('0');" style="float: left;margin-right: -95px;">{{tr}}CDebiteur-title-create{{/tr}}</button>
        {{tr}}CDebiteur.all{{/tr}}
      </th>
    </tr>
    <tr>
      <th class="narrow">{{mb_title class= CDebiteur field=numero}}</th>
      <th>{{mb_title class= CDebiteur field=nom}}</th>
      <th>{{mb_title class= CDebiteur field=description}}</th>
    </tr>
    {{foreach from=$debiteurs item=debiteur}}
      <tr style="text-align:center;">
        <td><a href="#" onclick="Debiteur.edit('{{$debiteur->_id}}');">{{mb_value object=$debiteur field=numero}}</a></td>
        <td>{{mb_value object=$debiteur field=nom}}</td>
        <td>{{mb_value object=$debiteur field=description}}</td>
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="3" class="empty">{{tr}}CDebiteur.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
</div>