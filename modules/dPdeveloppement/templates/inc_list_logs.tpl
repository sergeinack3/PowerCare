{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPdeveloppement script=error_logs ajax=true}}

{{if $nb_logs > 0 }}
  <div class="divInfosLog">{{$nb_logs}} logs (obtenus en {{$exec_time}} ms)</div>
  <table class="table_log">
    {{foreach from=$logs item=_log}}
      <tr class="tr_log" style="color:{{$_log.color}}" onclick="ErrorLogs.jsonViewer('{{$_log.infos}}')">
        <td style="white-space: nowrap;">{{$_log.date|html_entity_decode}}</td>
        <td>{{$_log.level|html_entity_decode}}</td>
        <td style="width:100%;">{{$_log.message|html_entity_decode}}</td>
        <td>{{$_log.context|html_entity_decode}}</td>
        <td>{{$_log.extra|html_entity_decode}}</td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}

{{if $nb_logs == 1000 }}
  <div class="divShowMoreLog" onclick="ErrorLogs.showMoreLog(this)"><i class="fas fa-arrow-circle-down"></i> Afficher plus de logs ...</div>
{{else}}
  <div class="divInfosLog">Tous les logs ont été chargés</div>
{{/if}}
