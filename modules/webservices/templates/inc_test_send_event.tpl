{{*
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="formExecute" method="post" action="?m=webservices" onsubmit="return onSubmitFormAjax(this, null, 'result-execute')">
  <input type="hidden" name="m" value="webservices" />
  <input type="hidden" name="dosql" value="do_execute_method" />
  <input type="hidden" name="func" value="{{$method}}" />
  <input type="hidden" name="exchange_source_guid" value="{{$exchange_source_guid}}" />
  
  <table class="tbl">
    <tr>
      <th class="title" colspan="2">{{$method}}</th>
    </tr>
    <tr>
      <th class="category">Paramètres</th>
      <th class="category">Valeurs</th>
    </tr>
    {{foreach from=$parameters.2 key=key item=_parameter}}
    <tr>
      <td>{{$_parameter}} ({{$parameters.1.$key}})</td>
      <td>
        <input type="text" name="parameters[{{$key}}]" value="" />
      </td>
    </tr>
    {{/foreach}}
    <tr>
      <td colspan="2">
        <button class="tick" type="submit">{{tr}}Execute{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<textarea id="result-execute" rows="10"></textarea>