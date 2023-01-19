{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination change_page=changePage}}

<table class="tbl me-no-align">
  <tr>
    <th class="narrow" rowspan="2">{{mb_title object=$cim field=code}}</th>
    <th rowspan="2">{{mb_title object=$cim field=libelle_court}}</th>
    <th rowspan="2">{{mb_title object=$cim field=libelle}}</th>
    <th colspan="4">{{mb_title object=$cim field=type_mco}}</th>
    {{if $modal}}
      <th rowspan="2" class="narrow"></th>
    {{/if}}
  </tr>
  <tr>
    <th>{{tr}}CCIM10.DP{{/tr}}</th>
    <th>{{tr}}CCIM10.DR{{/tr}}</th>
    <th>{{tr}}CCIM10.DAS{{/tr}}</th>
    <th>{{tr}}CCIM10.ailleurs{{/tr}}</th>
  </tr>
  {{foreach from=$list_cim item=_cim}}
    <tr>
      <td class="text narrow">
        <span onmouseover="ObjectTooltip.createEx(this, 'CCIM10-{{$_cim->id}}')"><strong>{{$_cim->code}}</strong></span></td>
      <td>{{$_cim->libelle_court}}</td>
      <td>{{$_cim->libelle}}</td>
      <td class="button narrow">
        {{if $_cim->type_mco == 0}}
          <i class="fas fa-check fa-lg" title="{{tr}}CCIM10.type.{{$_cim->type_mco}}{{/tr}}" style="color: forestgreen;"></i>
        {{else}}
          <i class="fas fa-ban fa-lg" title="{{tr}}CCIM10.type.{{$_cim->type_mco}}{{/tr}}" style="color: red;"></i>
        {{/if}}
      </td>
      <td class="button narrow">
        {{if $_cim->type_mco == 0 || $_cim->type_mco == 4}}
          <i class="fas fa-check fa-lg" title="{{tr}}CCIM10.type.{{$_cim->type_mco}}{{/tr}}" style="color: forestgreen;"></i>
        {{else}}
          <i class="fas fa-ban fa-lg" title="{{tr}}CCIM10.type.{{$_cim->type_mco}}{{/tr}}" style="color: red;"></i>
        {{/if}}
      </td>
      <td class="button narrow">
        {{if $_cim->type_mco != 3}}
          <i class="fas fa-check fa-lg" title="{{tr}}CCIM10.type.{{$_cim->type_mco}}{{/tr}}" style="color: forestgreen;"></i>
        {{else}}
          <i class="fas fa-ban fa-lg" title="{{tr}}CCIM10.type.{{$_cim->type_mco}}{{/tr}}" style="color: red;"></i>
        {{/if}}
      </td>
      <td class="button narrow">
        <i class="fas fa-check fa-lg" title="{{tr}}CCIM10.type.{{$_cim->type_mco}}{{/tr}}" style="color: forestgreen;"></i>
      </td>
      {{if $modal}}
        <td>
          <button type="button" class="tick notext" onclick="DiagPMSI.selectDiag('{{$_cim->code}}'); Control.Modal.close()"></button>
        </td>
      {{/if}}
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="8" class="empty">{{tr}}CCIM.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>