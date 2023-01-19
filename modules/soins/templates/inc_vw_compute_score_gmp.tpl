{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="3">
      {{tr}}CExamGir-GMP score in EHPAD{{/tr}}
      ({{tr var1=$_date_min|date_format:$conf.date var2=$_date_max|date_format:$conf.date}}common-From %s to %s{{/tr}})
      &mdash; 
      {{if $service && $service->_id}}
        {{$service->_view}}
      {{elseif $service_id == "NP"}}
        {{tr}}CService-Not placed{{/tr}}
      {{else}}
        {{tr}}CService.all{{/tr}}
      {{/if}}
    </th>
  </tr>
  <tr>
    <th>{{tr}}CExamGir{{/tr}}</th>
    <th>{{tr}}CExamGir-Total GIR points{{/tr}}</th>
    <th>{{tr}}CExamGir-Total patients{{/tr}}</th>
  </tr>
  {{counter start=1 assign="compteur"}}
  {{foreach from=$gir_points item=_gir}}
    <tr>
      <td>{{$compteur}}</td>
      <td>{{$_gir.points}}</td>
      <td>{{$_gir.patients}}</td>
    </tr>
    {{counter}}
  {{/foreach}}
  <tr>
    <th>{{tr}}common-Total|pl{{/tr}}</th>
    <td>{{$totaux.gir_points}}</td>
    <td>{{$totaux.patients}}</td>
  </tr>
  <tr>
    <th>{{tr}}CExamGir-GMP score in EHPAD-court{{/tr}}</th>
    <td colspan="2">{{$totaux.GMP}}</td>
  </tr>
</table>
