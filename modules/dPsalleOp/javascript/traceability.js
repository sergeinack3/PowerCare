Traceability = {
  /**
   * Load all checklists
   */
  loadChecklists: function (page) {
    page = (page) ? page : 0;
    new Url("salleOp", "vw_daily_check_traceability")
      .addParam('start', page)
      .requestUpdate("checklist");
  },

  /**
   * Load all checklists
   */
  filterChecklists: function (form) {
    new Url("salleOp", "vw_daily_check_traceability")
      .addParam('_date_min', $V(form._date_min))
      .addParam('_date_max', $V(form._date_max))
      .addParam('object_guid', $V(form.object_guid))
      .addParam('_type', $V(form._type))
      .addParam('type', $V(form.type))
      .addParam('start', 0)
      .requestUpdate("checklist");
  },

  /**
   * Load a checklist
   * @param checklist_id
   */
  loadChecklist: function (checklist_id) {
    new Url("salleOp", "vw_daily_check_traceability")
      .addParam('check_list_id', checklist_id)
      .requestUpdate("checklist");
  },

  loadRadiologie: function () {
    new Url("salleOp", "vw_radiologie")
      .requestUpdate("radiologie");
  },

  viewRadiologieList: function (form, exporte) {
    if (!$V(form.search_protocole)) {
      $V(form.protocole_id, "");
    }

    var url = new Url("salleOp", "ajax_list_operations_radiologie");
    url.addFormData(form);
    url.addNotNullParam("salle_ids[]", $V(form.salle_ids), true);
    url.addNotNullParam("ampli_ids[]", $V(form.ampli_ids), true);


    if (exporte) {
      url.addParam("export", "csv");
      url.addParam("suppressHeaders", 1);
      url.open();
    } else {
      url.requestUpdate("result_search_interv")
    }

    return false;
  },

  changePageRadio: function (page) {
    var form = getForm("listFilterInterv");
    $V(form.page, page);
    this.viewRadiologieList(form);
  },

  changeFilterRadio: function (order_col, order_way) {
    var form = getForm("listFilterInterv");
    $V(form.order_col, order_col);
    $V(form.order_way, order_way);
    this.viewRadiologieList(form);
  },

  deleteCCAMCode: function (code, form) {
    var elts = $$('span.ccam_' + code);
    if (elts.length > 0) {
      elts[0].remove();
    }

    var form = getForm('listFilterInterv');
    var codes = $V(form.ccam_codes).split('|');
    codes.splice(codes.indexOf(code), 1);
    $V(form.ccam_codes, codes.join('|'));
  },

  addCCAMCode: function (code, form) {
    var elts = $('display_ccam_codes');
    var elt_code = '<span class="circled ccam_' + code.readAttribute('data-code') + '">' +
      code.readAttribute('data-code')
      + '<span style="margin-left: 5px; cursor: pointer;" onclick="Traceability.deleteCCAMCode(\''
      + code.readAttribute('data-code') + '\')" title="{{tr}}Delete{{/tr}}"><i class="fa fa-times"></i></span>' +
      '</span>';
    elts.insert(elt_code);

    var codes = $V(form.ccam_codes).split('|');
    codes.push(code.readAttribute('data-code'));
    $V(form.ccam_codes, codes.join('|'));
  }
};
