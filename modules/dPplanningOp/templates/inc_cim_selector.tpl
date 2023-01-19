{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
{{foreach from=$fusion item=curr_code key=curr_key name=fusion}}
  <td>
    <strong><span style="float:left">{{$curr_code->code}}</span>
    {{if $curr_code->occurrences==0}}
    <span style="float:right">Favoris</span>
    {{else}}
    <span style="float:right">{{$curr_code->occurrences}}</span>
    {{/if}}
    </strong><br />
    {{$curr_code->libelle}}
    <br />
    <button class="tick" type="button" onclick="setClose('{{$curr_code->code}}', '{{$type}}' )">
      {{tr}}Select{{/tr}}
    </button>
  </td>  
{{if $smarty.foreach.fusion.index % 3 == 2}}
</tr><tr>
{{/if}}
{{foreachelse}}
   <td class="empty">Aucun code</td>
{{/foreach}}
</tr>
  