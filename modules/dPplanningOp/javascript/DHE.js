/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

DHE = {
  forms: {
    patient: {
      select: null,
      edit: null
    },
    protocol: null,
    edit: null,
  },
  configs: {
    currency: null,
    sejour: {
      provenance_transfert_obligatory: null,
      date_entree_transfert_obligatory : null,
      required_dest_when_transfert: null,
      required_dest_when_mutation: null,
      required_uf_soins: null,
      heure_sortie_ambu: null,
      heure_sortie_autre: null,
      heure_entree_veille: null,
      heure_entree_jour: null,
      blocage_occupation: null
    },
    operation: {
      date_min: null,
      date_max: null,
      filter_dates: null,
      show_duree_preop: null,
      show_presence_op: null,
      duree_deb: null,
      duree_fin: null,
      hour_urgence_deb: null,
      hour_urgence_fin: null,
      min_intervalle: null,
      time_urgence: null
    }
  },
  action: null,

  sejour: null,
  consult: null,
  operation: null,

  /**
   * Initialize the configs and the autocomple fields
   *
   * @param configs
   */
  initialize: function(configs, action) {
    this.forms.patient.select = getForm('selectPatient');
    this.forms.patient.edit = getForm('patientEdit');
    this.forms.protocol = getForm('selectProtocole');
    this.forms.edit = getForm('DHEedit');
    this.configs = configs;
    this.action = action;
    this.setAutocompletion();

    var edit_consult = false;
    var edit_operation = false;
    if (this.action == 'edit_consultation' || this.action == 'new_consultation') {
      edit_consult = true;
    }
    if (this.action == 'edit_operation' || this.action == 'new_operation') {
      edit_operation = true;
    }

    this.sejour = new Sejour(true);
    this.consult = new Consultation(edit_consult);
    this.operation = new Operation2(edit_operation);
  },

  /**
   * Callback launcher when activating a tab
   *
   * @param {HTMLDivElement} elt The tab
   */
  triggerTab: function(elt) {
    if (/docs/.test(elt.id)) {
      this.loadDocs(elt);
    }
    else if (/prescription/.test(elt.id)) {
      Prescription.reloadPrescSejour(null, this.sejour.forms.edit.sejour_id.value);
    }
  },

  /**
   * Apply the given protocol
   *
   * @param {String} id   The id of the protocol
   */
  applyProtocol: function(id) {
    var protocol = aProtocoles[id];
    var libelle = protocol.libelle;
    if (protocol.for_sejour) {
      libelle = protocol.libelle_sejour;
    }
    $V(this.forms.protocol.elements['_protocole_view'], libelle);
    $V(this.forms.protocol.elements['protocole_id'], protocol.protocole_id);

    this.sejour.applyProtocol(protocol);

    if (!protocol.for_sejour) {
      this.operation.applyProtocol(protocol);
    }
  },

  /**
   * Open the patient selector
   */
  selectPatient: function() {
    PatSelector.init = function(form) {
      this.sForm    = form.name;
      this.sId      = 'patient_id';
      this.sView    = '_patient_view';
      this.sSexe    = '_patient_sexe';
      this.sTutelle = '_patient_tutelle';
      this.sALD     = '_patient_ald';
      this.pop();
    };

    PatSelector.init(DHE.forms.patient.select);
  },

  /**
   * Display the view of the selected object
   *
   * @param {String}      type  The class of the selected object
   * @param {Integer}     id    The id of the object
   * @param {HTMLElement} field The select field
   */
  selectObject: function(type, id, field) {
    /* For the case when the seelct's default option is selected */
    if (id == 0 && field) {
      this.hideObjects();
    }

    if (id == 0) {
      $V($('select-object-' + type), 0, false);
    }

    var container_id;
    if (type == 'COperation') {
      container_id = 'operation';
      $('consultation').hide();
      $V($('select-object-CConsultation'), 0, false);
      this.consult.edit = false;
    }
    else {
      container_id = 'consultation';
      $('operation').hide();
      $V($('select-object-COperation'), 0, false);
      this.operation.edit = false;
    }

    $('selected-object-type').innerHTML = id ? field.down('option:selected').innerHTML : $T(type);
    $(container_id).show();
    $('fieldset-objects').show();

    $('objects-state').update();

    var url = new Url('planningOp', 'ajax_dhe_object');
    url.addParam('object_class', type);
    url.addParam('object_id', id);
    url.addParam('sejour_id', $V(this.sejour.forms.edit.elements['sejour_id']));
    url.addParam('patient_id', $V(this.sejour.forms.edit.elements['patient_id']));
    url.addParam('chir_id', $V(this.sejour.forms.edit.elements['praticien_id']));
    url.addParam('grossesse_id', $V(this.sejour.forms.edit.elements['grossesse_id']));
    url.requestUpdate(container_id);
  },

  /**
   * Hide the objects fieldset and set the modified field of the forms to 0
   */
  hideObjects: function() {
    if ((this.operation.edit && this.operation.modified) || (this.consult.edit && this.consult.modified)) {
      Modal.confirm('Attention, les changements que vous avez apportés ne seront pas appliqués.<br>Etes vous sur?', {
        onOK: function() {
          $V($('select-object-CConsultation'), 0, false);
          $V($('select-object-COperation'), 0, false);
          DHE.operation.edit = false;
          DHE.operation.modified = false;
          DHE.consult.edit = false;
          DHE.consult.modified = false;
          $('objects-state').update();
          $('fieldset-objects').hide();
        }
      })
    }
    else {
      $V($('select-object-CConsultation'), 0, false);
      $V($('select-object-COperation'), 0, false);
      $('fieldset-objects').hide();
      $('objects-state').update();
    }
  },

  /** Create a field who looks like a datePicker but with a custom onclick function
   *
   * {HTMLInputElement} field   The field
   * {Function}         onclick The method called on the click on the element
   * {String=}          title   An optional title for calendar image
   */
  makePlageInput: function(field, onclick, title) {
    var container = DOM.div({class: 'datePickerWrapper', style: 'position:relative; border:none; padding:0; margin:0; display:inline-block;'});
    field.wrap(container);

    var options = {
      src: 'images/icons/calendar.gif',
      class: 'inputExtension'
    };

    if (title) {
      options.title = title;
    }

    var icon = DOM.i({class: "me-icon calendar me-primary inputExtension", title: title ? options.title : null });

    var padding = 3;
    icon.observe('load', function() {
      var elementDim = field.getDimensions();
      var iconDim = icon.getDimensions();
      padding = parseInt(elementDim.height - iconDim.height) / 2;
    }.bindAsEventListener(this)).setStyle({position: 'absolute', right: padding+'px', top: padding+'px'});

    container.insert(icon);
    container.observe('click', onclick);
  },

  /**
   * Synchronize the chir fields among the sejour, consult and operation forms
   *
   * @param {String}           object_type The object type (sejour, operation or consultation)
   * @param {String}           view        The view (summary or edit)
   * @param {HTMLInputElement} field       The field
   */
  syncChir: function(object_type, view, field) {
    var form_sejour = this.sejour.forms.edit;
    var form_op = this.operation.forms.edit;
    var form_consult = this.consult.forms.edit;
    var chir_id, chir_view;

    if (object_type == 'sejour') {
      chir_id = $V(field);
      chir_view = $V(field.form.elements['_chir_view']);
      if (!$V(form_op.elements['chir_id'])) {
        $V(form_op.elements['chir_id'], chir_id, false);
        $V(form_op.elements['_chir_view'], chir_view, false);
        form_op.elements['chir_id'].fire('ui:change');

        this.operation.syncField('edit', form_op.elements['chir_id'], form_op.elements['_chir_view']);
      }
      if (!$V(form_consult.elements['chir_id'])) {
        $V(form_consult.elements['chir_id'], chir_id, false);
        $V(form_consult.elements['_chir_view'], chir_view, false);
        form_consult.elements['chir_id'].fire('ui:change');

        this.consult.syncChirField('edit', form_consult.elements['chir_id']);
      }

      this.sejour.syncField(view, field, field.form.elements['_chir_view']);
    }
    else if (object_type == 'operation') {
      chir_id = $V(field);
      chir_view = $V(field.form.elements['_chir_view']);

      $V(form_sejour.elements['praticien_id'], chir_id, false);
      $V(form_sejour.elements['_chir_view'], chir_view, false);
      form_sejour.elements['praticien_id'].fire('ui:change');

      this.sejour.syncField('edit', form_sejour.elements['praticien_id'], form_sejour.elements['_chir_view']);

      if (!$V(form_consult.elements['chir_id'])) {
        $V(form_consult.elements['chir_id'], chir_id, false);
        $V(form_consult.elements['_chir_view'], chir_view, false);
        form_consult.elements['chir_id'].fire('ui:change');

        this.consult.syncChirField('edit', form_consult.elements['chir_id']);
      }

      this.operation.syncField(view, field, field.form.elements['_chir_view']);
    }
    else if (object_type == 'consultation') {
      this.consult.syncChirField(view, field);
    }
  },

  /**
   * Synchronize the patient between all the forms
   * @param view
   * @param field
   */
  syncPatient: function(view, field) {
    var form;
    if (view == 'summary') {
      form = this.sejour.forms.edit;
    }
    else {
      form = this.forms.patient.select;
    }

    this.syncField($V(field), form.elements['patient_id'], $V(field.form.elements['_patient_view']), form.elements['_patient_view']);
    $V(this.consult.forms.edit.elements['patient_id'], $V(field));
    this.sejour.loadListSejour();
    this.sejour.setModified();
    this.consult.setModified();
  },

  /**
   * Empty the patient_id field and the fields that contains informations on the patient (sex, tutelle and ald)
   */
  emptyPatient: function() {
    var form = this.forms.patient.select;
    $V(form.elements['_patient_view'], '');
    $V(this.sejour.forms.edit.elements['_patient_view'], '');
    $V(form.elements['_patient_ald'], '');
    $V(form.elements['_patient_sexe'], '');
    $V(form.elements['_patient_tutelle'], '');
    $V(form.elements['patient_id'], '');
  },

  /**
   * Hide or show the field concerne_ALD according to the value of the field
   *
   * @param {HTMLInputElement} field The ALD field
   */
  syncALD: function(field) {
    $V(this.sejour.forms.edit.elements['_ald'], $V(field));
    $V(field) == '1' ? $('consult-edit-concerne_ALD').show() : $('consult-edit-concerne_ALD').hide();
  },

  /**
   * Set the value of the field adresse_par_prat_id depending on the selected correspondant
   */
  changeAdressePar: function(object) {
    if (object == 'consult') {
      object = this.consult;
    }
    else {
      object = this.sejour;
    }
    var view = '';
    if ($V(object.forms.edit.elements['adresse_par_prat_id'])) {
      if ($V(object.forms.edit.elements['_correspondants_medicaux'])) {
        view = object.forms.edit.elements['_correspondants_medicaux'].down('option:selected').readAttribute('data-view');
      }
      else {
        $V(object.forms.edit.elements['_correspondants_medicaux'], '', false);
        view = $('_adresse_par_prat').down('span').innerHTML;
      }
    }

    $V(object.forms.edit.elements['_adresse_par_view'], view);
  },

  /**
   * Submit the data of the DHE
   */
  submit: function(action) {
    var data = {};

    if (action == 'save') {
      data = {
        sejour:       null,
        operation:    null,
        consultation: null
      }

      if (this.sejour.edit && this.sejour.modified) {
        data.sejour = this.setDataFromForm(this.sejour);
      }
      else {
        data.sejour = {sejour_id: $V(this.sejour.forms.edit.elements['sejour_id'])};
      }

      if (this.operation.edit && this.operation.modified) {
        data.operation = this.setDataFromForm(this.operation);
      }

      if (this.consult.edit && this.consult.modified) {
        data.consultation = this.setDataFromForm(this.consult);
      }
    }
    else {
      var object_guids = [];

      $$('div#' + action + '-objects input[type="checkbox"].select_object:checked').each(function(element) {
        object_guids.push(element.readAttribute('data-guid'));
      });

      if ($(action + '-select_sejour').checked) {
        object_guids.push($(action + '-select_sejour').readAttribute('data-guid'));
      }

      data = {
        object_guid: object_guids
      };

      $V(this.forms.edit.elements['action'], action);
    }

    $V(this.forms.edit.elements['data'], JSON.stringify(data));
    this.forms.edit.submit();
  },

  /**
   * Get the data from the inputs of the edit form of the given object
   *
   * @param {Object} object The object
   * @returns {Object}
   */
  setDataFromForm: function(object) {
    return object.forms.edit.serialize(true);
  },

  /**
   * Display the modal that allow the user to select the objects to delete or cancel
   *
   * @param {String} action The action to perform (cancel or delete)
   */
  showObjectSelector: function(action) {
    var title = 'Sélection des objets à ';
    if (action == 'delete') {
      title += $('delete');
    }
    else {
      title += $T('cancel')
    }

    Modal.open(action + '-objects', {
      title: title,
      showClose: true
    });
  },

  /**
   * Select or deselect all the objects checkboxes
   * @param {String}           action
   * @param {HTMLInputElement} checkbox
   */
  checkAllObjects: function(action, checkbox) {
    $$('div#' + action + '-objects input[type="checkbox"].select_object').each(function(element) {
      element.checked = checkbox.checked;
      if (checkbox.checked) {
        element.disable();
      }
      else {
        element.enable();
      }
    });
  },

  /**
   * Sync the value between two fields, and the view field if necessary
   *
   * @param {String}         value      The value of the modified field
   * @param {HTMLInputElement}  field   The field to synchronize
   * @param {String=}        view       The value of the view
   * @param {HTMLInputElement=} view_field The view field
   */
  syncField: function(value, field, view, view_field) {
    $V(field, value, false);
    if (field.hasClassName('notNull')) {
      field.fire('ui:change');
    }

    if (view && view_field) {
      $V(view_field, view, false);
    }
  },

  /**
   * Synchronize the summary with the field
   *
   * @param {HTMLInputElement} field The diagnostic field
   * @param {String}           id    The id of the summary element
   * @param {String=}          text  The text to display in place of the value (for the reference field)
   * @param {String=}          title The title of the display element
   */
  syncView: function(field, id, text, title) {
    var element = $(id);
    var value = $V(field);

    if (value) {
      if (field.readAttribute('view')) {
        value = $V(field.form.elements[field.readAttribute('view')]);
      }

      if (field.hasClassName('enum')) {
        value = $T('CSejour.' + field.name + '.' + value);
      }

      if (field.hasClassName('dateTime') || field.hasClassName('date') || field.hasClassName('time')) {
        value = $V(field.form.elements[field.name + '_da']).replace(':', 'h');
      }

      if (field.tagName == 'SELECT') {
        value = field.down('option:selected').innerHTML;
      }

      if (field.tagName == 'TEXTAREA' && value.length > 50) {
        value = value.substr(0, 50) + '...';
      }

      if (text) {
        value = text;
      }

      element.innerHTML = value;
      element.show();

      if (title) {
        element.title = title;
      }
    }
    else {
      element.innerHTML = '';
      element.hide();
    }
  },

  /**
   * Synchronize the summary with the field
   *
   * @param {HTMLInputElement} field  The diagnostic field
   * @param {String}           id    The id of the summary element
   * @param {String=}          title  An optionnal title for the flag
   * @param {Array=}           values If set, if value of the field is in the array, the flag will be displayed
   */
  syncViewFlag: function(field, id, title, values) {
    var element = $(id);
    var value = $V(field);

    if (values) {
      if (values.indexOf(value) != -1) {
        if (title) {
          element.title = title;
        }
        element.show();
      }
      else {
        element.hide();
      }
    }
    else {
      if (value != '0' && value != '') {
        if (title) {
          element.title = title;
        }
        element.show();
      }
      else {
        element.hide();
      }
    }
  },

  /**
   * Set the hidden bool field according to the state of the given checkbox
   *
   * @param {HTMLInputElement} checkbox The checkbox
   * @param {HTMLInputElement} field    The field
   */
  setCheckboxField: function(checkbox, field) {
    if (checkbox.checked) {
      $V(field, '1', true);
    }
    else {
      $V(field, '0', true);
    }
  },

  /**
   * Empty the given field
   *
   * @param {HTMLInputElement} field The field
   */
  emptyField: function(field) {
    $V(field, '', true);
    if (field.readAttribute('view')) {
      $V(field.form.elements[field.readAttribute('view')], '');
    }
  },

  /**
   * Set the given field to a not null field
   *
   * @param {HTMLInputElement} field The field
   */
  setNotNull: function(field) {
    field.addClassName('notNull');
    if (field.getLabel()) {
      field.getLabel().addClassName('notNull');
    }
    field.observe('change', notNullOK).observe('keyup', notNullOK).observe('ui:change', notNullOK);
  },

  /**
   * Set the given field to a null field
   *
   * @param {HTMLInputElement} field The field
   */
  setNull: function(field) {
    field.removeClassName('notNull');
    if (field.getLabel()) {
      field.getLabel().removeClassName('notNull').removeClassName('notNullOK');
    }
  },

  /**
   * Load the documents for a specifc context
   *
   * @param {HTMLDivElement} elt The targetted element
   */
  loadDocs: function(elt) {
    var object_class, object_id, patient_id, split = elt.id.split("-"), target = split[0];

    if (target == "sejour") {
      object_class = "CSejour";
      object_id = $V(this.sejour.forms.edit.sejour_id);
    }
    else if (target == "consult") {
      object_class = "CConsultation";
      object_id = $V(this.consult.forms.edit.consultation_id);
    }
    else if (target == "operation") {
      object_class = "COperation";
      object_id = $V(this.operation.forms.edit.operation_id);
    }

    if (!object_id) {
      return;
    }

    patient_id = $V(this.sejour.forms.edit.patient_id);

    new Url("patients", "vw_all_docs")
      .addParam("patient_id", patient_id)
      .addParam("context_guid", object_class + "-" + object_id)
      .requestUpdate(elt);
  },

  /**
   * Synchronize the files between modal and summary
   *
   * @param {String} object_class Context class
   * @param {String} object_id    Context id
   */
  syncDocs: function(object_class, object_id) {
    var target_div;

    if (object_class == "CSejour") {
      target_div = "sejour_documents_items";
    }
    else if (object_class == "COperation") {
      target_div = "operation_documents_items";
    }
    else if (object_class = "CConsultation") {
      target_div = "consult_documents_items";
    }

    if (!target_div) {
      return;
    }

    new Url("planningOp", "ajax_dhe_docitems")
      .addParam("object_class", object_class)
      .addParam("object_id", object_id)
      .requestUpdate(target_div);
  },

  /**
   * Set all the autocomplete fields
   */
  setAutocompletion: function() {
    /* Protocols autocomplete */
    var view_field = this.forms.protocol.elements['_protocole_view'];
    var id_field = this.forms.protocol.elements['protocole_id'];
    var url = new Url('planningOp', 'ajax_protocoles_autocomplete');
    url.addParam('field', id_field.name);
    url.addParam('input_field', view_field.name);
    url.autoComplete(view_field, null, {
      minchars: 3,
      method: 'get',
      select: 'view',
      dropdown: true,
      width: '400px',
      afterUpdateElement: function(field, selected) {
        var id = selected.get('id');
        $V(DHE.forms.protocol.elements['protocole_id'], id);
      },
      callback : function(input, query) {
        query = query + '&chir_id=' + $V(DHE.sejour.forms.edit.praticien_id);
        if (DHE.operation.edit) {
          query = query + '&for_sejour=0';
        }
        else {
          query = query + '&for_sejour=1';
        }
        return query;
      }.bind(this)
    });

    /* Patient autocomplete */
    view_field = this.forms.patient.select.elements['_patient_view'];
    id_field = this.forms.patient.select.elements['patient_id'];
    url = new Url('system', 'ajax_seek_autocomplete');
    url.addParam('object_class', 'CPatient');
    url.addParam('field', id_field.name);
    url.addParam('input_field', view_field.name);
    url.autoComplete(view_field, null, {
      minchars: 3,
      method: 'get',
      select: 'view',
      dropdown: false,
      width: '300px',
      afterUpdateElement: function(field, selected) {
        $V(field.form.elements['patient_id'], selected.get('guid').split('-')[1]);
        $V(DHE.forms.patient.select.elements['_patient_sexe'], selected.down('.view').get('sexe'));
        $V(DHE.sejour.forms.edit.elements['tutelle'], selected.down('.view').get('tutelle'));
        $V(DHE.forms.patient.select.elements['_patient_tutelle'], selected.down('.view').get('tutelle'));
        $V(DHE.forms.patient.select.elements['_patient_ald'], selected.down('.view').get('ald'));
        DHE.sejour.changeTypePec();
      }
    });
  }
};

