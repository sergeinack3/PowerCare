{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  {{tr}}cache_tester-metamodel-info1{{/tr}}
  <br/>
  {{tr}}cache_tester-metamodel-info2{{/tr}}
</div>

<table class="tbl">
  <tr>
    <th>{{tr}}Action{{/tr}}</th>
    <th>{{tr}}Total{{/tr}} (ms)</th>
    <th>{{tr}}Average{{/tr}} (ms)</th>
  </tr>
  {{foreach from=$chrono->report key=_key item=_chrono}}
    <tr>
      <td>{{$_chrono->nbSteps}} x {{$_key}}</td>
      <td style="text-align: center">
        {{math assign=total equation="x * 1000" x=$_chrono->total}} {{$total|float:2}}
      </td>
      <td style="text-align: center">
        {{math assign=total equation="x * 1000" x=$_chrono->avgStep}} {{$total|float:2}}
      </td>
    </tr>
  {{/foreach}}
</table>