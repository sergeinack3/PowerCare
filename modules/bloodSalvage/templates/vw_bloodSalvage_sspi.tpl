{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var="module" value="bloodSalvage"}}
{{assign var="object" value=$blood_salvage}}
{{mb_script module="bloodSalvage" script="bloodSalvage"}}

<script type="text/javascript">
  Main.add(function () {
    var url = new Url("bloodSalvage", "httpreq_liste_patients_bs");
    url.addParam("date", "{{$date}}");
    url.periodicalUpdate('listRSPO', {frequency: 90});
    {{if $selOp->_id}}
    url.setModuleAction("bloodSalvage", "httpreq_vw_sspi_bs");
    url.addParam("date", "{{$date}}");
    url.requestUpdate("bloodSalvageSSPI");
    {{/if}}
  });
</script>

<table class="main">
  <tr>
    <td class="halfPane" id="listRSPO"></td>
    <td class="halfPane">
      {{if $selOp->_id}}
        <div id="bloodSalvageSSPI"></div>
      {{else}}
        <div class="small-info">{{tr}}msg-CBloodSalvage.select_patient{{/tr}}</div>
      {{/if}}
    </td>
  </tr>
</table>
