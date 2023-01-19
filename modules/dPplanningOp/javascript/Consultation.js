/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Consultation = Class.create({
  forms: {
    edit: null,
    summary: null
  },
  tabs: null,
  edit: 0,
  modified: 0,

  initialize: function(edit) {
    if (edit) {
      this.edit = 1;
    }
    this.forms.edit = getForm('consultationEdit');
    this.forms.summary = getForm('consultationSummary');

    this.tabs = Control.Tabs.create('consult-edit-tabs', false, {foldable: true, afterChange: function(elt) { DHE.triggerTab(elt); }});

    this.refreshListCategories($V(this.forms.edit.elements['chir_id']), $V(this.forms.edit.elements['_category_id']));
    this.makePlageInputs();
    this.setAutocompletion();
  },

  /**
   * Display the edit view in a modal
   *
   * @param {String=} tab Optional. The tab to display
   */
  displayEditView: function(tab) {
    Modal.open('consultation-edit', {
      title: 'Saisie des données de la consultation',
      showClose: false,
      width: '900',
      height: '500'
    });

    if (tab) {
      this.tabs.setActiveTab(tab);
    }
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
      id: 'consultation-cancelled',
      class: 'dhe_flag dhe_flag_important',
    }, 'Annulée');
    container.insert(cancelled);
    cancelled.setStyle({position: 'absolute', padding: '2px',fontWeight: 'bold', top: top + 'px', left: left + 'px'});
  },

  setType: function(value) {
    var summary = this.forms.summary;
    var edit = this.forms.edit;
    if (value == 'immediate') {
      DHE.setNull(edit.elements['plageconsult_id']);
      DHE.setNull(edit.elements['heure']);
      DHE.setNull(summary.elements['plageconsult_id']);
      DHE.setNull(summary.elements['heure']);
      DHE.setNotNull(edit.elements['_datetime']);
      DHE.setNotNull(summary.elements['_datetime']);
      $('consult-edit-plage-container').hide();
      $('consult-edit-date-container').show();
      $('plage-consult-container').hide();
      $('date-consult-container').show();
      $V(edit.elements['plageconsult_id'], '');
      $V(edit.elements['heure'], '');
      $V(summary.elements['plageconsult_id'], '');
      $V(summary.elements['heure'], '');
    }
    else {
      DHE.setNotNull(edit.elements['plageconsult_id']);
      DHE.setNotNull(summary.elements['plageconsult_id']);
      DHE.setNull(edit.elements['_datetime']);
      DHE.setNull(summary.elements['_datetime']);
      $('consult-edit-date-container').hide();
      $('consult-edit-plage-container').show();
      $('date-consult-container').hide();
      $('plage-consult-container').show();
      $V(edit.elements['_datetime'], '');
      $V(summary.elements['_datetime'], '');
    }
  },
  
  updateDuree: function() {
    var url = new Url('planningOp', 'ajax_dhe_consult_update_duree');
    url.addParam('plage_id', $V(this.forms.edit.elements['plageconsult_id']));
    url.addParam('consult_id', $V(this.forms.edit.elements['consult_id']));
    url.requestUpdate('consult-edit-duree');
  },

  refreshListCategories: function(chir_id, category_id) {
    var url = new Url('cabinet', 'httpreq_view_list_categorie');
    url.addParam('praticien_id', chir_id);
    url.addParam('categorie_id', category_id);
    url.addParam('form', this.forms.edit.name);
    url.addParam('onchange', 'DHE.consult.syncView(this, null, null, true);');
    url.requestUpdate('categories_list');
  },

  makePlageInputs: function() {
    DHE.makePlageInput(
      this.forms.summary.elements['_datetime_plage'],
      this.selectPlage.bindAsEventListener(this, this.forms.summary),
      'Sélectionner une plage de consultation'
    );
    DHE.makePlageInput(
      this.forms.edit.elements['_datetime_plage'],
      this.selectPlage.bindAsEventListener(this, this.forms.edit),
      'Sélectionner une plage de consultation'
    );
  },

  selectPlage: function(event, form) {
    PlageConsultSelector.init = function(form) {
      this.DHE              = true;
      this.sForm            = form.name;
      this.sPlageconsult_id = 'plageconsult_id';
      this.sDate            = '_datetime_plage';
      this.sChir_id         = 'chir_id';
      this.sChir_view       = '_chir_view';
      this.sFunction_id     = '_function_id';
      this.sHeure           = 'heure';
      this.options          = {width: -30, height: -30};

      this.modal();
    };

    PlageConsultSelector.init(form);
  },

  syncChirField: function(view, field) {
    this.syncField(view, field, field.form.elements['_chir_view']);
    this.refreshListCategories($V(this.forms.edit.elements['chir_id']), $V(this.forms.edit.elements['_category_id']));
  },

  /**
   * Synchronize the value of the given field, in the given view, with the same field of the other view
   *
   * @param {string}            view       The view where the field is located (summary or edit)
   * @param {HTMLInputElement}  field      The field to synchronize
   * @param {HTMLInputElement=} field_view A field who contain the view of the field (like the name for the patient_id). Optional
   * @param {Boolean=}          auto       Indicate that the field has been modified automatically
   */
  syncField: function(view, field, field_view, auto) {
    this.setModified(auto);
    var form_to_sync;

    if (view == 'edit') {
      form_to_sync = this.forms.summary;
    }
    else {
      form_to_sync = this.forms.edit;
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
   * @param {Boolean=}         auto  Indicate that the field has been modified automatically
   */
  syncView: function(field, text, title, auto) {
    this.setModified(auto);
    var id = 'consult-' + field.name;
    DHE.syncView(field, id, text, title);
  },

  /**
   * Synchronize the summary with the field
   *
   * @param {HTMLInputElement} field  The diagnostic field
   * @param {String=}          title  An optionnal title for the flag
   * @param {Array=}           values If set, if value of the field is in the array, the flag will be displayed
   * @param {Boolean=}         auto   Indicate that the field has been modified automatically
   */
  syncViewFlag: function(field, title, values, auto) {
    this.setModified(auto);
    var id = 'consult-' + field.name;
    DHE.syncViewFlag(field, id, title, values);
  },

  setAutocompletion: function() {
    /* Edit view */
    /* Chir */
    var url = new Url('mediusers', 'ajax_users_autocomplete');
    url.addParam('praticiens', 1);
    url.addParam('input_field', '_chir_view');
    url.autoComplete(this.forms.edit.elements['_chir_view'], null, {
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
    });

    /* Summary view */
    url = new Url('mediusers', 'ajax_users_autocomplete');
    url.addParam('praticiens', 1);
    url.addParam('input_field', '_chir_view');
    url.autoComplete(this.forms.summary.elements['_chir_view'], null, {
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
    });
  },

  /**
   * Set the modified field
   *
   * @param {Boolean=} auto Indicate that the field has been modified automatically
   */
  setModified: function(auto) {
    if (this.edit && !auto) {
      this.modified = 1;
    }
  }
});