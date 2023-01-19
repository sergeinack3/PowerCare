{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination total=$list_count current=$start step=100 change_page='changePage' jumper=1}}

<table class="tbl">
  <tr>
    <th>{{mb_title class=CUserLog field=object_class}}</th>
    <th>{{mb_title class=CUserLog field=object_id}}</th>
    <th>{{mb_title class=CUserLog field=ip_address}}</th>
    <th>{{mb_title class=CUserLog field=user_id}}</th>
    <th colspan="2">{{mb_title class=CUserLog field=date}}</th>
    {{if $app->user_prefs.displayUTCDate}}
      <th>{{tr}}common-UTC Date{{/tr}}</th>
    {{/if}}
    <th>{{mb_title class=CUserLog field=type}}</th>
  </tr>

  {{mb_include module=system template=inc_history_line logs=$list}}
</table>
