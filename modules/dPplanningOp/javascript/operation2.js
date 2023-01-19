/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Operation2 = Class.create({
  forms: {
    edit: null,
    summary: null
  },

  ccam_tokenfield: null,
  tabs: null,
  edit: 0,
  modified: 0,

  /**
   * Set the forms, create the Control tabs and set the autocomplete field
   */
  initialize: function(edit) {
    if (edit) {
      this.edit = 1;
    }
    this.forms.edit    = getForm('operationEdit');
    this.forms.summary = getForm('operationSummary');

    this.tabs = Control.Tabs.create('operation-edit-tabs', false, {foldable: true, afterChange: function(elt) { DHE.triggerTab(elt); }});

    this.initSelectors();

    this.setAutocompletion();

    Calendar.regField(this.forms.edit._time_urgence, null, {
      datePicker:  false,
      timePicker:  true,
      minHours:    parseInt(DHE.configs.operation.hour_urgence_deb),
      maxHours:    parseInt(DHE.configs.operation.hour_urgence_fin),
      minInterval: parseInt(DHE.configs.operation.min_intervalle)
    });

    Calendar.regField(this.forms.edit._time_op, null, {
      datePicker:  false,
      timePicker:  true,
      minHours:    parseInt(DHE.configs.operation.duree_deb),
      maxHours:    parseInt(DHE.configs.operation.duree_fin),
      minInterval: parseInt(DHE.configs.operation.min_intervalle)
     });

    var dates = null;
    if (DHE.configs.operation.filter_dates) {
      dates = {
        limit: {
          start: DHE.configs.operation.date_min,
          stop: DHE.configs.operation.date_max
        }
      };
    }

    Calendar.regField(this.forms.edit._date_hors_plage, dates);

    DHE.makePlageInput(this.forms.edit._date_planifiee, PlageOpSelector.init);
  },

  /**
   * Set all the autocomplete
   */
  setAutocompletion: function() {
    /* Chir */
    new Url('mediusers', 'ajax_users_autocomplete')
      .addParam('praticiens', 1)
      .addParam('input_field', '_chir_view')
      .autoComplete(this.forms.summary.elements['_chir_view'], null, {
        minChars: 0,
        method: 'get',
        select: 'view',
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          if ($V(field) == '') {
            $V(field, selected.down('.view').innerHTML);
          }
          $V(field.form.elements['chir_id'], selected.getAttribute('id').split('-')[2]);
        }
      }
    );

    // CCAM actes
    new Url("ccam", "autocompleteCcamCodes")
      .autoComplete(this.forms.edit._codes_ccam, null, {
        minChars: 1,
        dropdown: true,
        width: "250px",
        callback: function(input, queryString) {
          var form = getForm('editOp');
          var formSejour = getForm('editSejour');
          return queryString + '&user_id=' + $V(DHE.operation.forms.edit.elements['chir_id']) + '&patient_id=' + $V(DHE.sejour.forms.edit.elements['patient_id']);
        },
        updateElement: (function(selected) {
          this.ccam_tokenfield.add(selected.down("strong").getText().trim(), true);
        }).bind(this)
      }
    );

    // In summary
    /* Chir */
    new Url('mediusers', 'ajax_users_autocomplete')
      .addParam('praticiens', 1)
      .addParam('input_field', '_chir_view')
      .autoComplete(this.forms.edit.elements['_chir_view'], null, {
          minChars: 0,
          method: 'get',
          select: 'view',
          dropdown: true,
          afterUpdateElement: function(field, selected) {
            if ($V(field) == '') {
              $V(field, selected.down('.view').innerHTML);
            }
            $V(field.form.elements['chir_id'], selected.getAttribute('id').split('-')[2]);
          }
        }
      );
  },

  /**
   * Initialize all the selectors
   */
  initSelectors: function() {
    // CCAM acts
    this.ccam_tokenfield = new TokenField(this.forms.edit.codes_ccam, {
      onChange : (this.updateTokenfield).bind(this),
      sProps : "notNull code ccam",
      serialize: true
    } );

    this.updateTokenfield();

    CCAMSelector.init = function() {
      this.sForm  = getForm("operationEdit");
      this.sView  = "_codes_ccam";
      this.sChir  = "chir_id";
      this.sClass = "_class";
      this.pop();
    };

    PlageOpSelector.init = (function() {
      var op_form     = getForm("operationEdit");
      var sejour_form = getForm("sejourEdit");

      this.sPlage_id         = "plageop_id";
      this.sSalle_id         = "salle_id";
      this.sDate             = "date";
      this.sType             = "type";
      this.sPlaceAfterInterv = "_place_after_interv_id";
      this.sHoraireVoulu     = "_horaire_voulu";

      this.s_date_entree_prevue = "entree_prevue";

      this.new_dhe = 1;

      this.options = {
        width: "100%",
        height: "100%"
      };

      this.pop(op_form.chir_id.value, op_form._time_op.value, sejour_form.group_id.value,
        op_form.operation_id.value);
    }).bind(PlageOpSelector);

    // Protocol selector
    ProtocoleSelector.init = function() {
      this.new_dhe = true;
      this.sForm = "operationEdit";
      this.sChir_id = "chir_id";
      this.pop();
    };
  },

  /**
   * Synchronize the value of the given field, in the given view, with the same field of the other view
   *
   * @param {string}            view       The view where the field is located (summary or edit)
   * @param {HTMLInputElement}  field      The field to synchronize
   * @param {HTMLInputElement=} field_view A field who contain the view of the field (like the name for the patient_id). Optional
   */
  syncField: function(view, field, field_view) {
    var form_to_sync = this.forms.edit;

    if (view == 'edit') {
      form_to_sync = this.forms.summary;
    }

    var view_value, view_field_to_sync;
    if (field_view) {
      view_value = $V(field_view);
      view_field_to_sync = form_to_sync.elements[field_view.name];
    }

    DHE.syncField($V(field), form_to_sync.elements[field.name], view_value, view_field_to_sync);
  },

  /**
   * Synchronize the summary with the field
   *
   * @param {HTMLInputElement} field The diagnostic field
   * @param {String=}          text  The text to display in place of the value (for the reference field)
   * @param {String=}          title The title of the display element
   */
  syncView: function(field, text, title) {
    this.setModified();
    var id = 'operation-' + field.name;
    DHE.syncView(field, id, text, title);
  },

  /**
   * Synchronize the summary with the field
   *
   * @param {HTMLInputElement} field  The diagnostic field
   * @param {String=}          title  An optionnal title for the flag
   * @param {Array=}           values If set, if value of the field is in the array, the flag will be displayed
   */
  syncViewFlag: function(field, title, values) {
    this.setModified();
    var id = 'operation-' + field.name;
    DHE.syncViewFlag(field, id, title, values);
  },

  /**
   * Display the edit view in a modal
   *
   * @param {String} tab Optional. The tab to display
   */
  displayEditView: function(tab) {
    Modal.open('operation-edit', {
      title: 'Saisie des données de l\'opération',
      width: '900',
      height: '500'
    });

    if (tab) {
      this.tabs.setActiveTab(tab);
    }
  },

  updateTokenfield: function() {
    var form = this.forms.edit;
    var aCcam = form.codes_ccam.value.split("|").without("");

    var oCcamNode = $("codes_ccam_area");

    var aCodeNodes = [];
    aCcam.each(function(sCode) {
      if (sCode.indexOf('*') != -1) {
        var count = sCode.substring(0, sCode.indexOf('*'));
        var sCode = sCode.substring(sCode.indexOf('*') + 1);

        for (var i = 0; i < count; i++) {
          var sCodeNode = printf("<button class='remove' type='button' onclick='DHE.operation.ccam_tokenfield.remove(\"%s\", true)'>%s<\/button>", sCode, sCode);
          aCodeNodes.push(sCodeNode);
        }
      }
      else {
        var sCodeNode = printf("<button class='remove' type='button' onclick='DHE.operation.ccam_tokenfield.remove(\"%s\", true)'>%s<\/button>", sCode, sCode);
        aCodeNodes.push(sCodeNode);
      }
    });

    oCcamNode.innerHTML = aCodeNodes.join("");

    $V(form._codes_ccam, "");

    this.syncActes();
  },

  syncActes: function() {
    var form = this.forms.edit;

    var actes_area = $("actes_items");

    actes_area.update();

    this.ccam_tokenfield.getValues().each(function(acte_ccam) {
      actes_area.insert(DOM.span({className: "dhe_diagnostic dhe_diag_main"}, acte_ccam))
    });
  },

  togglePlanification: function(area) {
    var other_area = area == "planifiee" ? "hors_plage" : "planifiee";
    $("operation_" + area).toggle();
    $("operation_" + other_area).toggle();

    var form = this.forms.edit;

    form._date_hors_plage.toggleClassName("notNull");
    form._time_urgence.toggleClassName("notNull");

    $V(form.plageop_id, "");
    $V(form.salle_id, "");
    $V(form.date, "");
    $V(form._date_hors_plage, "");
    $V(form._date_hors_plage_da, "");
    $V(form._time_urgence, "");
    $V(form._time_urgence_da, "");

    if (area == "hors_plage") {
      $V(form._date_hors_plage_da, Date.fromDATE(DHE.configs.operation.date_min).toLocaleDate());
      $V(form._date_hors_plage, DHE.configs.operation.date_min);

      if (DHE.configs.operation.time_urgence) {
        $V(form._time_urgence_da, Date.fromTIME(DHE.configs.operation.time_urgence).toLocaleTime());
        $V(form._time_urgence, DHE.configs.operation.time_urgence);
      }
    }
  },

  syncDate : function(elt) {
    var form_op = this.forms.edit;
    var operation_id = $V(form_op.operation_id);
    var type_op = $V(form_op.type_op);

    var date = $V(form_op.date);

    var field_date;

    if (type_op == "planifiee") {
      $V(form_op._date_planifiee, date ? Date.fromDATE(date).toLocaleDate() : "");
      field_date = form_op._date_planifiee;
    }
    else {
      field_date = form_op._date_hors_plage_da;

      if (!operation_id) {
        this.updateEntreePrevue();
      }
    }
    this.syncView(elt, $V(field_date));
  },

  updateEntreePrevue: function() {
    var op_form = this.forms.edit;
    var sejour_form = DHE.sejour.forms.edit;

    /*if(op_form.date.value) {
      if(!oSejourForm._date_entree_prevue.value || !(oSejourForm._date_entree_prevue.value <= oOpForm.date.value && oSejourForm._date_sortie_prevue.value >= oOpForm.date.value)) {
        oSejourForm._date_entree_prevue.value = oOpForm.date.value;
        oView = getForm('editSejour')._date_entree_prevue_da;
        oView.value = Date.fromDATE(oOpForm.date.value).toLocaleDate();
      }
    }*/
  },

  applyProtocol: function(protocol) {
    var form = this.forms.edit;

    var fields = ["codes_ccam", "libelle", "presence_preop", "presence_preop_da",
                  "presence_postop_da", "presence_postop", "duree_bio_nettoyage_da",
                  "duree_bio_nettoyage", "cote", "type_anesth", "_time_op_da", "_time_op",
                  "materiel", "exam_per_op", "examen", "duree_uscpo_da", "duree_uscpo_da",
                  "duree_preop", "duree_preop", "exam_extempo",
                  "depassement", "forfait", "fournitures", "rques"];

    fields.each(function(field) {
      var elt = form.elements[field];
      if (!elt) {
        return;
      }

      var protocol_field = field == "rques" ? "rques_operation" : field;

      $V(elt, protocol[protocol_field], false);
    });

    $V(form.elements['_docitems_guid'], protocol._docitems_guid_operation, true);
  },

  /**
   * Display a cancelled flag on the same level as the legend
   */
  displayCancelFlag: function() {
    var container = $('objects-state');
    var legend = $('fieldset-objects').down('legend');
    var offset = legend.cumulativeOffset();
    var top = offset.top - $('dhe_linked_objects').cumulativeOffset().top;
    var left = offset.left + legend.getWidth() + 20 - $('fieldset-objects').cumulativeOffset().left;
    var cancelled = DOM.span({
      id: 'operation-cancelled',
      class: 'dhe_flag dhe_flag_important',
    }, 'Annulée');
    container.insert(cancelled);
    cancelled.setStyle({position: 'absolute', padding: '2px',fontWeight: 'bold', top: top + 'px', left: left + 'px'});
  },

  /**
   * Set the modified field
   */
  setModified: function() {
    if (this.edit) {
      this.modified = 1;
    }
  }
});

checkChir = function() {
  var form = DHE.operation.forms.edit;
  var field = null;

  if (field = form.chir_id) {
    if (field.value == 0) {
      alert("Chirurgien manquant");
      return false;
    }
  }
  return true;
};

checkDuree = function() {
  var form = DHE.operation.forms.edit;
  var field1 = form._time_op;

  if (field1 && field1.value == "00:00:00") {
    alert("Temps opératoire invalide");
    return false;
  }

  return true;
};
