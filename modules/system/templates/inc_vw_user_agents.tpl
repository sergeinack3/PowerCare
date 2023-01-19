{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=useragent ajax=true}}

{{mb_include module=system template=inc_vw_graph_user_agents}}
{{mb_include module=system template=inc_pagination change_page="UserAgent.changePage" total=$total current=$start step=50}}

<table class="main tbl">
  <tr>
    <th class="narrow"></th>
    <th class="narrow">{{mb_title class=CUserAgent field=browser_name}}</th>
    <th class="narrow">{{mb_title class=CUserAgent field=browser_version}}</th>

    <th class="narrow">{{mb_title class=CUserAgent field=platform_name}}</th>
    <th class="narrow">{{mb_title class=CUserAgent field=platform_version}}</th>

    <th class="narrow">{{mb_title class=CUserAgent field=device_name}}</th>
    <th class="narrow">{{mb_title class=CUserAgent field=device_maker}}</th>
    <th class="narrow">{{mb_title class=CUserAgent field=device_type}}</th>
    <th class="narrow">{{mb_title class=CUserAgent field=pointing_method}}</th>
    <th class="narrow">{{tr}}CUserAgent-back-user_authentications{{/tr}}</th>
    <th>{{mb_title class=CUserAgent field=user_agent_string}}</th>
  </tr>

  {{foreach from=$user_agents item=_user_agent}}
    <tr id="user_agent_{{$_user_agent->_id}}">
      {{mb_include module=system template=inc_vw_user_agents_line}}
    </tr>
  {{/foreach}}
</table>
