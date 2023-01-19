Stats = {
  form: null,

  init: function (form_name) {
    this.form = getForm(form_name);

    // Events
    this.chooseBasedFilterChange();
    this.computeButtonClick();
    this.downloadCsvClick();
  },

  /**
   * Using the form, gets the csv generated file
   */
  getSpreadSheet: function () {
    var compute = this.form.doctors.value;

    var url = new Url('cabinet', 'ajax_stats_medecins_correspondants')
      .addParam('_function_id', this.form._function_id.value)
      .addParam('_user_id', this.form._user_id.value)
      .addParam('compute_mode', (compute === 'gp') ? 'correspondants' : 'adresse_par');

    if (this.form.doctors.value === 'addressed') {
      url.addParam('_date_min', this.form._date_min.value)
        .addParam('_date_max', this.form._date_max.value);
    }
    url.addParam('suppressHeaders', 1)
      .addParam('csv', 1);
    url.popup(550, 300, 'stats_correspondants');
  },

  /**
   * Checks if the dates are coherent
   *
   * @param {HTMLElement} elt - the field (input)
   */
  checkMaxPeriod: function(elt) {
    var form = elt.form;
    var date_min_elt = form._date_min;
    var date_max_elt = form._date_max;
    var date_min = new Date($V(date_min_elt));
    var date_max = new Date($V(date_max_elt));

    if (date_min.format("yyyy-MM-dd") > date_max.format("yyyy-MM-dd")) {
      return;
    }

    if (elt.name == "_date_min" ) {
      if (date_max.format("yy-MM-dd") > date_min.addDays(31).format("yy-MM-dd")) {
        $V(date_max_elt, date_min.format("yyyy-MM-dd"), false);
        $V(form._date_max_da, date_min.format("dd/MM/yyyy"), false);
      }
    }
    else if (date_min.format("yy-MM-dd") < date_max.addDays(-31).format("yy-MM-dd")) {
      $V(date_min_elt, date_max.format("yyyy-MM-dd"), false);
      $V(form._date_min_da, date_max.format("dd/MM/yyyy"), false);
    }
  },

  /**
   * Using the form, generates a table displayed
   */
  computeTable: function() {
    var compute = this.form.doctors.value;

    var url = new Url('cabinet', 'ajax_stats_medecins_correspondants')
      .addParam('_function_id', this.form._function_id.value)
      .addParam('_user_id', this.form._user_id.value)
      .addParam('compute_mode', (compute === 'gp') ? 'correspondants' : 'adresse_par');

    if (this.form.doctors.value === 'addressed') {
      url.addParam('_date_min', this.form._date_min.value)
         .addParam('_date_max', this.form._date_max.value);
    }

    url.requestUpdate('refresh_medecins_correspondants');
  },

  /**
   * Event which will show or hide the dates depending on the user's choice
   */
  chooseBasedFilterChange: function () {
    $$('select[name="doctors"]').invoke('observe', 'change', this._chooseBasedFilterChange.bind(this));
  },

  /**
   * Toggle between hiding or showing elements
   *
   * @param {Object} event - the event
   *
   * @private
   */
  _chooseBasedFilterChange: function (event) {
    var dates = $$('.addressed-dates');
    (event.target.value === "addressed") ? dates.invoke('show') : dates.invoke('hide');
  },

  /**
   * Event compute button click
   */
  computeButtonClick: function () {
    $$('button.corresponding-compute')[0].on('click', Stats.computeTable);
  },

  /**
   * Event download button click
   */
  downloadCsvClick: function () {
    $$('button.corresponding-download')[0].on('click', Stats.getSpreadSheet);
  }
};