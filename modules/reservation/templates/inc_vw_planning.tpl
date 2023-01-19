{{*
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=planning ajax=1}}

<script>
  showAlerte = function(date, bloc_id, type, edit) {
    var url = new Url("dPbloc", "vw_alertes");
    url.addParam("date"   , date);
    url.addParam("type"   , type);
    url.addParam("bloc_id", bloc_id);
    url.addParam("edit_mode", edit);
    url.requestModal(800, 500);
    url.modalObject.observe("afterClose", function() {
      refreshPlanning();
    });
  };

  Main.add(function() {
    var height_planning = '{{$height_planning_resa}}';
    ViewPort.SetAvlHeight("planningInterventions", 1.0);
    $('planningWeek').style.height = height_planning+"px";

    // used for hover event
    $$(".body").each(function(elt) {
      //elt.setStyle({backgroundColor: elt.up().getStyle("backgroundColor"), backgroundImage: elt.up().getStyle("backgroundImage")});
    });
  });
</script>

<style type="text/css">
  .planning .event .body{
    line-height: 120% !important;
  }

  /* below events, force up */
  .event-container div.now {
    z-index: 50!important;
  }

  .planning td {
    border:solid 1px #bbb!important;
  }

  .planning div.minute-30 {
    border-top:solid 1px #ccc!important;
  }

  .plage_planning {
    position:relative;
    z-index: 0 !important;
  }

  div.hover_chir {
    display: none;
    position: absolute;
    top:0;
    white-space: nowrap;
    background-color: white;
    font-size: 1.4em;
  }

  .plage_planning:hover div.hover_chir {
    display: block;
    left:-.1em;

    transform-origin: 100% 100%;
    -webkit-transform-origin: 100% 100%;
    -moz-transform-origin: 100% 100%;
    -ms-transform-origin: 100% 100%;

    transform: rotate(-90deg);
    -webkit-transform: translate(-100%, 0) rotate(-90deg) ;
    -moz-transform: translate(-100%, 0) rotate(-90deg);
    -ms-transform: translate(-100%, 0) rotate(-90deg);
  }

</style>

  {{if $can->edit && ($nbIntervNonPlacees || $nbIntervHorsPlage || $nbAlertesInterv)}}
  <div class="warning" style="margin:0 auto">
    <a href="#nothing" onclick="showAlerte('{{$date_planning}}', '{{$bloc_id}}', 'day', 1)">
      {{if $nbAlertesInterv}}
        {{$nbAlertesInterv}} alerte(s) sur des interventions<br/>
      {{/if}}
      {{if $nbIntervNonPlacees}}
        {{$nbIntervNonPlacees}} intervention(s) non validée(s)<br/>
      {{/if}}
      {{if $nbIntervHorsPlage}}
        {{$nbIntervHorsPlage}} intervention(s) hors plage
      {{/if}}
    </a>
  </div>
{{/if}}
<div id="planningInterventions">
   {{mb_include module=system template=calendars/vw_week}}
</div>

<form name="updateIntervHorsPlage" method="post" style="display: none;">
  <input type="hidden" name="m" value="dPplanningOp" />
  <input type="hidden" name="dosql" value="do_planning_aed" />
  <input type="hidden" name="date"/>
  <input type="hidden" name="operation_id" />
  <input type="hidden" name="delay"/>
  <input type="hidden" name="hour_operation">
  <input type="hidden" name="duration">
  <input type="hidden" name="salle_id"/>
</form>

