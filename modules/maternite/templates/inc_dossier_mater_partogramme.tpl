{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dossier value=$grossesse->_ref_dossier_perinat}}
{{assign var=patient value=$grossesse->_ref_parturiente}}

{{mb_script module=monitoringPatient script=surveillance_perop         ajax=1}}
{{mb_script module=monitoringPatient script=surveillance_timeline      ajax=1}}
{{mb_script module=monitoringPatient script=surveillance_timeline_item ajax=1}}
{{mb_script module=monitoringPatient script=supervision_graph_defaults ajax=1}}

{{unique_id var=surv_uid}}

<script>
  reloadSurveillance = {};
  reloadSurveillance.perop = function () {
    var container = $("surveillance_partogramme-{{$surv_uid}}");
    if (container) {
      container.update("");

      new Url("salleOp", "ajax_vw_surveillance_perop")
        .addParam("operation_id", "{{$operation_id}}")
        .addParam("type", "partogramme")
        .addParam("print", "{{$print}}")
        .addParam("isDossierPerinatal", 1)
        .requestUpdate(container);
    }
  };

  refreshFicheAnesth = function () {
    new Url("cabinet", "print_fiche")
      .addParam("operation_id", "{{$operation_id}}")
      .addParam("offline", 0)
      .addParam("display", 1)
      .addParam("pdf", 0)
      .requestModal();
  };

  submitAnesth = function (oForm) {
    onSubmitFormAjax(oForm, function () {
      reloadAnesth($V(oForm.operation_id));
    });
  };

  reloadAnesth = function (operation_id) {
    new Url("salleOp", "httpreq_vw_anesth")
      .addParam("operation_id", operation_id)
      .requestUpdate("perop-anesth");
  };

  Main.add(reloadSurveillance.perop);
</script>

{{mb_include module=maternite template=inc_dossier_mater_header with_buttons=0}}

<div id="surveillance_partogramme-{{$surv_uid}}"></div>

{{if $print}}
  <script>
    // Status envoyé pour wkhtmltopdf lors de l'impression
    setTimeout(function () {
      window.status = "partogramme_completed";
    }, 2000);
  </script>
{{/if}}
