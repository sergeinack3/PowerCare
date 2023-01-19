{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=planning ajax=$ajax}}

<script>
  Main.add(function() {
    ViewPort.SetAvlHeight("planningInterventions", 1);
    $('planningWeek').setStyle({height : "{{$height_calendar}}px"});

    $("previous_day").setAttribute("data-date", '{{$pday}}');
    $("next_day").setAttribute("data-date", '{{$nday}}');
  });
</script>

<div id="planningInterventions">
  {{mb_include module=system template=calendars/vw_week}}
</div>

<script>
  modalPriseRDV = function(consult_id, date, heure, plage_id) {
    var url = new Url("dPcabinet", "edit_planning");
    url.addParam("dialog", 1);
    url.addParam("consultation_id", consult_id);
    url.addParam("date_planning"  , date);
    url.addParam("heure"          , heure);
    url.addParam("plageconsult_id", plage_id);
    url.modal({width: "100%", height: "100%" , afterClose: refreshPlanning});
  };

  Main.add(function() {
    var planning = window['planning-{{$planning->guid}}'];

    planning.onMenuClick = function(action, plageconsult_id, elt) {
      window.action_in_progress = true;
      var consultation_id = elt.get("consultation_id");

      if (action == "add") {
        var plageSel = elt.up(1);
        var date =  '{{$date}}';
        var hour =  plageSel.down('strong').innerHTML;

        modalPriseRDV(null, date, hour, plageconsult_id)
      }

      if (action == "tick" || action == "tick_cancel") {
        var oform = getForm('chronoPatient');
        $V(oform.consultation_id, consultation_id);
        $V(oform.chrono, action == "tick" ? 32 : 16);
        $V(oform.arrivee,  action == "tick" ? new Date().toDATETIME(true) : '');
        onSubmitFormAjax(oform, {onComplete: refreshPlanning });
        // clean up
        $V(oform.consultation_id, "");
        $V(oform.chrono, 0);
        return false;
      }

      if (action == "cancel") {
        Planning.cancelRdv(consultation_id);
      }

      if (action == "change") {
        Planning.restoreConsult(consultation_id);
      }
    };

    planning.onEventChange = function(e) {
      window.action_in_progress = true;
      if (!window.save_to) {
        refreshPlanning();
        return;
      }
      var time = e.getTime();
      var hour = time.start.toTIME();

      var form = getForm("editConsult");
      var consultation_id = e.draggable_guid.split("-")[1];
      var plageconsult_id = window.save_to.get("plageconsult_id");

      $V(form.consultation_id, consultation_id);
      $V(form.plageconsult_id, plageconsult_id);
      $V(form.heure, hour);
      onSubmitFormAjax(form, {onComplete: refreshPlanning });
      window.save_to = null;
    };

    $$(".droppable").each(function(elt) {
      Droppables.add(elt, {
        onDrop: function(from, to) {
          window.save_to = to;
        }});
    });

    {{if $scroll_top}}
    setTimeout(
      function() {
        $$('.week-container')[0].scrollTop = {{$scroll_top}};
      },
      100
    );
    {{/if}}
  });
</script>
