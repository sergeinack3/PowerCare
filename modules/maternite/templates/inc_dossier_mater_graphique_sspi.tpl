{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=monitoringPatient script=surveillance_perop         ajax=1}}
{{mb_script module=monitoringPatient script=surveillance_timeline      ajax=1}}
{{mb_script module=monitoringPatient script=surveillance_timeline_item ajax=1}}
{{mb_script module=monitoringPatient script=supervision_graph_defaults ajax=1}}
{{mb_script module=prescription      script=prescription               ajax=1}}

{{unique_id var=surv_uid}}

<script>
  reloadSurveillance = {};
  reloadSurveillance.sspi = function () {
    var container = $("surveillance_sspi-{{$surv_uid}}");
    if (container) {
      new Url("salleOp", "ajax_vw_surveillance_perop")
        .addParam("operation_id", '{{$operation_id}}')
        .addParam("type", "sspi")
        .addParam("force", "1")
        .addParam("isDossierPerinatal", '{{$isDossierPerinatal}}')
        .requestUpdate(container);
    }
  };

  Main.add(reloadSurveillance.sspi);
</script>

<div id="surveillance_sspi-{{$surv_uid}}"></div>