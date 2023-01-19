{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination total=$list_count current=$start step=50 change_page='LongRequestLog.changePageLongRequest' jumper=1}}

<table class="tbl">
  <tr>
    <th class="narrow"></th>
    <th class="narrow"></th>
    <th class="narrow">{{mb_title class=CLongRequestLog field=datetime_start}}</th>
    <th class="narrow">{{mb_title class=CLongRequestLog field=datetime_end}}</th>
    <th colspan="2" style="width: 75px;">{{mb_title class=CLongRequestLog field=duration}} (s)</th>
    <th class="narrow">{{mb_title class=CLongRequestLog field=_enslaved}}</th>
    <th class="narrow">{{mb_title class=CLongRequestLog field=server_addr}}</th>
    <th class="narrow">{{mb_title class=CLongRequestLog field=_module}}</th>
    <th class="narrow">{{mb_title class=CLongRequestLog field=_action}}</th>
    <th class="narrow">{{mb_title class=CLongRequestLog field=session_id}}</th>
    <th>{{mb_title class=CLongRequestLog field=user_id}}</th>
  </tr>

    {{foreach from=$logs item=_log}}
      <tr>
        <td>
          <button class="search notext compact" onclick="LongRequestLog.edit('{{$_log->_id}}')"></button>
        </td>

        <td style="text-align: center;">
            {{if $_log->isPublic()}}
              <i class="fa fa-user-secret fa-lg" title="{{tr}}Public{{/tr}}"></i>
            {{elseif $_log->_ref_user->isRobot()}}
              <i class="fas fa-robot fa-lg" style="color: steelblue;" title="{{tr}}common-Bot{{/tr}}"></i>
            {{else}}
              <i class="fas fa-user fa-lg" style="color: forestgreen;" title="{{tr}}common-Human{{/tr}}"></i>
            {{/if}}
        </td>

        <td>{{$_log->datetime_start}}</td>

        <td>{{$_log->datetime_end}}</td>

        <td class="narrow" style="text-align: right;">
            {{$_log->duration|number_format:3:'.':' '}}
        </td>

        <td class="narrow">
            {{assign var=ratio value=$_log->_performance_ratio}}
            {{assign var=ratio_transport value=$_log->_transport_ratio}}

          <div class="progressBar" style="display: inline-block; width: 40px;"
               title="{{$ratio}} % du temps passé en requêtes aux sources de données (SGBD, Redis...) et {{$ratio_transport}} % en transport tiers (ftp, http, filesystem ...)">
            <div class="bar booked" style="width: {{$ratio+$ratio_transport}}%; text-align: center;"></div>
          </div>
        </td>

        <td
                {{if $_log->_enslaved}}
                  style="background-color: #007e34 !important; color: white;"
                {{/if}}
        ></td>

        <td style="text-align: center;">{{mb_value object=$_log field=server_addr}}</td>

        <td>{{mb_value object=$_log field=_module}}</td>

        <td>{{mb_value object=$_log field=_action}}</td>

        <td>
            {{if $_log->_ref_session && $_log->_ref_session->_id}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_log->_ref_session->_guid}}');">
            {{$_log->session_id|truncate:12}}
          </span>
            {{/if}}
        </td>

        <td>
            {{if $_log->isPublic()}}
              PUBLIC
            {{else}}
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_log->_ref_user}}
            {{/if}}
        </td>
      </tr>
        {{foreachelse}}
      <tr>
        <td class="empty" colspan="11">{{tr}}CLongRequestLog.none{{/tr}}</td>
      </tr>
    {{/foreach}}
</table>
