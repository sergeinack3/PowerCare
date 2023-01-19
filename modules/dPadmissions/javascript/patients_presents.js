var PatientsPresents = {
  url: null,

  /**
   * Reload global (cf. button in filters) patient list
   */
  vueGlobalePresent: function() {
    var form = getForm("selType");
    var url = new Url("admissions", "vw_presents_by_services");
    url.addParam("type", $V(form._type_admission));
    url.requestModal('90%', null);
    PatientsPresents.url = url;
  },

  /**
   * Reload global (cf. button in filters) patient list with filters
   *
   * @param {str} date - a date
   */
  vuedateGlobalePresent: function(date) {
    var form = getForm("selType");
    var url = new Url("admissions", "vw_presents_by_services");
    url.addParam("type", $V(form._type_admission));
    url.addParam("date", date);
    url.requestUpdate(PatientsPresents.url.modalObject.container.down('.content'));
  },

  /**
   * Prints the planning
   */
  printPlanning: function() {
    var oForm = getForm("selType");
    var url = new Url("admissions", "print_entrees");
    url.addParam("date", $('admissions').dataset.date);
    url.addParam("type", $V(oForm._type_admission));
    url.addParam("service_id", [$V(oForm.service_id)].flatten().join(","));
    url.popup(700, 550, "Entrees");
  },

  /**
   * Add params to an Url object
   *
   * @param {Url} url - the object to reload lists
   * @param form
   */
  commonParams: function(url, form) {
    url.addParam("type", $V(form._type_admission));
    url.addParam("service_id", [$V(form.service_id)].flatten().join(","));
    url.addParam("prat_id", $V(form.prat_id));
    url.addParam("active_filter_services", $V(form.elements['active_filter_services']));
    url.addParam("only_entree_reelle", form.only_entree_reelle.checked ? 1 : 0);
    url.addParam("type_pec[]", $V(form.elements["type_pec[]"]), true);
  },

  /**
   * Reload the list of patients (left column included)
   */
  reloadFullPresents: function() {
    var oForm = getForm("selType");
    var url = new Url("admissions", "httpreq_vw_all_presents");
    PatientsPresents.commonParams(url, oForm);
    url.addParam('date', $('admissions').dataset.date);
    url.requestUpdate('allPresents');
    PatientsPresents.reloadPresent();
  },

  /**
   * Reload the list of patients (not the left column)
   */
  reloadPresent: function(page = 0) {
    var oForm = getForm("selType");
    var url = new Url("admissions", "httpreq_vw_presents");
    PatientsPresents.commonParams(url, oForm);
    url.addParam("filterFunction", $V(getForm("filterFunctionForm").filterFunction));
    url.addParam("date", $('admissions').dataset.date);
    url.addParam("page", page);
    url.requestUpdate('listPresents');
  },

  /**
   * Submit an admission as "prepared"
   *
   * @param {HTMLElement} oForm - an HTML element
   * @param {boolean} bPassCheck - an argument
   *
   * @returns {boolean}
   */
  submitAdmission: function(oForm, bPassCheck) {
    var oIPPForm = null;
    var oNumDosForm = null;
    if (getForm('editIPP' + $V(oForm.patient_id)) !== undefined) {
      oIPPForm = getForm("editIPP" + $V(oForm.patient_id));
    }
    if (getForm('editNumdos' + $V(oForm.patient_id)) !== undefined) {
      oNumDosForm = getForm("editNumdos" + $V(oForm.sejour_id));
    }

    if (oIPPForm || oNumDosForm) {
      if (!bPassCheck && oIPPForm && oNumDosForm && (!$V(oIPPForm.id400) || !$V(oNumDosForm.id400))) {
        Idex.edit_manually(
          $V(oNumDosForm.object_class) + "-" + $V(oNumDosForm.object_id),
          $V(oIPPForm.object_class) + "-" + $V(oIPPForm.object_id),
          this.reloadPresent.curry()
        );
      } else {
        return onSubmitFormAjax(oForm, this.reloadPresent);
      }
    }
    else {
      return onSubmitFormAjax(oForm, this.reloadPresent);
    }
  },

  /**
   * Submit all existing patients (multiple)
   *
   * @param form - an HTML element
   *
   * @returns {Boolean} - often false to avoid the default behaviour
   */
  submitMultiple: function(form) {
    return onSubmitFormAjax(form, PatientsPresents.reloadFullPresents);
  },

  /**
   * Prints table of present patients by services
   */
  printGlobalPresents: function() {
    if($('presents_table')) {
      $('presents_table').print();
    }
  },

  changePageSejours: function (page) {
    PatientsPresents.reloadPresent(page);
  }
};
