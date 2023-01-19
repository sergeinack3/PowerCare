/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

AccidentTravail = {
  form: null,
  uid: null,

  /**
   * Initialize form
   *
   * @param form
   * @param uid
   */
  initialize: function(form, uid) {
    this.form = form;
    this.uid = uid;
    this.setNotNull(this.form.elements['nature']);
    this.syncField(this.form.elements['type']);
    this.syncField(this.form.elements['nature']);
    this.syncField(this.form.elements['feuille_at']);
    this.syncField(this.form.elements['consequences']);
  },
  /**
   * Display view
   *
   * @param view
   * @param input
   */
  displayView: function(view, input) {
    if (view != '') {
      $$('div.at_view').invoke('hide');
      $(view).show();
    }

    if (input) {
      $V(input, '', false);
    }
  },
  /**
   * Load the work accident
   *
   * @param consult_id
   * @param sejour_id
   * @param object_class
   */
  loadAccidentTravail: function (consult_id, sejour_id, object_class) {
    new Url('cabinet', 'ajax_accident_travail')
      .addParam('consult_id', consult_id)
      .addParam('sejour_id', sejour_id)
      .addParam('object_class', object_class)
      .requestUpdate('accident_travail_mp');
  },
  /**
   * Edit the work accident
   *
   * @param accident_travail_id
   * @param consult_id
   * @param sejour_id
   * @param object_class
   */
  editAccidentTravail: function (accident_travail_id, consult_id, sejour_id, object_class) {
    new Url('cabinet', 'ajax_edit_accident_travail')
      .addParam('accident_travail_id', accident_travail_id)
      .addParam('consult_id', consult_id)
      .addParam('sejour_id', sejour_id)
      .addParam('object_class', object_class)
      .requestModal('60%', '95%', {
        onClose: function () {
          if ($('accident_travail_mp')) {
            AccidentTravail.loadAccidentTravail(consult_id, sejour_id, object_class);
          }
        }
      });
  },
  /**
   * Update the max duration
   */
  updateMaxDuree: function () {
    var form = this.form ? this.form : getForm('editAccidentTravail');
    var unite = $V(form.unite_duree);
    switch (unite) {
      case 'j':
        form.duree.max = 1092;
        break;
      case 'm':
        form.duree.max = 36;
        break;
      case 'a':
        form.duree.max = 3;
        break;
      default:
    }
  },
  /**
   * Update the end date
   */
  updateEndDate: function () {
    var form = this.form ? this.form : getForm('editAccidentTravail');
    var begin_date = $V(form.date_debut_arret);
    var unite_duree = $V(form.unite_duree);
    var duree_input = parseInt($V(form.duree));
    var end_date = new Date(begin_date);

    switch (unite_duree) {
      case 'j':
        duree_input === 0 ? duree_input = 0 : duree_input -= 1;
        end_date.setDate(end_date.getDate() + duree_input);
        break;
      case 'm':
        end_date.setMonth(end_date.getMonth() + duree_input);
        break;
      case 'a':
        end_date.setFullYear(end_date.getFullYear() + duree_input);
        break;
    }

    var month = end_date.getMonth() + 1 + '';
    if (month.length == 1) {
      month = "0" + month;
    }
    var day = end_date.getDate() + "";
    if (day.length == 1) {
      day = "0" + day;
    }

    $V(form.date_fin_arret, end_date.getFullYear() + '-' + month + '-' + day);
    $V(form.date_fin_arret_da, day + '/' + month + '/' + end_date.getFullYear());

    AccidentTravail.syncField(form.elements['duree']);
    AccidentTravail.syncField(form.elements['unite_duree']);
    AccidentTravail.syncField(form.elements['date_fin_arret']);
  },
  /**
   * Check the authorized exits
   */
  checkSortiesAutorisees: function(form) {
    if ($V(form.sorties_autorisees) == '1') {
      $('sorties_autorisees').show();
    }
    else {
      $('sorties_autorisees').hide();
      $V(form.elements['sorties_restriction'], '0');
      form.elements['__sorties_restriction'].checked = false;
      $V(form.elements['sorties_sans_restriction'], '0');
      form.elements['__sorties_sans_restriction'].checked = false;
      $V(form.elements['motif_sortie_sans_restriction'], '');
      $V(form.elements['date_sortie_sans_restriction'], '', false);
      $V(form.elements['date_sortie_sans_restriction_da'], '');
      $V(form.elements['date_sortie'], '', false);
      $V(form.elements['date_sortie_da'], '');
    }
  },
  /**
   * Check the type of authorized exits
   *
   * @param input
   * @param type
   */
  checkSortiesAutoriseesType: function(input, type) {
    var form = input.form;
    if ($V(input) == '1') {
      $('sorties_autorisees_' + type).show();
      if (type == 'restriction') {
        this.setNotNull(form.elements['date_sortie']);
        $V(form.elements['date_sortie'], $V(form.elements['date_constatations']), false);
        $V(form.elements['date_sortie_da'], $V(form.elements['date_constatations_da']));
        /* Fire the keyup event for update the notNull status of the field */
        form.elements['date_sortie'].dispatchEvent(new Event('keyup'));

        // remove value without restriction
        $V(form.elements['sorties_sans_restriction'], '0');
        form.elements['__sorties_sans_restriction'].checked = false;
        this.setNull(form.elements['date_sortie_sans_restriction']);
        this.setNull(form.elements['motif_sortie_sans_restriction']);
        $V(form.elements['date_sortie_sans_restriction'], '');
        $V(form.elements['date_sortie_sans_restriction_da'], '');
      }
      else {
        this.setNotNull(form.elements['date_sortie_sans_restriction']);
        this.setNotNull(form.elements['motif_sortie_sans_restriction']);
        $V(form.elements['date_sortie_sans_restriction'], $V(form.elements['date_constatations']), false);
        $V(form.elements['date_sortie_sans_restriction_da'], $V(form.elements['date_constatations_da']));
        /* Fire the keyup event for update the notNull status of the field */
        form.elements['date_sortie_sans_restriction'].dispatchEvent(new Event('keyup'));

        // remove value restriction
        $V(form.elements['sorties_restriction'], '0');
        form.elements['__sorties_restriction'].checked = false;
        this.setNull(form.elements['date_sortie']);
        $V(form.elements['date_sortie'], '');
        $V(form.elements['date_sortie_da'], '');
      }
    }
    else {
      $('sorties_autorisees_' + type).hide();
      if (type == 'restriction') {
        this.setNull(form.elements['date_sortie']);
        $V(form.elements['date_sortie'], '');
        $V(form.elements['date_sortie_da'], '');
      }
      else {
        this.setNull(form.elements['date_sortie_sans_restriction']);
        this.setNull(form.elements['motif_sortie_sans_restriction']);
        $V(form.elements['date_sortie_sans_restriction'], '');
        $V(form.elements['date_sortie_sans_restriction_da'], '');
      }
    }
  },

  checkDateSortie: function(input, type) {
    var form = input.form;
    var date = $V(input);
    var begin = $V(form.elements['date_constatations']);
    var end = $V(form.elements['date_fin_arret']);

    if (date < begin) {
      $V(input, $V(form.elements['date_constatations']), false);
      $V(form.elements[input.name + '_da'], $V(form.elements['date_constatations_da']));
    }
    else if (date > end) {
      $V(input, $V(form.elements['date_fin_arret']), false);
      $V(form.elements[input.name + '_da'], $V(form.elements['date_fin_arret_da']));
    }

    if (type == 'sans_restriction' && $V(form.elements['sorties']) == '1') {
      var date_sortie = $V(form.elements['date_sortie']);
      if (date < date_sortie) {
        $V(input, $V(form.elements['date_sortie']), false);
        $V(form.elements[input.name + '_da'], $V(form.elements['date_sortie_da']));
      }
    }
    else if (type == 'restriction' && $V(form.elements['sorties_sans_restriction']) == '1') {
      var date_sortie = $V(form.elements['sorties_sans_restriction_date']);
      if (date_sortie < date) {
        $V(form.elements['date_sortie_sans_restriction'], $V(input), false);
        $V(form.elements['date_sortie_sans_restriction_da'], $V(form.elements[input.name + '_da']));
      }
    }
  },
  /**
   * Set the given field to a not null field
   *
   * @param field The field
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
   * @param field The field
   */
  setNull: function(field) {
    field.removeClassName('notNull');
    if (field.getLabel()) {
      field.getLabel().removeClassName('notNull').removeClassName('notNullOK');
    }
    field.observe('change', Prototype.emptyFunction).observe('keyup', Prototype.emptyFunction).observe('ui:change', Prototype.emptyFunction);
  },
  /**
   * Set the given field to a null field
   *
   * @param form
   * @param object_class
   * @param object_id
   */
  saveAndOpenCerfa: function(form, object_class, object_id) {
    onSubmitFormAjax(this.form ? this.form : form, {onComplete: function () {
      Control.Modal.close();
        Cerfa.editCerfa('11138-06', object_class, object_id);
      }});
  },
  /**
   *
   */
  confirmSaving: function() {
    if (checkForm(this.form)) {
      this.form.onsubmit();
    }
  },
  /**
   *
   */
  confirmPrinting: function() {
    if ($V(this.form.elements['ald_temps_complet']) == '1' && $V(this.form.elements['maternite']) == '1') {
      Modal.alert($T('CAvisArretTravail-error-legal-maternite'));
    }
    else if (checkForm(this.form)) {
      this.print();
    }
  },
  /**
   * Check the visit address
   *
   * @param input
   */
  checkVisitAddress: function(input) {
    if ($V(input) == '1') {
      $('AAT_visit_address_part' + this.uid).show();
      this.setNotNull(this.form.elements['patient_visite_adresse']);
      this.setNotNull(this.form.elements['patient_visite_code_postal']);
      this.setNotNull(this.form.elements['patient_visite_ville']);
    }
    else {
      $('AAT_visit_address_part' + this.uid).hide();
      $$('AAT_visit_address_part' + this.uid + ' input').each(function(input) {
        $V(input, '');
      });
      this.setNull(this.form.elements['patient_visite_adresse']);
      this.setNull(this.form.elements['patient_visite_code_postal']);
      this.setNull(this.form.elements['patient_visite_ville']);
    }
  },
  /**
   * Affiche ou cache la partie Prescripteur selon le type de l'arrêt
   *
   * @param input
   */
  checkType: function(input) {
    this.syncField(input);
  },

  syncField: function(input) {
    var element = this.form.elements['summary-' + input.name];
    var row = $('at_summary-' + input.name + this.uid);

    if (input.tagName.toLowerCase() == 'select') {
      var name = input.name == 'unite_duree' ? '_unite_duree' : input.name;
      $V(element, $T('CAccidentTravail.' + name + '.' + $V(input)));
    }
    else if (input.type == 'hidden' && $V(input) == '1') {
      $V(element, $T('bool.1'));
    }
    else if (input.hasClassName('date')) {
      $V(element, $V(this.form.elements[input.name + '_da']));
    }
    else {
      $V(element, $V(input));
    }

    if (row) {
      if ($V(input) != '' || $V(input) != '0') {
        row.show();
      }
      else {
        row.hide();
      }
    }
  },
};