Sejour = Class.create({
  forms: {
    edit    : null,
    summary : null
  },
  initial_entry_date: null,
  occupation: 0,
  tabs: null,
  edit: 0,
  modified: 0,

  /**
   * Set the forms, create the Control tabs and set the autocomplete field
   */
  initialize: function(edit) {
    if (edit) {
      this.edit = 1
    }
    this.forms.edit = getForm('sejourEdit');
    this.forms.summary = getForm('sejourSummary');

    /* Sinc the field grossesse_id is defined in a template, we must set the onchange manually for synchronize the view with it */
    if ($('sejourEdit__grossesse_id')) {
      $('sejourEdit__grossesse_id').onchange = function() {DHE.sejour.syncViewGrossesse(this)};
    }
    this.setAutocompletion();

    this.tabs = Control.Tabs.create('sejour-edit-tabs', false, {foldable: true, afterChange: function(elt) { DHE.triggerTab(elt); }});

    if (DHE.configs.sejour.required_uf_soins == 'obl') {
      DHE.setNotNull(this.forms.edit.elements['uf_soins_id']);
    }

    if (DHE.configs.sejour.required_uf_med == 'obl') {
      DHE.setNotNull(this.forms.edit.elements['uf_medicale_id']);
    }

    if ($V(this.forms.edit.elements['entree_prevue']) != '') {
      this.initial_entry_date = Date.fromDATETIME($V(this.forms.edit.elements['entree_prevue'])).toDATE();
    }
    this.updateOccupation();
    this.loadListSejour();
  },

  /**
   * Display the edit view in a modal
   *
   * @param {String=} tab Optional. The tab to display
   */
  displayEditView: function(tab) {
    Modal.open('sejour-edit', {
      title: 'Saisie des données du séjour',
      showClose: false,
      width: '900',
      height: '500'
    });

    if (tab) {
      this.tabs.setActiveTab(tab);
    }
  },

  /**
   * Submit the sejour's form
   *
   * @param {String} action The action to perform
   */
  submit: function(action) {
    switch (action) {
      case 'delete':
        $V(this.forms.edit.elements['del'], 1);
        break;
      case 'cancel':
        $V(this.forms.edit.elements['annule'], 1);
        break;
      default:
    }

    this.forms.edit.submit();
  },

  /**
   * Load the sejour list and the collision flag
   */
  loadListSejour: function() {
    if ($V(this.forms.edit.elements['patient_id'])) {
      var url = new Url('planningOp', 'ajax_dhe_list_sejours');
      url.addParam('patient_id', $V(this.forms.edit.elements['patient_id']));
      url.addParam('sejour_id', $V(this.forms.edit.elements['sejour_id']));
      url.addParam('group_id', $V(this.forms.edit.elements['group_id']));
      url.addParam('entree_prevue', $V(this.forms.edit.elements['entree_prevue']));
      url.addParam('sortie_prevue', $V(this.forms.edit.elements['sortie_prevue']));
      url.requestUpdate('sejour-list-container', {
        /* Custom insertion for not displaying the loading */
        insertion: function(element, content) {
          element.innerHTML = content;
        },
        onComplete: this.setListSejourPosition.curry()
      });
    } else {
      if ($('list_sejours')) {
        $('list_sejours').hide();
      }
      if ($('collision')) {
        $('collision').hide();
      }
    }
  },

  /**
   * Set the position of the elements that shows the number of existing sejour, and the collision flag
   */
  setListSejourPosition: function() {
    var legend = $('sejour_summary').down('legend');
    var offset = legend.cumulativeOffset();
    var list = $('list_sejours');
    var top = offset.top - $('dhe_sejour').cumulativeOffset().top;
    var left = offset.left + legend.getWidth() + 15;

    if (parseInt(list.readAttribute('data-count'))) {
      list.setStyle({top: top + 'px', left: left + 'px'});
      $('sejour-list-container').show();
      list.show();

      var collision = $('collision');
      if (parseInt(collision.readAttribute('data-count'))) {
        left = left + list.getWidth() + 15;
        collision.setStyle({top: top + 'px', left: left + 'px'});
        collision.show();
      }

      var tooltip = $('list_sejours_tooltip');
      var width = tooltip.getWidth() + 15;
      tooltip.down('table').setStyle({width: tooltip.getWidth() + 'px'});
      tooltip.setStyle({width: width + 'px'});

      if ($('sejour-annule')) {
        var cancelled = $('sejour-annule');

        if (parseInt(collision.readAttribute('data-count'))) {
          left = left + collision.getWidth() + 15;
        }
        else {
          left = left + list.getWidth() + 15;
        }

        cancelled.setStyle({top: top + 'px', left: left + 'px'});
        cancelled.show();
      }
    }
    else if ($('sejour-annule')) {
      var cancelled = $('sejour-annule');
      cancelled.setStyle({top: top + 'px', left: left + 'px'});
      cancelled.show();
    }
  },

  /**
   * Check that the sex of the patient is allowed with the pec type
   */
  changeTypePec: function() {
    var field = this.forms.edit.down('input[name="type_pec"]:checked');

    if ($V(field) == 'O' && $V(DHE.forms.patient.select.elements['_patient_sexe']) == 'm') {
      field.up('td').insert(DOM.div({id: 'sejour-type_pec-alert', class: 'small-warning'}, $T('CSejour-msg-type_pec-O_for_male')));
    }
    else {
      if ($('sejour-type_pec-alert')) {
        $('sejour-type_pec-alert').remove();
      }
    }
  },

  /**
   * Set the admissions fields (entree_prevue, sortie_prevue, duree_prevue and type) depending on the given modified field
   * Sync the edit and summary view
   *
   * @param {HTMLInputElement} field The modified field
   * @param {String=}          view  The view to which the field belong (ie summary or edit)
   */
  setAdmissionDates: function(field, view) {
    if (view == 'summary' && field.name != 'sortie_prevue') {
      var field_view;
      if (field.hasClassName('dateTime')) {
        field_view = field.form.elements[field.name + '_da'];
      }
      this.syncField(view, field, field_view);
    }

    var form = this.forms.edit;
    var entree, sortie;
    if ($V(form.elements['entree_prevue'])) {
      entree = Date.fromDATETIME($V(form.elements['entree_prevue']));
    }
    else {
      entree = new Date();
    }
    if ($V(form.elements['sortie_prevue'])) {
      sortie = Date.fromDATETIME($V(form.elements['sortie_prevue']));
    }
    else {
      sortie = new Date();
    }
    var type = $V(form.elements['type']);
    var nights = $V(form.elements['_duree_prevue']);
    var hours = $V(form.elements['_duree_prevue_heure']);

    switch (field.name) {
      case 'entree_prevue':
        if (type == 'ambu') {
          sortie = entree.cloneDate();
          sortie.addHours(hours);
          nights = 0;
          if (sortie.toDATE() != entree.toDATE()) {
            sortie = entree.cloneDate();
            sortie.setHours(23, 0, 0);
            hours = Math.round((sortie - entree) / (60 * 60 * 1000));
          }
        }
        else {
          sortie = entree.cloneDate();
          sortie.addDays(nights);

          hours = 0;
        }

        this.setDate(form.elements['sortie_prevue'], sortie);
        $V(form.elements['_duree_prevue_heure'], hours, false);
        $V(form.elements['_duree_prevue'], nights, false);
        $('sejour-edit-view-days').innerHTML = parseInt(nights) + 1;
        $('sejour-summary-view-days').innerHTML = parseInt(nights) + 1;
        break;
      case 'sortie_prevue':
        if (type == 'ambu') {
          if (sortie > entree) {
            nights = Math.round((sortie - entree) / (24 * 60 * 60 * 1000));
            if (nights > 0) {
              type = 'comp';
              hours = 0;
              $V(form.elements['type'], type, false);
              hours = 0;
              $V(form.elements['_duree_prevue_heure'], hours, false);
              $('sejour-edit-duree-unit-hours').hide();
              $('sejour-edit-duree-unit-nights').show();
              $('sejour-summary-duree-unit-hours').hide();
              $('sejour-summary-duree-unit-nights').show();
            }
            else {
              hours = Math.round((sortie - entree) / (60 * 60 * 1000));
            }
          }
          else {
            entree = sortie.cloneDate();
            entree.setHours(sortie.getHours() - hours);
          }
        }
        else {
          if (sortie < entree) {
            entree = sortie.cloneDate();
            entree.addDays(-nights);
          }
          else {
            nights = Math.round((sortie - entree) / (24 * 60 * 60 * 1000));
          }
        }

        this.setDate(form.elements['entree_prevue'], entree);
        $V(form.elements['_duree_prevue_heure'], hours, false);
        $V(form.elements['_duree_prevue'], nights, false);
        $('sejour-edit-view-days').innerHTML = parseInt(nights) + 1;
        $('sejour-summary-view-days').innerHTML = parseInt(nights) + 1;
        break;
      case '_duree_prevue':
        sortie = entree.cloneDate();
        sortie.addDays(nights);
        this.setDate(form.elements['sortie_prevue'], sortie);
        $('sejour-edit-view-days').innerHTML = parseInt(nights) + 1;
        $('sejour-summary-view-days').innerHTML = parseInt(nights) + 1;
        break;
      case '_duree_prevue_heure':
        sortie = entree.cloneDate();
        sortie.addHours(hours);
        if (sortie.toDATE() != entree.toDATE()) {
          sortie = entree.cloneDate();
          sortie.setHours(23, 0, 0);
          hours = Math.round((sortie - entree) / (60 * 60 * 1000));
          $V(form.elements['_duree_prevue_heure'], hours, false);
        }
        this.setDate(form.elements['sortie_prevue'], sortie);
        break;
      case 'type':
        if (type == 'ambu') {
          if (entree.toDATE() != sortie.toDATE()) {
            sortie = entree.cloneDate();
          }

          entree.setHours(DHE.configs.sejour.heure_entree_jour, 0, 0);
          sortie.setHours(DHE.configs.sejour.heure_sortie_ambu, 0, 0);
          hours = sortie.getHours() - entree.getHours();
          nights = 0;
          $('sejour-edit-duree-unit-nights').hide();
          $('sejour-edit-duree-unit-hours').show();
          $('sejour-summary-duree-unit-nights').hide();
          $('sejour-summary-duree-unit-hours').show();
        }
        else {
          if (entree.toDATE() == sortie.toDATE() && type == 'comp') {
            sortie.addDays(1);
            nights = 1;
          }

          sortie.setHours(DHE.configs.sejour.heure_sortie_autre, 0, 0);
          entree.setHours(DHE.configs.sejour.heure_entree_jour, 0, 0)
          hours = 0;
          $('sejour-edit-duree-unit-hours').hide();
          $('sejour-edit-duree-unit-nights').show();
          $('sejour-summary-duree-unit-hours').hide();
          $('sejour-summary-duree-unit-nights').show();
        }

        this.setDate(form.elements['entree_prevue'], entree);
        this.setDate(form.elements['sortie_prevue'], sortie);
        $V(form.elements['_duree_prevue_heure'], hours, false);
        $V(form.elements['_duree_prevue'], nights, false);
        $('sejour-edit-view-days').innerHTML = parseInt(nights) + 1;
        $('sejour-summary-view-days').innerHTML = parseInt(nights) + 1;
        break;
    }

    this.syncField('edit', form.elements['entree_prevue'], form.elements['entree_prevue_da']);
    this.syncField('edit', form.elements['_duree_prevue']);
    this.syncField('edit', form.elements['_duree_prevue_heure']);
    this.syncField('edit', form.elements['type']);
    this.syncView(form.elements['entree_prevue']);
    this.syncView(form.elements['sortie_prevue']);
    this.syncView(form.elements['type']);
    this.updateOccupation();
  },

  /**
   * Hide or display the fields reanimation and UHCD acording to the value of the field type
   *
   */
  setTypeHospi: function() {
    var form = this.forms.edit;
    var value = $V(form.elements['type'])
    if (value != 'comp') {
      $V(form.elements['reanimation'], '0', true);
      $V(form.elements['UHCD'], '0', true);
    }

    $(form).select('.sejour-edit-reanimation').invoke(value == 'comp' ? 'show' : 'hide');
    $(form).select('.sejour-edit-uhcd').invoke(value == "comp" ? 'show' : 'hide');
  },

  /**
   * Set the given date field to the given value.
   * Also set the view field of the date picker
   *
   * @param {HTMLInputElement} field The field
   * @param {Date}             value The date
   */
  setDate: function(field, value) {
    $V(field, value.toDATETIME(true), false);
    $V(field.form.elements[field.name + '_da'], value.toLocaleDateTime(), false);
    if (field.hasClassName('notNull')) {
      field.fire('ui:change');
    }
  },

  /**
   * Display or hide the fields linked to each entry mode
   */
  changeModeEntree: function() {
    var field;
    if (this.forms.edit.elements['mode_entree_id']) {
      field = this.forms.edit.elements['mode_entree'];
    }
    else {
      field = this.forms.edit.down('input[name="mode_entree"]:checked');
    }

    var value = $V(field);

    /* Mutation */
    if (value == '6') {
      $('sejour-entree-fields-mutation').show();
    }
    else {
      $('sejour-entree-fields-mutation').hide();
      $V(this.forms.edit.elements['service_entree_id'], '');
      $V(this.forms.edit.elements['_service_entree_view'], '');
    }

    /* Transfert */
    if (value == '7') {
      $('sejour-entree-fields-transfert').show();
      if (this.forms.edit.elements['provenance'].readAttribute('obligatory')) {
        DHE.setNotNull(this.forms.edit.elements['provenance']);
      }
      if (this.forms.edit.elements['date_entree_reelle_provenance'].readAttribute('obligatory')) {
        DHE.setNotNull(this.forms.edit.elements['date_entree_reelle_provenance']);
      }
    }
    else {
      $('sejour-entree-fields-transfert').hide();
      $V(this.forms.edit.elements['provenance'], '');
      $V(this.forms.edit.elements['etablissement_entree_id'], '');
      $V(this.forms.edit.elements['_etablissement_entree_view'], '');
      $V(this.forms.edit.elements['date_entree_reelle_provenance'], '');
      $V(this.forms.edit.elements['date_entree_reelle_provenance_da'], '');
      if (DHE.configs.sejour.provenance_transfert_obligatory) {
        DHE.setNull(this.forms.edit.elements['provenance']);
      }
      if (DHE.configs.sejour.date_entree_transfert_obligatory) {
        DHE.setNull(this.forms.edit.elements['date_entree_reelle_provenance']);
      }
    }
  },

  /**
   * Display or hide the fields linked to each exit mode
   */
  changeModeSortie: function() {
    var field;
    if (this.forms.edit.elements['mode_sortie_id']) {
      field = this.forms.edit.elements['mode_sortie'];
    }
    else {
      field = this.forms.edit.down('input[name="mode_sortie"]:checked');
    }

    var value = $V(field);

    if (value != 'transfert') {
      $('sejour-edit-etablissement_sortie_id').hide();
      $V(this.forms.edit.elements['etablissement_sortie_id'], '');
      $V(this.forms.edit.elements['_etablissement_sortie_view'], '');
    }
    else {
      $('sejour-edit-etablissement_sortie_id').show();
      $('sejour-edit-transport_sortie').show();
    }

    if (value != 'mutation') {
      $('sejour-edit-service_sortie_id').hide();
      $V(this.forms.edit.elements['service_sortie_id'], '');
      $V(this.forms.edit.elements['_service_sortie_view'], '');
    }
    else {
      $('sejour-edit-transport_sortie').hide();
      $V(this.forms.edit.elements['transport_sortie'], '', true);
      $V(this.forms.edit.elements['rques_transport_sortie'], '', true);
      $('sejour-edit-service_sortie_id').show();
    }

    if (value != 'deces') {
      $('sejour-edit-_date_deces').hide();
      $V(this.forms.edit.elements['_date_deces'], '');
      $V(this.forms.edit.elements['_date_deces_da'], '');
      DHE.setNull(this.forms.edit.elements['_date_deces']);
    }
    else {
      DHE.setNotNull(this.forms.edit.elements['_date_deces']);
      $('sejour-edit-_date_deces').show();
      $('sejour-edit-transport_sortie').show();
    }

    if (value == 'normal') {
      $('sejour-edit-transport_sortie').show();
    }

    if ((DHE.configs.sejour.required_dest_when_mutation && value == 'mutation')
        || (DHE.configs.sejour.required_dest_when_transfert && value == 'transfert')
    ) {
      DHE.setNotNull(this.forms.edit.elements['destination']);
    }
    else {
      DHE.setNull(this.forms.edit.elements['destination']);
    }
  },

  /**
   * Set the field chambre_seule if the prestation_id is set
   */
  changePrestation: function() {
    if ($V(this.forms.edit.elements['prestation_id']) != '') {
      $V(this.form.edit.elements['chambre_seule'], '1', true);
    }
  },

  /**
   * Show or hide the isolement fields depending on the value of the field isolement
   */
  changeIsolement: function() {
    if ($V(this.forms.edit.elements['isolement']) == '1') {
      $('sejour-edit-isolement').show();
    }
    else {
      $('sejour-edit-isolement').hide();
      $V(this.forms.edit.elements['_isolement_date'], '');
      $V(this.forms.edit.elements['_isolement_date_da'], '');
      $V(this.forms.edit.elements['isolement_fin'], '');
      $V(this.forms.edit.elements['isolement_fin_da'], '');
      $V(this.forms.edit.elements['raison_medicale'], '');
    }
  },

  /**
   * Display or hide the field acs_type depending on the value of the field acs
   */
  changeACS: function() {
    if ($V(this.forms.edit.elements['acs']) == '1') {
      $('sejour-edit-acs_type').show();
    }
    else {
      $('sejour-edit-acs_type').hide();
      $V(this.forms.edit.elements['acs_type'], 'none');
    }
  },

  /**
   * Display or hide the field nature_accident depending on the value of the field date_accident
   */
  changeDateAccident: function() {
     if ($V(this.forms.edit.elements['date_accident'])) {
      $('sejour-edit-nature_accident').show();
    }
    else {
      $('sejour-edit-nature_accident').hide();
      $V(this.forms.edit.elements['nature_accident'], '');
    }
  },

  /**
   * Display or hide the fields linked to the reanimation field
   */
  changeReanimation: function() {
    if ($V(this.forms.edit.elements['reanimation']) == '1' && $('sejour-edit-reanimation-fields')) {
      $('sejour-edit-reanimation').show();
    }
    else if ($('sejour-edit-reanimation-fields')) {
      $V(this.forms.edit.elements['technique_reanimation_status'], 'unknown');
      DHE.emptyField(this.forms.edit.elements['technique_reanimation']);
      $('sejour-edit-reanimation').hide();
    }
  },

  /**
   * Hide or display the service's occupation in function of the occupation rate
   *
   * @param {Integer} occupation The occupation rate
   */
  setOccupation: function(occupation) {
    this.occupation = occupation;
    if (this.occupation > -1) {
      $('sejour-edit-occupation').show();
    }
    else {
      $('sejour-edit-occupation').hide();
    }
  },

  /**
   * Get the occupation rate
   */
  updateOccupation: function() {
    var form = this.forms.edit;
    if ($V(form.elements['entree_prevue']) && $V(form.elements['type'])) {
      if (!this.initial_entry_date) {
        this.initial_entry_date = Date.fromDATETIME($V(form.elements['entree_prevue'])).toDATE();
      }

      let entree = $V(form.elements['_date_entree_prevue']) + ' ' + $V(form.elements['_hour_entree_prevue']).padStart(2, '0')
        + ':' + $V(form.elements['_min_entree_prevue']).padStart(2, '0') + ':00';
      let sortie = '';
      if ($V(form.elements['_date_sortie_prevue'])) {
        sortie = $V(form.elements['_date_sortie_prevue']) + ' ' + $V(form.elements['_hour_sortie_prevue']).padStart(2, '0')
          + ':' + $V(form.elements['_min_sortie_prevue']).padStart(2, '0') + ':59';
      }

      new Url('planningOp', 'httpreq_show_occupation_lits')
        .addParam('type', $V(form.elements['type']))
        .addParam('entree', entree)
        .addParam('sortie', sortie)
        .addParam('view', 'dhe')
        .requestUpdate('occupation_rate');
    }
  },

  /**
   * Check the service's occupation and display a message if it's too high to create the sejour
   *
   * @returns {boolean}
   */
  checkOccupation: function() {
    var date = Date.fromDATETIME($V(form.elements['entree_prevue']));
    if (DHE.configs.sejour.blocage_occupation && this.initial_entry_date != date.toDATE() && this.occupation >= 100) {
      alert("L'occupation des services est de " + this.occupation + "%.\nVeuillez contacter le responsable des services");
      return false;
    }

    return true;
  },

  /**
   * Open a modal for setting the diet fields
   */
  openDiet: function() {
    Modal.open('sejour-regime_alimentaire', {title: 'Régime alimentaire'});
  },

  /**
   * Apply the given protocol
   *
   * @param {Object} protocol The protocol
   */
  applyProtocol: function(protocol) {
    var form = this.forms.edit;

    $V(form.elements['praticien_id'], protocol.chir_id, true);
    $V(form.elements['_chir_view'], protocol.chir_view, true);

    if (!$V(form.elements['sejour_id']) || $V(form.elements['_duree_prevue']) != protocol.duree_hospi) {
      $V(form.elements['type'], protocol.type, true);
      $V(form.elements['_duree_prevue'], protocol.duree_hospi, true);
    }

    if (form.elements['charge_id']) {
      $V(form.elements['charge_id'], protocol.charge_id, true);
    }

    if (parseInt(protocol.duree_heure_hospi)) {
      $V(form.elements['_duree_prevue_heure'], protocol.duree_heure_hospi, true);
    }

    $V(form.elements['_uf_hebergement_view'], protocol._uf_hebergement_view, false);
    $V(form.elements['uf_hebergement_id'], protocol.uf_hebergement_id, true);
    $V(form.elements['_uf_medicale_view'], protocol._uf_medicale_view, false);
    $V(form.elements['uf_medicale_id'], protocol.uf_medicale_id, true);
    $V(form.elements['_uf_soins_view'], protocol._uf_soins_view, false);
    $V(form.elements['uf_soins_id'], protocol.uf_soins_id, true);
    $V(form.elements['hospit_de_jour'], protocol.hospit_de_jour, true);
    $V(form.elements['_service_view'], protocol._service_view, false);
    $V(form.elements['service_id'], protocol.service_id, true);
    $V(form.elements['type_pec'], protocol.type_pec, true);
    $V(form.elements['RRAC'], protocol.RRAC, true);
    $V(form.elements['_hour_entree_prevue'], protocol.hour_entree_prevue, true);
    $V(form.elements['_min_entree_prevue'], protocol.min_entree_prevue, true);
    $V(form.elements['circuit_ambu'], protocol.circuit_ambu, true);

    if (!$V(form.elements['sejour_id'])) {
      $V(form.elements['libelle'], protocol.libelle_sejour, true);
    }
    $V(form.elements['_DP_view'], protocol.DP, false);
    $V(form.elements['DP'], protocol.DP, true);
    $V(form.elements['_DR_view'], protocol.DR, false);
    $V(form.elements['DR'], protocol.DR, true);
    if ($V(form.elements['sejour_id']) && $V(form.elements['convalescence'])) {
      $V(form.elements['convalescence'], $V(form.elements['convalescence']) + "\n" + protocol.convalescence, true);
    }
    else {
      $V(form.elements['convalescence'], protocol.convalescence, true);
    }
    $V(form.elements['facturable'], protocol.facturable, true);

    if ($V(form.elements['sejour_id']) && $V(form.elements['rques'])) {
      $V(form.elements['rques'], $V(form.elements['rques']) + "\n" + protocol.convalescence, true);
    }
    else {
      $V(form.elements['rques'], protocol.rques_sejour, true);
    }
    $V(form.elements['_protocole_prescription_chir_id'], protocol.protocole_prescription_chir_id, true);
    $V(form.elements['_docitems_guid'], protocol._docitems_guid_sejour, true);
  },

  /**
   * Empty the fields that depends on the patient
   */
  emptyPatient: function() {
    $V(this.forms.edit.elements['_patient_view'], '');
    $V(this.forms.edit.elements['_patient_sex'], '');
    $V(this.forms.edit.elements['patient_id'], '', true);
  },

  /**
   * Synchronize the value of the given field, in the given view, with the same field of the other view
   *
   * @param {string}            view       The view where the field is located (summary or edit)
   * @param {HTMLInputElement}  field      The field to synchronize
   * @param {HTMLInputElement=} field_view A field who contain the view of the field (like the name for the patient_id). Optional
   */
  syncField: function(view, field, field_view) {
    this.setModified();
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
   * Sync the patient_id betwee the edit and summary views
   *
   * @param field
   * @param name
   */
  syncPatientField: function(field, name) {
    this.setModified();
    if (!name) {
      name = field.name;
    }

    $V(DHE.forms.patient.edit.elements[name], $V(field));
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
    var id = 'sejour-' + field.name;
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
    var id = 'sejour-' + field.name;
    DHE.syncViewFlag(field, id, title, values);
  },

  /**
   * Synchronize the summary with the field
   *
   * @param {HTMLInputElement} field The diagnostic field
   */
  syncViewDiagnostic: function(field) {
    this.setModified();
    var id, classname = 'dhe_diagnostic ', title;
    switch (field.name) {
      case 'DP':
        id = 'sejour-main_diagnostic';
        title = 'Principal: ' + field.get('libelle');
        classname = classname + 'dhe_diag_main';
        break;
      case 'DR':
        id = 'sejour-second_diagnostic';
        title = 'Secondaire: ' + field.get('libelle');
        classname = classname + 'dhe_diag_second';
        break;
    }

    var value = $V(field);
    var flag = $(id);
    if (value) {
      if (flag) {
        flag.innerHTML = $V(field);
        flag.title = title;
      }
      else {
        var items = $('sejour-linked_diagnostics');
        flag = DOM.span({class: classname, id: id, title: title}, $V(field));
        if (field.name == 'DP') {
          items.insert({top: flag});
        }
        else {
          items.insert(flag);
        }
      }
    }
    else {
      if (flag) {
        flag.remove();
      }
    }
  },

  /**
   * Sync the view of the pregnancy
   *
   * @param {HTMLInputElement} field The field
   */
  syncViewGrossesse: function(field) {
    this.setModified();
    var value = $V(field);

    if (value) {
      $('sejour-grossesse_id').onmouseover = function() {ObjectTooltip.createEx(this, 'CGrossesse-' + value);};
      $('sejour-grossesse_id').show();
    }
    else {
      $('sejour-grossesse_id').hide();
    }
  },

  /**
   * Set all the autocomplete
   */
  setAutocompletion: function() {
    /* View edition autocomplete */

    /* Patient */
    var url = new Url('system', 'ajax_seek_autocomplete');
    url.addParam('object_class', 'CPatient');
    url.addParam('field', 'patient_id');
    url.addParam('input_field', '_patient_view');
    url.autoComplete(this.forms.edit.elements['_patient_view'], null, {
      minchars: 3,
      method: 'get',
      select: 'view',
      dropdown: false,
      width: '300px',
      afterUpdateElement: function(field, selected) {
        $V(field.form.elements['patient_id'], selected.get('guid').split('-')[1]);
        $V(DHE.forms.patient.select.elements['_patient_sexe'], selected.down('.view').get('sexe'));
        $V(DHE.sejour.forms.edit.elements['tutelle'], selected.down('.view').get('tutelle'));
        $V(DHE.forms.patient.select.elements['_patient_tutelle'], selected.down('.view').get('tutelle'));
        $V(DHE.forms.patient.select.elements['_patient_ald'], selected.down('.view').get('ald'));
        DHE.sejour.changeTypePec();
      }
    });

    /* Chir */
    url = new Url('mediusers', 'ajax_users_autocomplete');
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

        $V(field.form.elements['praticien_id'], selected.getAttribute('id').split('-')[2]);
      }
    });

    /* CIM10 DP */
    CIM.autocomplete(
      this.forms.edit.elements['_DP_view'],
      null,
      {
        afterUpdateElement: function(field, selected) {
          $V(field.form.elements['DP'], $V(field));
          field.form.elements['DP'].writeAttribute('data-libelle', selected.down('div').innerHTML.trim());
        }
      }
    );

    /* CIM10 DR */
    CIM.autocomplete(
      this.forms.edit.elements['_DP_view'],
      null,
      {
        afterUpdateElement: function(field, selected) {
          $V(field.form.elements['DR'], $V(field));
          field.form.elements['DR'].writeAttribute('data-libelle', selected.down('div').innerHTML.trim());
        }
      }
    );

    /* Etablissement d'entrée */
    url = new Url('etablissement', 'ajax_autocomplete_etab_externe');
    url.addParam('field', 'etablissement_entree_id');
    url.addParam('input_field', '_etablissement_entree_view');
    url.addParam('view_field', 'nom');
    url.autoComplete(this.forms.edit.elements['_etablissement_entree_view'], null, {
      minChars: 0,
      method: 'get',
      select: 'view',
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        var form = field.form;
        form.elements['provenance'].removeClassName('notNull');
        form.elements['date_entree_reelle_provenance'].removeClassName('notNull');
        $V(form.elements['etablissement_entree_id'], selected.getAttribute('id').split('-')[2]);
      }
    });

    /* Etablissement de sortie */
    url = new Url('etablissement', 'ajax_autocomplete_etab_externe');
    url.addParam('field', 'etablissement_sortie_id');
    url.addParam('input_field', '_etablissement_sortie_view');
    url.addParam('view_field', 'nom');
    url.autoComplete(this.forms.edit.elements['_etablissement_sortie_view'], null, {
      minChars: 0,
      method: 'get',
      select: 'view',
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        $V(field.form.elements['etablissement_sortie_id'], selected.getAttribute('id').split('-')[2]);
      }
    });

    /* Service de provenance */
    url = new Url('system', 'httpreq_field_autocomplete');
    url.addParam('class', 'CSejour');
    url.addParam('field', 'service_entree_id');
    url.addParam('limit', '50');
    url.addParam('view_field', 'nom');
    url.addParam('show_view', false);
    url.addParam('input_field', '_service_entree_view');
    url.addParam('wholeString', true);
    url.addParam('min_occurences', 1);
    url.autoComplete(this.forms.edit.elements['_service_entree_view'], null, {
      minChars: 1,
      method: 'get',
      select: 'view',
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        $V(field.form.elements['service_entree_id'], selected.getAttribute('id').split('-')[2]);
      },
      callback: function(element, query) {
        query += '&where[group_id]=' + $V(DHE.sejour.forms.edit.elements['group_id']);
        if ($V(DHE.sejour.forms.edit.elements['cancelled'])) {
          query += '&where[cancelled]=1'
        }

        return query;
      }
    });

    /* Service de sortie */
    url = new Url('system', 'httpreq_field_autocomplete');
    url.addParam('class', 'CSejour');
    url.addParam('field', 'service_sortie_id');
    url.addParam('limit', '50');
    url.addParam('view_field', 'nom');
    url.addParam('show_view', false);
    url.addParam('input_field', '_service_sortie_view');
    url.addParam('wholeString', true);
    url.addParam('min_occurences', 1);
    url.autoComplete(this.forms.edit.elements['_service_sortie_view'], null, {
      minChars: 1,
      method: 'get',
      select: 'view',
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        $V(field.form.elements['service_sortie_id'], selected.getAttribute('id').split('-')[2]);
        var data = selected.down('.data');
        if (!$V(field.form.elements['destination'])) {
          $V(field.form.elements['destination'], data.get('default_destination'));
        }
        if (field.form.elements['orientation'] && !$V(field.form.elements['orientation'])) {
          $V(field.form.elements['orientation'], data.get('default_orientation'));
        }
      },
      callback: function(element, query) {
        query += '&where[group_id]=' + $V(DHE.sejour.forms.edit.elements['group_id']);
        if ($V(DHE.sejour.forms.edit.elements['cancelled'])) {
          query += '&where[cancelled]=1'
        }

        return query;
      }
    });

    if (this.forms.edit.elements['service_id']) {
      /* Service */
      url = new Url('system', 'httpreq_field_autocomplete');
      url.addParam('class', 'CSejour');
      url.addParam('field', 'service_id');
      url.addParam('limit', '50');
      url.addParam('view_field', 'nom');
      url.addParam('show_view', false);
      url.addParam('input_field', '_service_view');
      url.addParam('wholeString', true);
      url.addParam('min_occurences', 1);
      url.autoComplete(this.forms.edit.elements['_service_view'], null, {
        minChars:           1,
        method:             'get',
        select:             'view',
        dropdown:           true,
        afterUpdateElement: function (field, selected) {
          $V(field.form.elements['service_id'], selected.getAttribute('id').split('-')[2]);
        },
        callback:           function (element, query) {
          query += '&where[group_id]=' + $V(DHE.sejour.forms.edit.elements['group_id']);
          if ($V(DHE.sejour.forms.edit.elements['cancelled'])) {
            query += '&where[cancelled]=1'
          }

          return query;
        }
      });
    }

    if (this.forms.edit.elements['_unique_lit_id']) {
      /* Lit */
      url = new Url('hospi', 'ajax_lit_autocomplete');
      url.autoComplete(this.forms.edit.elements['_unique_lit_view'], null, {
        minChars: 0,
        method: 'get',
        select: 'view',
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          $V(DHE.sejour.forms.edit.elements['_unique_lit_id'], selected.id.split('-')[2]);
        },
        callback: function(input, query) {
          if (DHE.sejour.forms.edit.elements['service_id'] && $V(DHE.sejour.forms.edit.elements['service_id'])) {
            query += '&service_id=' + $V(DHE.sejour.forms.edit.elements['service_id']);
          }

          return query;
        }
      });
    }

    /* Assurance maladie */
    if (this.forms.edit.elements['_assurance_maladie']) {
      url = new Url('dPpatients', 'ajax_correspondant_autocomplete');
      url.addParam('patient_id', $V(this.forms.edit.elements['patient_id']));
      url.addParam('type', '_assurance_maladie_view');
      url.autoComplete(this.forms.edit.elements['_assurance_maladie_view'], null, {
        minChars:      0,
        dropdown:      true,
        select:        'newcode',
        updateElement: function (field, selected) {
          $V(DHE.sejour.forms.edit.elements['_assurance_maladie_view'], selected.down('.newcode').getText(), false);
          $V(DHE.sejour.forms.edit.elements['_assurance_maladie'], selected.down('.newcode').get('id'), true);
        }
      });
    }

    /* UF hebergement */
    url = new Url('hospi', 'ajax_autocomplete_uf');
    url.addParam('sejour_id', $V(this.forms.edit.elements['sejour_id']));
    url.addParam('uf_type', 'hebergement');
    url.autoComplete(this.forms.edit.elements['_uf_hebergement_view'], 'keyword', {
      minChars: 0,
      dropdown: true,
      callback: function(input, query) {
        var form = DHE.sejour.forms.edit;
        if ($V(input)) {
          query += '&keyword=' + $V(input);
        }
        if (form.elements['service_id'] && $V(form.elements['service_id'])) {
          query += '&service_id=' + $V(form.elements['service_id']);
        }
        if ($V(form.elements['praticien_id'])) {
          query += '&praticien_id=' + $V(form.elements['praticien_id']);
        }
        if ($V(form.elements['type'])) {
          query += '&sejour_type=' + $V(form.elements['type']);
        }
        if ($V(form.elements['entree_prevue'])) {
          query += '&entree=' + $V(form.elements['entree_prevue']);
        }
        if ($V(form.elements['sortie_prevue'])) {
          query += '&sortie=' + $V(form.elements['sortie_prevue']);
        }

        return query;
      },
      afterUpdateElement: function(field, selected) {
        $V(field, selected.readAttribute('data-view'));
        $V(DHE.sejour.forms.edit.elements['uf_hebergement_id'], selected.readAttribute('data-id'));
      }
    });

    /* UF soins */
    url = new Url('hospi', 'ajax_autocomplete_uf');
    url.addParam('sejour_id', $V(this.forms.edit.elements['sejour_id']));
    url.addParam('uf_type', 'soins');
    url.autoComplete(this.forms.edit.elements['_uf_soins_view'], 'keyword', {
      minChars: 0,
      dropdown: true,
      callback: function(input, query) {
        var form = DHE.sejour.forms.edit;
        if ($V(input)) {
          query += '&keyword=' + $V(input);
        }
        if (form.elements['service_id'] && $V(form.elements['service_id'])) {
          query += '&service_id=' + $V(form.elements['service_id']);
        }
        if ($V(form.elements['praticien_id'])) {
          query += '&praticien_id=' + $V(form.elements['praticien_id']);
        }
        if ($V(form.elements['type'])) {
          query += '&sejour_type=' + $V(form.elements['type']);
        }
        if ($V(form.elements['entree_prevue'])) {
          query += '&entree=' + $V(form.elements['entree_prevue']);
        }
        if ($V(form.elements['sortie_prevue'])) {
          query += '&sortie=' + $V(form.elements['sortie_prevue']);
        }

        return query;
      },
      afterUpdateElement: function(field, selected) {
        $V(field, selected.readAttribute('data-view'));
        $V(DHE.sejour.forms.edit.elements['uf_soins_id'], selected.readAttribute('data-id'));
      }
    });

    /* UF medicale */
    url = new Url('hospi', 'ajax_autocomplete_uf');
    url.addParam('sejour_id', $V(this.forms.edit.elements['sejour_id']));
    url.addParam('uf_type', 'medicale');
    url.autoComplete(this.forms.edit.elements['_uf_medicale_view'], 'keyword', {
      minChars: 0,
      dropdown: true,
      callback: function(input, query) {
        var form = DHE.sejour.forms.edit;
        if ($V(input)) {
          query += '&keyword=' + $V(input);
        }
        if (form.elements['service_id'] && $V(form.elements['service_id'])) {
          query += '&service_id=' + $V(form.elements['service_id']);
        }
        if ($V(form.elements['praticien_id'])) {
          query += '&praticien_id=' + $V(form.elements['praticien_id']);
        }
        if ($V(form.elements['type'])) {
          query += '&sejour_type=' + $V(form.elements['type']);
        }
        if ($V(form.elements['entree_prevue'])) {
          query += '&entree=' + $V(form.elements['entree_prevue']);
        }
        if ($V(form.elements['sortie_prevue'])) {
          query += '&sortie=' + $V(form.elements['sortie_prevue']);
        }

        return query;
      },
      afterUpdateElement: function(field, selected) {
        $V(field, selected.readAttribute('data-view'));
        $V(DHE.sejour.forms.edit.elements['uf_medicale_id'], selected.readAttribute('data-id'));
      }
    });

    /* Summary view autocomplete */

    /* Chir */
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

        $V(field.form.elements['praticien_id'], selected.getAttribute('id').split('-')[2]);
      }
    });
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

aProtocoles = {};
