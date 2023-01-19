/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

var element_select;
var zone_select;
var conf_resa;

Main.add(function() {
  //Tous les éléments draggables
  $$('div.patient.draggable').each(function (e) {
    new Draggable(e, {
      revert:   true,
      scroll:   window,
      ghosting: true
    });
  });

  // Toutes les zones droppables pour les chambres/lits
  $$('td.chambre').each(function (e) {
    Droppables.add(e, {onDrop: TraiterDrop});
  });

  // Toutes les zones droppables pour les salles de bloc
  $$('td.salle').each(function (e) {
    Droppables.add(e, {onDrop: ChoiceSalle.TraiterDropSalle});
  });
});

legendPlacement = function() {
  new Url("urgences", "vw_legende_placement").requestModal();
};

TraiterDrop = function(element, zoneDrop) {
  if (element.hasClassName("lit_bloque_urgences") && !zoneDrop.hasClassName("blockedBedroom")) {
      if (zoneDrop.querySelector('.patient')) {
        alert($T('CRPU-msg-Warning bed-occupied'));
        return;
      }

      new Url("dPhospi", "ajax_edit_affectation")
        .addParam("urgence", 1)
        .addParam("mod_urgence", 1)
        .addParam("lit_id", zoneDrop.dataset.litId)
        .requestModal(900, null, {
          showReload: false,
          onClose: () => Rafraichissement.init()
        });
  }

  if (zoneDrop.hasClassName("blockedBedroom")) {
    alert($T('CRPU-msg-Warning this bed is blocked Please remove this assignment to be able to move the patient'));
    return;
  }

  if (!element.hasClassName("patient")) {
    return;
  }

  if(zoneDrop.get("chambre-id") != element.parentNode.get("chambre-id")) {
    element_select = element;
    zone_select = zoneDrop;
    var nb_chambres_libres = parseInt(zoneDrop.getAttribute("data-nb-lits")) - parseInt(zoneDrop.select('div.patient').length);

    ChoiceLit.service_id = zoneDrop.get("service-id");

    if(nb_chambres_libres >= 2 || conf_resa == '1'){
      element.style.width = "92%";
      var form = getForm("changeDate");
      var date = form ? $V(getForm("changeDate").date) : null;
      ChoiceLit.edit(zoneDrop.get("chambre-id"), element.get("patient-id"), date, element.get("rpu-id"));
    }
    else{
      var callback = Prototype.emptyFunction;
      element.style.width = "92%";

      if (ChoiceLit.isMater) {
        callback = function () {
          Placement.refreshNonPlaces();
          setTimeout(Placement.refreshPlacement.curry('CService-' + ChoiceLit.service_id), 1000);
        }
      }

      ChoiceLit.submitLit(zoneDrop.get("lit-id"), zoneDrop.get("service-id"), callback);
    }
  }  
};