<script>
  Main.add(function() {
    var planning = window["planning-{{$planning->guid}}"];
    planning.salles_ids = {{$salles_ids|@json}};

    planning.onMenuClick = function(event, object_id, elem) {

      window.cut_operation_id = null;
      window.copy_operation_id = null;

      switch (event) {
        case 'list':
          planningInter(object_id);
          break;

        case 'cancel':
          if (elem.up().up().hasClassName("commentaire_planning")) {
            deleteCommentaire(object_id);
          }
          break;


        case 'edit':
          // Commentaire
          if (elem.up().up().hasClassName("commentaire_planning")) {
            modifCommentaire(null, null, null, object_id, false);
          }

          else if (elem.up().up().hasClassName("plage_planning")) {
            modifPlage(object_id, '{{$date_planning}}');
          }
          // DHE
          else {
            if (elem.up().up().hasClassName("operation_enplage")) {
              modifIntervention('', '', '', object_id, true);
            }
            else if (elem.up().up().hasClassName("operation_horsplage")) {
              modifIntervention('', '', '', object_id, false);
            }
            else {
              modifIntervention('', '', '', '', false);
            }
          }
          break;
        case 'cut':
        case 'copy':

          //cleaning up
          window.cut_operation_id = null;
          window.copy_operation_id = null;
          window.copy_commentaire_id = null;

          //opacity
          if (window.save_elem && window.save_elem != elem) {
            window.save_elem.removeClassName("opacity-50");
          }
          if (elem.hasClassName("opacity-50")) {
            elem.removeClassName("opacity-50");
            window.save_elem = null;
          }
          else {
            elem.addClassName("opacity-50");
          }

          //commentaire
          if (elem.up().up().hasClassName("commentaire_planning")) {
            window.copy_commentaire_id = object_id;
            var com_infos = elem.up().up().down("div.body").down("span").down("span");
            window.copy_commentaire_libelle     = com_infos.get("libelle");
            window.copy_commentaire_commentaire = com_infos.get("commentaire");
            window.save_duree                   = com_infos.get("duree");
            window.save_color                   = com_infos.get("color");
            window.save_elem                    = elem;
            updateStatusCut();
            break;
          }
          

          
          // DHE
          if (event == "cut") {
            window.cut_operation_id = object_id;
          }
          else {
            window.copy_operation_id = object_id;
          }
          var span_infos = elem.up('div.toolbar').next('div.body').down('span.data');
          window.save_entree_prevue = span_infos.get("entree_prevue");
          window.save_sortie_prevue = span_infos.get("sortie_prevue");
          window.save_sejour_id     = span_infos.get("sejour_id");
          window.save_chir_id       = span_infos.get("chir_id");
          window.save_duree         = span_infos.get("duree");
          window.save_pec           = span_infos.get("pec");
          window.save_elem = elem;
          updateStatusCut();
          break;
        case 'clock':
          modifSejour(object_id, null, null, null, null, null, "Control.Modal.close");
      }
    };

    //drag&Drop
    planning.onEventChange = function(e) {
      var time = e.getTime();
      var start = time.start;
      var end = time.end;
      var index_salle = start.getFullYear()-2000;
      var salle_id = this.salles_ids[index_salle];

      if (index_salle < 0 || index_salle > this.salles_ids.length) {
        return;
      }


      var object_guid = e.draggable_guid;
      var object = object_guid.split("-");
      var object_class = object[0];
      var object_id = object[1];
      if (object_class == "COperation") {
        var entree_prevue = /entree_prevue='([0-9 \:-]*)'/.exec(e.title)[1];
        var prevue_split = entree_prevue.split(" ");
        var date_entree_prevue = prevue_split[0];
        var heure_entree_prevue = prevue_split[1];
        var sortie_prevue = /sortie_prevue='([0-9 \:-]*)'/.exec(e.title)[1];
        var heure_sortie_prevue = sortie_prevue.split(" ")[1];
      }


      // Pour un commentaire
      if (e.type == "commentaire_planning") {

        var temp_object = $(e.internal_id).down("div.body").down("span").down("span");
        var form = getForm("editCommentairePlanning");
        $V(form.commentaire_planning_id, object_id);
        $V(form.debut, "{{$date_planning}} " + start.format("HH:mm"));
        $V(form.fin, "{{$date_planning}} " + end.format("HH:mm"));
        $V(form.salle_id, salle_id);
        $V(form.commentaire, temp_object.get("commentaire"));
        $V(form.libelle, temp_object.get("libelle"));
        $V(form.color, temp_object.get("color"));
        
        onSubmitFormAjax(form, {onComplete: refreshPlanning});
        return;
      }
      
      // Pour une DHE
      var form = getForm("editOperation");

      var time_operation = start;
      var preop = /preop='([0-9 \:-]*)'/.exec(e.title)[1];
      var preop_segmented = preop.split(":");
      var postop = /postop='([0-9 \:-]*)'/.exec(e.title)[1];
      var postop_segmented = postop.split(":");

      time_operation.addHours(preop_segmented[0]);
      time_operation.addMinutes(preop_segmented[1]);

      end.addHours(-postop_segmented[0]);
      end.addMinutes(-postop_segmented[1]);

      var temp_operation = (end - start) / 60000;
      var hour = parseInt(temp_operation / 60);
      var min = temp_operation - 60 * hour;
      var temp_operation = strpad(hour) + ":"+strpad(min);

      time_operation = time_operation.format("HH:mm");

      // Popup de modification des dates d'entrée et sortie prévue du séjour
      // dans le cas où la date et heure d'intervention n'est pas dans cet intervalle
      
      if ("{{$date_planning}} "+time_operation < entree_prevue) {
        modifSejour(object_id, "{{$date_planning}} "+time_operation, null, null, null, "afterModifSejour");
        
        window.save_operation =
          {"operation_id": object_id,
           "time_operation": time_operation,
           "temp_operation": temp_operation,
           "salle_id": salle_id};
        return;
      }

      var initial_hour = e.start.substr(e.start.indexOf(' ') + 1);
      var initial_start = new Date(time.start);
      initial_start.setHours(parseInt(initial_hour.substr(0, 2)));
      initial_start.setMinutes(parseInt(initial_hour.substr(3, 2)));

      // Sinon, on peut enregistrer
      $V(form.operation_id,   object_id);
      $V(form.time_operation, time_operation);
      $V(form.temp_operation, temp_operation);
      $V(form.salle_id,       salle_id);
      
      onSubmitFormAjax(form, {onComplete: delayOperations.curry(object_id, (start - initial_start) / 60000)});
    };
    
    var planning_div = $("{{$planning->guid}}");

    
    {{if $can->edit}}
      // Création d'une interv sur une case à une heure donnée
      planning_div.select("div.minutes").each(function(elt) {

        elt.observe('dblclick', function() {
          var classes = elt.className.split("  ");
          var hour = $(elt).get("hour");
          var minutes = $(elt).get("minutes");
          if (minutes < 10 && minutes.length < 2) {
            minutes = "0"+minutes;
          }
          var time = hour+":"+minutes+":00";
          var salle_id = planning.salles_ids[classes[0].split("-")[1]];

          // Mode commentaire
          var form = getForm("filterPlanning");
          
          if (form._comment_mode.checked && !window.copy_commentaire_id) {
            modifCommentaire("{{$date_planning}}", hour, salle_id, null);
            return;
          }
          
          // Mode DHE

          // - copier coller commentaire
          if (window.copy_commentaire_id) {
            var hour_debut = Date.fromTIME(time);
            var time_fin = hour_debut.addMinutes(window.save_duree).toTIME();
            pasteCommentaire("{{$date_planning}}", salle_id, time, time_fin, window.save_color, "");
            return;
          }
          
          // - Couper coller interv
          if (window.cut_operation_id) {
            pasteIntervention(window.cut_operation_id, salle_id, time);
            return;
          }
          
          // - Copier coller interv
          if (window.copy_operation_id) {
            pasteIntervention(window.copy_operation_id, salle_id, time, window.save_sejour_id, window.save_duree);
            return;
          }
          
          // - Création
          modifIntervention("{{$date_planning}}", hour, salle_id, null, null, null, minutes);
        });
      });
    {{/if}}


    //drag & drop label
    $$("label.droppable").each(function(li) {
      Droppables.add(li, {
        onDrop: function(from, to, event) {
          Event.stop(event);
          var fromSalle = from.get("salle_id");
          var toSalle   = to.get("salle_id");
          if (fromSalle && toSalle) {
            Operation.switchOperationsFromSalles(fromSalle, toSalle, "{{$date_planning}}", refreshPlanning);
          }
        },
        accept: 'draggable',
        hoverclass:'dropover'
      });
    });

    $$("label.draggable").each(function(a) {
      new Draggable(a, {
        onEnd: function(element, event) {
          Event.stop(event);
        },
        ghosting: true});
    });
  });
</script>