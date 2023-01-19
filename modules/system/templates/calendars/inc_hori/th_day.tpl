{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=_name value=""}}
{{mb_default var=planning->type value="week"}}

{{assign var=daySel value=$planning->year_day_list.$_name}}

<span onmouseover="ObjectTooltip.createDOM(this, 'tooltip-CMbDay-{{$_name}}')">
  {{if $planning->type == "day"}}
    {{$_name|date_format:$conf.longdate}}
  {{elseif $planning->type == "week"}}
    {{$_name|date_format:$conf.longdate}}
  {{elseif $planning->type == "month"}}
    {{$_name|date_format:"%a %d %b"}}
  {{else}}
    {{$_name}}
  {{/if}}
</span>

<div id="tooltip-CMbDay-{{$_name}}" style="display: none;">
  <table class="tbl">
    <tr>
      <th colspan="2" class="title">{{$_name|date_format:$conf.longdate}}</th>
    </tr>
    <tr>
      <th>Jour numéro :</th><td>{{$daySel->number}} / {{$daySel->_nbDaysYear}}</td>
    </tr>
    <tr>
      <th>Jours restants dans l'année :</th><td>{{$daySel->days_left}}</td>
    </tr>
    <tr>
      <th>Semaine : </th><td>{{$daySel->date|date_format:"%W"}}</td>
    </tr>
    <tr>
      <th>Fête du jour</th><td>{{$daySel->name}}</td>
    </tr>
    {{if $daySel->ferie}}
      <tr>
        <td colspan="2">
          <div class="small-info">
            Ce jour est férié : {{$daySel->ferie}}
          </div>
        </td>
      </tr>
    {{/if}}

  </table>
</div>