ChoiceLit  = {
  modal: null,
  vue_hospi: 0,
  field_box_id: "box_id",
  isMater: 0,
  edit: function(chambre_id, patient_id, date, rpu_id) {
    var url = new Url("hospi", "ajax_choice_lit")
      .addParam("rpu_id"    , rpu_id)
      .addParam("chambre_id", chambre_id)
      .addParam("patient_id", patient_id)
      .addParam("vue_hospi", ChoiceLit.vue_hospi)
      .addParam("is_mater", ChoiceLit.isMater)
      .addNotNullParam("date", date)
      .requestModal();
    this.modal = url.modalObject;
  },

  finish: function(lit_id) {
    var service_id = ChoiceLit.isMater ? ChoiceLit.service_id : null;
    var callback = ChoiceLit.submitReservation;

    if (ChoiceLit.isMater) {
      callback = function () {
        Control.Modal.close();
        Placement.refreshNonPlaces();
        setTimeout(Placement.refreshPlacement.curry('CService-' + ChoiceLit.service_id), 1000);
      }
    }

    return ChoiceLit.submitLit(lit_id, service_id, callback);
  },

  submitLit: function(lit_id, service_id, callback) {
    callback = callback || Prototype.emptyFunction;
    zone_select.appendChild(element_select);
    var guid = element_select.get("form_name");
    var form = getForm(guid);

    if (!form && !guid) {
      form = getForm(element_select.get("affectation-guid"));
      $V(form.lit_id, lit_id);
    }

    $V(form.elements[ChoiceLit.field_box_id], lit_id.trim());
    if (service_id) {
      $V(form._service_id, service_id);
    }
    return onSubmitFormAjax(form, callback);
  },

  submitReservation: function() {
    zone_select.appendChild(element_select);
    var rpu_guid = element_select.get("form_name");
    var form = getForm('CRPUReservationBox_'+rpu_guid);
    if (!form || !$V(form.lit_id)) {
      Control.Modal.close();

      Placement.refreshPlacement('CService-' + ChoiceLit.service_id);

      return false;
    }

    if ($V(form.lit_id)) {
      return onSubmitFormAjax(form, Rafraichissement.init);
    }
  },

  addReservation: function(lit_id, del) {
    zone_select.appendChild(element_select);
    var rpu_guid = element_select.get("form_name");
    var form = getForm('CRPUReservationBox_'+rpu_guid);
    $V(form.lit_id, lit_id);
    if (del) {
      $V(form.del, 1);
    }
  },

  retourBox: function(lit_id) {
    var form = getForm("Choice_lit");
    $V(form.lit_id, lit_id);
    ChoiceLit.addReservation(lit_id, true);
    ChoiceLit.finish($V(form.lit_id), 1);
  },
  /**
   * Move to another service or blok
   *
   * @param sejour_id
   * @param location
   */
  moveServiceOrBlock: function(sejour_id, location) {
    var url = new Url("maternite", "ajax_vw_choice_service_bloc")
      .addParam("sejour_id", sejour_id)
      .addParam("location", location)
      .requestModal();
  },
};

ChoiceSalle  = {
  modal: null,
  bloc_id: 0,
  /**
   * Send the form
   *
   * @param form
   * @param salle_id
   * @param callback
   * @returns {Boolean}
   */
  submitSalle: function(form, salle_id, callback) {
    callback = callback || Prototype.emptyFunction;
    zone_select.appendChild(element_select);
    var form_name = form ? form.name : element_select.get("form_name");

    if (!form_name) {
      form_name =  zone_select.get("form_name");
    }

    var form = getForm(form_name);

    if (element_select.get('last_operation_id')) {
      $V(form.operation_id, element_select.get('last_operation_id'));
    }

    $V(form.salle_id, salle_id.trim());

    return onSubmitFormAjax(form, callback);
  },
  /**
   * Traitment for the drag and drop
   *
   * @param element
   * @param zoneDrop
   * @constructor
   */
  TraiterDropSalle: function(element, zoneDrop) {
    if (!element.hasClassName("patient")) {
      return;
    }

    ChoiceSalle.bloc_id = zoneDrop.get("bloc_id");

    var last_operation_id = element.get("last_operation_id");
    var callback = function () {
      Control.Modal.Close();
      Placement.refreshNonPlaces();
      Placement.refreshPlacement('CBlocOperatoire-' + ChoiceSalle.bloc_id);
    }

    if (!last_operation_id) {
      var patient_id = element.get("patient-id");
      var sejour_id = element.get("affectation-guid").split('-')[1];

      Tdb.editAccouchement(null, sejour_id, null, patient_id, callback, zoneDrop.get("salle_id"));
    }
    else {
      if(zoneDrop.get("salle_id") != element.parentNode.get("salle_id")){
        element_select = element;
        zone_select = zoneDrop;

        element.style.width = "92%";
        ChoiceSalle.submitSalle(null, zoneDrop.get("salle_id"), callback);
      }
    }
  }
};
