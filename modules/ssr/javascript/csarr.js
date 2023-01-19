/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CsARR = {
  onSelectCode: Prototype.emptyFunction,
  modulateur_codes: [],

  viewActivite: function(code) {
    new Url('ssr', 'vw_activite_csarr')
      .addParam('code', code)
      .requestModal(600);
  },
  
  viewActiviteStats: function(code) {
    new Url('ssr', 'vw_activite_csarr_stats')
      .addParam('code', code)
      .requestModal();
  },
  
  viewHierarchie: function(code) {
    new Url('ssr', 'vw_hierarchie_csarr')
      .addParam('code', code) .
      requestModal(600);
  },

  /**
   * Display the search modal
   *
   * @param {Function} callback       The callback for selecting a code
   * @param {Number=}  chir_id        The user id (for displaying its favori)
   * @param {String=}  object_class   The object class
   * @param {Number=}  object_id      The object id
   */
  viewSearch: function(callback, chir_id, object_class, object_id) {
    this.onSelectCode = callback;
    var url = new Url('ssr', 'view_search_csarr');

    if (chir_id) {
      url.addParam('chir_id', chir_id);
    }
    if (object_class) {
      url.addParam('object_class', object_class);
    }
    if (object_id) {
      url.addParam('object_id', object_id);
    }

    url.requestModal(900, 800, {onClose: CsARR.onCloseSearchModal.bind(CsARR)});
  },

  /**
   * Functions of the search view
   */

  /**
   * Creates the Control Tabs and initialize the scroll overflow for the results
   *
   * @param tab_id
   */
  initializeViewSearch: function(tab_id) {
    if ($(tab_id)) {
      this.search_tab = Control.Tabs.create(tab_id, true);
    }

    if ($('search-csarr-results')) {
      ViewPort.SetAvlHeight($('search-csarr-results'), 1.00);
    }
  },

  /**
   * Search the CsARR database
   *
   * @param {HTMLFormElement} form The form
   */
  search: function(form, object_class, object_id, hide_selector) {
    new Url('ssr', 'ajax_search_csarr')
      .addParam('code', $V(form.elements['code']))
      .addParam('keywords', $V(form.elements['keywords']))
      .addParam('hierarchy_1', $V(form.elements['hierarchy_1']))
      .addParam('hierarchy_2', $V(form.elements['hierarchy_2']))
      .addParam('hierarchy_3', $V(form.elements['hierarchy_3']))
      .addParam('object_class', object_class)
      .addParam('object_id', object_id)
      .addParam('hide_selector', hide_selector)
      .requestUpdate('search-csarr-results');
  },

  refreshHierarchySelector: function(input, level) {
    new Url('ssr', 'ajax_refresh_hierarchy_selector')
      .addParam('code', $V(input))
      .addParam('level', level)
      .requestUpdate('searchCsARR-hierarchy_' + level + '-placeholder');
  },

  /**
   * Execute the callback specified for selecting the code
   *
   * @param {String} code The code
   */
  selectCode: function(code) {
    this.onSelectCode(code);

    if (code && getForm('editCsarr')) {
      $V(getForm('editCsarr').modulateurs, "");
      $V(getForm('editCsarr').type_seance, "");
      CsARR.modulateur_codes = [];
      CsARR.refreshModulateurs(code);
    }

    Control.Modal.close();
  },

  /**
   * Empty the callback for the code selection
   */
  onCloseSearchModal: function() {
    this.onSelectCode = Prototype.emptyFunction;
  },

  /**
   * Functions for handling favorites
   */

  /**
   * Creates the favorite
   *
   * @param {String}  code The code
   * @param {String=} uid  An uid
   *
   * @returns {Boolean}
   */
  addCodeToFavorite: function(code, uid) {
    var form = getForm('editFavoriCsARR-' + code + uid);
    if (form) {
      Form.onSubmitComplete = CsARR.updateFavoriForm.bind(CsARR, form);
      return onSubmitFormAjax(form);
    }
  },

  /**
   * Deletes the favorite
   *
   * @param {String}  code The code
   * @param {String=} uid  An uid
   *
   * @returns {Boolean}
   */
  deleteCodeFromFavorite: function(code, uid) {
    var form = getForm('editFavoriCsARR-' + code + uid);
    if (form) {
      $V(form.elements['del'], '1');
      Form.onSubmitComplete = CsARR.updateFavoriForm.bind(CsARR, form);
      return onSubmitFormAjax(form);
    }
  },

  /**
   * Update the favori form with the data returned by the form submission
   *
   * @param {HTMLFormElement} form   The form
   * @param {String}          guid   The guid of the object
   * @param {Object}          object The favori data
   */
  updateFavoriForm: function(form, guid, object) {
    if (object.favori_csarr_id) {
      $V(form.elements['favori_csarr_id'], object.favori_csarr_id);
      $V(form.elements['code'], object.code);
      $V(form.elements['user_id'], object.user_id);
      $V(form.elements['del'], '0');
      $(form.name + '-add').hide();
      $(form.name + '-del').show();
    }
    else {
      $V(form.elements['favori_csarr_id'], '');
      $V(form.elements['del'], '0');
      $(form.name + '-add').show();
      $(form.name + '-del').hide();
    }

    Form.onSubmitComplete = Prototype.emptyFunction;
  },
  /**
   * Refresh the modulator list according to the code
   *
   * @param code Csarr code
   */
  refreshModulateurs: function (code) {
    new Url('ssr', 'ajax_vw_activite_csarr')
      .addParam('code', code)
      .requestUpdate('modulateurs_csarr');
  },
  /**
   * Get the code CsARR list
   *
   * @param form
   */
  codeCsARRAutocomplete: function (form) {
    new Url("ssr", "httpreq_do_csarr_autocomplete")
      .autoComplete(form.code, null, {
        dropdown: true,
        minChars: 2,
        select:   "value",
        width:    "200px",
        afterUpdateElement: function(field, selected) {
          $V(form.modulateurs, "");
          $V(form.type_seance, "");
          $V(form.code, "");
          CsARR.modulateur_codes = [];

          var code_csarr = selected.down('strong').innerText;
          code_csarr = code_csarr.replace(/\r\n/g, "");
          $V(form.code, code_csarr);
          CsARR.refreshModulateurs(code_csarr);

          if (code_csarr) {
            $('labelFor_editCsarr_code').removeClassName('notNull');
            $('labelFor_editCsarr_code').addClassName('notNullOK');
          }
          else {
            $('labelFor_editCsarr_code').removeClassName('notNullOK');
            $('labelFor_editCsarr_code').addClassName('notNull');
          }
        }
      });
  },
  /**
   * Select Modulator values
   */
  addModulateurValues: function (form, elt) {
    if (elt.checked) {
      this.modulateur_codes.push(elt.value);
    }
    else {
      this.modulateur_codes.splice(this.modulateur_codes.indexOf(elt.value), 1);
    }

    $V(form.modulateurs, this.modulateur_codes.flatten().join("|"));
  },
};
