{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=calendars/vw_week}}

<script>
  ObjectTooltip.modes.patient_view = {
    module: "ssr",
    action: "ajax_vw_custom_tooltip_patient",
    sClass: "tooltip"
  };

Main.add(function() {
  var planning = window["planning-{{$planning->guid}}"];
  planning.onEventChange = function(e){
    var form = getForm("form-edit-planning-{{$planning->guid}}");
    var time = e.getTime();
    $V(form.evenement_ssr_id, e.draggable_guid.split('-')[1]);
    $V(form.debut, time.start.toDATETIME(true));
    $V(form.duree, time.length);
    onSubmitFormAjax(form, {onComplete: function(){
      Planification.refreshSejour("{{$planning->guid}}".split("-")[2], true);
      PlanningTechnicien.show();
      PlanningEquipement.show();
    }});
  }
});
</script>

<form name="form-edit-planning-{{$planning->guid}}" method="post" action="">
  <input type="hidden" name="@class" value="CEvenementSSR" />
  <input type="hidden" name="evenement_ssr_id" value="" />
  <input type="hidden" name="debut" value="" />
  <input type="hidden" name="duree" value="" />
</form>
