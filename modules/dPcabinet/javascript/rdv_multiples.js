/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

RDVmultiples = {
  is_multiple     : 0,
  slots           : [],
  current_rank    : 0,
  automatic_rank  : 1,
  max_rank        : null,

  init: function(max_rank, consultation_ids, multiple) {
    this.current_rank = RDVmultiples.current_rank;
    RDVmultiples.max_rank = max_rank;

    // selected
    RDVmultiples.is_multiple = (multiple == 1);
    if (consultation_ids.length > 0) {
      for (var b=0; b<consultation_ids.length; b++) {
        var plage_id            = consultation_ids[b][0];
        var consult_id          = consultation_ids[b][1];
        var date                = consultation_ids[b][2];
        var time                = consultation_ids[b][3];
        var chir_id             = consultation_ids[b][4];
        var chir_view           = consultation_ids[b][5];
        var annule              = consultation_ids[b][6];
        var rques               = consultation_ids[b][7];
        var el_prescrip_id      = consultation_ids[b][8];
        var el_prescrip_libelle = consultation_ids[b][9];

        RDVmultiples.addSlot(RDVmultiples.current_rank, plage_id, consult_id, date, time, chir_id, chir_view, annule, rques, el_prescrip_id, el_prescrip_libelle);        // insert
        RDVmultiples.loadPlageConsult(plage_id, consult_id, RDVmultiples.is_multiple, time);  // display
        if (this.is_multiple) {
          RDVmultiples.selRank(RDVmultiples.current_rank+1);
        }
      }
    }
    else {
      if (this.is_multiple) {
        $('tools_plage_0').addUniqueClassName('selected');
      }
      //show the default page
      var aSelected = $$("tr.selected");
      if (aSelected.length === 1) {
        aSelected[0].select("a").invoke('onclick');
      }
    }
  },

  // add a slot to the list.
  addSlot : function(slot_number, plage_id, consult_id, date, time, chir_id, _chir_view, toTrash, rques, el_prescrip_id, el_prescrip_libelle) {
    var oldslot = this.slots[slot_number];

    // if consult_id, We keep it
    if (oldslot && oldslot.consult_id && consult_id != oldslot.consult_id) {
      consult_id = oldslot.consult_id;
    }

    // if consult && is_cancelled, we keep the status
    if (oldslot && oldslot.is_cancelled == 1 && !toTrash) {
      toTrash = 1;
    }
    RDVmultiples.slots[slot_number] = new consultationRdV(plage_id, consult_id, date, time, chir_id, _chir_view, toTrash, rques, el_prescrip_id, el_prescrip_libelle);

    // creation                           plage_id && !consult_id
    // modif de consultation              !plage_id && consult_id
    // modifier la plage de la consult    plage_id && consult_id
    // simple modif                       !plage_id && !consult_id
  },

  resetSlots : function() {
    for (var a = 0; a<this.max_rank; a++ ) {
      RDVmultiples.removeSlot(a, 1);
    }
  },

  //enlever un slot (ne doit pas avoir de consult_id)
  removeSlot : function(rank, reset) {
    var _reset = reset ? 1 : 0;
    var slot = this.slots[rank];

    // si consult_id => annulation du rendez-vous
    if (slot && slot.consult_id) {
      if (!_reset) {
        if (slot.is_cancelled == 1) {
          slot.is_cancelled = 0;
          $('cancel_plage_'+rank).hide();
          $('discancel_plage_'+rank).show();
        }
        else {
          slot.is_cancelled = 1;
          $('cancel_plage_'+rank).show();
          $('discancel_plage_'+rank).hide();
        }
      }
    }
    // sinon on le supprime + refresh
    else {
      delete this.slots[rank];
      $("listPlaces-"+rank).update("");
    }
  },

  selRank: function(rank) {
    if (rank <= RDVmultiples.max_rank) {
      RDVmultiples.current_rank = rank;
      if (this.is_multiple) {
        $$('.tools_plage').each(
          function(elt) {
            $(elt).removeClassName('selected');
          }
        );

        var target = $('tools_plage_'+rank);
        if ($(target)) {
            $(target).addUniqueClassName('selected');
        }
      }
    }
  },

  cleanRank: function(rank) {
    RDVmultiples.selRank(rank);
    $('plistPlaces-'+rank).update('');
  },

  refreshSlot : function(rank, plage_id, consult_id, multiple) {
    this.selRank(rank);
    this.loadPlageConsult(plage_id, consult_id, multiple);
  },

  // Selects the right shift
  loadPlageConsultSlot : function(element, plageconsult_id, consult_id, multiple, heure) {
    while (element && (!element.classList || !element.classList.contains('plage_rank'))) {
      element = element.parentNode;
    }

    if (element && element.nodeName) {
      RDVmultiples.selRank(element.dataset.slot_number);
    }

    RDVmultiples.loadPlageConsult(plageconsult_id, consult_id, multiple, heure);
  },

  // load the plageconsult to the right
  loadPlageConsult : function(plageconsult_id, consult_id, multiple, heure) {
    // load plage
    var url = new Url("dPcabinet", "httpreq_list_places");
    url.addParam("plageconsult_id", plageconsult_id);
    url.addNotNullParam("heure", heure);
    url.addParam("consult_id", consult_id);
    url.addParam("multiple", (multiple && multiple !== "0") ? 1 : 0);
    url.addParam("slot_id", this.current_rank);
    url.requestUpdate("listPlaces-"+this.current_rank);
  },

  updateSelections : function(plage_id, multiple) {
    var list_of_plages_left = [];
    $('listePlages').select('table.tbl tr.plage').each(
      function(elt) {
        elt.removeClassName('selected');
        list_of_plages_left.push((elt.id).split('-')[1]);
      }
    );

    if (!multiple) {
      var line = $('plage-'+plage_id);
      line.addUniqueClassName("selected");
    }
    // multiple mode
    else {
      var plages_displayed = $('plage_list_container').select('table');
      var ids = [];
      plages_displayed.each(
        function(elt) {
          ids.push(elt.id.split('_')[1]);
        }
      );

      $(ids).each(
        function(elt) {
          for(var a = 0; a < list_of_plages_left.length; a++) {
            if (list_of_plages_left[a] == elt) {
              var line = $('plage-'+elt);
              line.addClassName("selected");
            }
          }
        }
      );
    }
  },

  sendData: function () {
    if (this.slots.size()) {
      window.parent.PlageConsultSelector.consultations = this.slots;
      window.parent.PlageConsultSelector.updateFromSelector();
    }
    else {
      alert("Selectionner au moins une plage");
    }

    var form_filter = getForm("Filter");
    var form_consult = window.parent.getForm("editFrm");

    if (form_filter.nb_semaines && form_consult.nb_semaines) {
      $V(form_consult.nb_semaines, $V(form_filter.nb_semaines));
    }
  },

  switchWeeklytoMonthly: function () {
    $$('select[name=repeat_type]')[0].observe('click', function (e) {
      $$('.repeat-type-txt')[0].innerHTML = (e.target.value === 'week') ? $T('weeks') : $T('months');
    });
  }
};