{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=salleOp script=salleOp}}
{{mb_script module=bloc    script=edit_planning}}
{{mb_script module=system  script=alert}}

{{if "offlineMode"|module_active && "offlineMode general active"|gconf}}
  {{mb_include module=offlineMode template=inc_offline_notifier worklist=perop}}
{{/if}}

<script>
  Main.add(function() {
    SalleOp.date = '{{$date}}';
    SalleOp.salle_id = '{{$salle}}';

    {{if "dPsalleOp COperation mode"|gconf || ($currUser->_is_praticien && !$currUser->_is_anesth)}}
      SalleOp.plages_by_prat = true;
    {{/if}}

    SalleOp.periodicalUpdateListePlages('{{$hide_finished}}');
    SalleOp.loadOperation('{{$operation_id}}', null, null, '{{$fragment}}');
  });
</script>

<table class="main">
  <tr>
    <td style="width: 220px;" id="listplages"></td>
    <td id="operation_area">&nbsp;</td>
  </tr>
</table>