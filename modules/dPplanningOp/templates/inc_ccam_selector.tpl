{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
{{foreach from=$fusion item=curr_code name=fusion}}
  <td style="background-color: #{{$curr_code->couleur}};">
    <strong><span style="float:left">{{$curr_code->code}}</span>
    {{if $curr_code->occ==0}}
    <span style="float:right">Favoris</span>
    {{else}}
    <span style="float:right">{{$curr_code->occ}} acte(s)</span>
    {{/if}}
    </strong><br />
    <small>(
    {{foreach from=$curr_code->activites item=curr_activite}}
      {{foreach from=$curr_activite->phases item=curr_phase}}
        <a href="#" onclick="setClose('{{$curr_code->code}}-{{$curr_activite->numero}}-{{$curr_phase->phase}}', '{{$type}}','{{$curr_code->_default}}' )">{{$curr_activite->numero}}-{{$curr_phase->phase}}</a>
      {{/foreach}}
    {{/foreach}}   
    )</small>
    <br />
    {{$curr_code->libelleLong}}
    <br />
    <button class="tick" type="button" onclick="setClose('{{$curr_code->code}}', '{{$type}}','{{$curr_code->_default}}' )">
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
