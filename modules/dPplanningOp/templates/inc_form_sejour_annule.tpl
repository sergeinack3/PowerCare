{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="tooltip_annulation_sejour" style="display:none;width:500px;">
  <div class="small-warning">
    {{tr}}CSejour-confirm-cancel{{/tr}}
  </div>
  <table class="form tbl" style="width:498px;">
    <tr>
      <td style="text-align: right"><strong>{{mb_title object=$sejour field=motif_annulation}}</strong></td>
      <td>{{mb_field object=$sejour field=motif_annulation typeEnum="radio" separator="<br/>"}}</td>
    </tr>
    <tr >
      <td style="text-align: right"><strong>{{mb_label object=$sejour field=rques_annulation}}</strong></td>
      <td>{{mb_field object=$sejour field=rques_annulation form="editSejour"}}</td>
    </tr>
    <tr>
      <td colspan="4" class="button">
        <button type="button" class="tick me-primary"   onclick="confirmCancelSejour();">{{tr}}Validate{{/tr}}</button>
        <button type="button" class="cancel me-tertiary me-dark" onclick="resetAnnulationSejour();Control.Modal.close();">
          {{tr}}Close{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</div>
