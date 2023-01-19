{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var="module" value="bloodSalvage"}}
{{assign var="object" value=$blood_salvage}}

{{mb_script module=bloodSalvage script=bloodSalvage}}
{{mb_script module=bloc         script=edit_planning}}

<script type="text/javascript">

  Main.add(function () {
    var url = new Url("bloodSalvage", "httpreq_liste_plages");
    url.addParam("date", "{{$date}}");
    url.addParam("operation_id", "{{$selOp->_id}}");
    url.periodicalUpdate('listplages', {frequency: 90});
    {{if $selOp->_id}}
    // Effet sur le programme
    new PairEffect("listplages", {sEffect: "appear", bStartVisible: true});
    url.setModuleAction("bloodSalvage", "httpreq_vw_bloodSalvage");
    url.requestUpdate('bloodSalvage');
    {{/if}}
  });
</script>

<table class="main">
  <tr>
    <td class="halfPane" id="listplages"></td>
    <td class="halfPane">
      {{if $selOp->_id}}
        {{mb_include template=inc_bloodSalvage_header}}
        <div id="bloodSalvage"></div>
      {{else}}
        <div class="small-info me-margin-top-7">
          {{tr}}msg-CBloodSalvage.select_interv{{/tr}}
        </div>
      {{/if}}
    </td>
  </tr>
</table>
