/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CIM = {
  quick_search_form: null,
  search_tab: null,
  onSelectCode: Prototype.emptyFunction,

  /**
   * Initialize an autocompleter for the Cim10 codes
   *
   * @param {HTMLInputElement,String} input    The input to autocomplete
   * @param {HTMLElement,String}      populate The element which will receive the response list
   * @param {Object=}                 options  Various options :
   *   limit_favoris        : Limit the search to the favorites of the connected user and chir
   *   chir_id              : Specify a user for the search limited to the favorites
   *   sejour_id            : Specify a sejour id
   *   sejour_type          : The type of sejour (mco, ssr ou psy) for checking if the code is authorized or not
   *   field_type           : The type of field (dp, dr, da, fppec, mmp, ae, das) for checking if the code is authorized or not
   */
  autocomplete : function(input, populate, options) {
    var url = new Url('cim10', 'ajax_code_cim10_autocomplete');
    options = Object.extend(
      {
        minChars: 1,
        dropdown: true,
        select: 'code',
        width: '250px',
        limit_favoris: 0,
        chir_id: 0,
        sejour_id: 0,
        sejour_type: null,
        field_type: null
      },
      options
    );

    if (input.name != 'keywords_code') {
      options.paramName = 'keywords_code';
    }

    if (options.limit_favoris == '1') {
      url.addParam('limit_favoris', '1');
    }

    if (options.chir_id) {
      url.addParam('chir_id', options.chir_id);
    }

    if (options.sejour_id) {
      url.addParam('sejour_id', options.sejour_id);
    }

    if (options.sejour_type) {
      if (options.sejour_type != 'mco' && options.sejour_type != 'ssr' && options.sejour_type != 'psy') {
        options.sejour_type = 'mco';
      }
      url.addParam('sejour_type', options.sejour_type);
    }

    if (options.field_type) {
      url.addParam('field_type', options.field_type);
    }

    url.autoComplete(input, null, options);
  },

  /**
   * Display the search modal
   *
   * @param {Function} callback     The callback when selecting a code
   * @param {Number=}  chir_id      The chir id (for displaying its favori)
   * @param {Number=}  anesth_id    The anesth id (for displaying its favori)
   * @param {String=}  object_class The object class
   * @param {String=}  sejour_type  The type of sejour (mco, ssr ou psy) for checking if the code is authorized or not
   * @param {String=}  field_type   The type of field (dp, dr, da, fppec, mmp, ae, das) for checking if the code is authorized or not
   */
  viewSearch: function(callback, chir_id, anesth_id, object_class, object_id, sejour_type, field_type) {
    this.onSelectCode = callback;
    var url = new Url('cim10', 'view_search_cim');

    if (chir_id) {
      url.addParam('chir_id', chir_id);
    }
    if (anesth_id) {
      url.addParam('anesth_id', anesth_id);
    }
    if (object_class) {
      url.addParam('object_class', object_class);
    }
    if (object_id) {
      url.addParam('object_id', object_id);
    }

    if (sejour_type) {
      if (typeof sejour_type == "function") {
        sejour_type = sejour_type();
      }

      if (sejour_type != 'mco' && sejour_type != 'ssr' && sejour_type != 'psy') {
        sejour_type = 'mco';
      }
      url.addParam('sejour_type', sejour_type);
    }

    if (field_type) {
      url.addParam('field_type', field_type);
    }

    url.requestModal(900, 800, {onClose: CIM.onCloseSearchModal.bind(CIM)});
  },

  /**
   * Display a code in the Cim10 view
   *
   * @param {String}    code    The code to display
   * @param {Function=} onClose A function to execute when the modal is closed
   */
  showCodeModal: function(code, onClose) {
    if (!onClose) {
      onClose = Prototype.emptyFunction;
    }

    var url = new Url('cim10', 'ajax_code_cim');
    url.addParam('code', code);
    url.addParam('modal', 1);
    url.requestModal('1000px', '500px', {onClose: onClose});
  },

  /**
   * Displays the diagnosis used on old sejour
   *
   * @param {String}    object_guid An object guid
   * @param {Function=} onClose     A function to execute when the modal is closed
   */
  showAnciensDiags: function(object_guid, onClose) {
    if (!onClose) {
      onClose = Prototype.emptyFunction;
    }

    new Url('cim10', 'anciens_diagnostics')
      .addParam('object_guid', object_guid)
      .requestModal('auto', 400, {onClose: onClose});
  },

  /**
   * Open the favoris view
   */
  manageFavoris: function() {
    var url = new Url('cim10', 'ajax_favoris');
    url.requestModal();
  },

  /**
   * Open the favoris view
   */
  reloadFavoris: function() {
    var url = new Url('cim10', 'ajax_favoris');
    url.addParam('reload', '1')
    url.requestUpdate('favoris_user_view');
  },

  /**
   * Functions related to the main CIM10 view
   */

  /**
   * Initialize the Cim10 view
   */
  initializeView: function() {
    if (getForm('quickSearchCim')) {
      this.quick_search_form = getForm('quickSearchCim');
      this.autocomplete(
        this.quick_search_form.elements['keywords_code'],
        null,
        {
          width: '400px',
          dropdown: false,
          afterUpdateElement: function(field) {
            this.showCode($V(field));
            this.clearQuickSearch();
          }.bind(this)
        }
      );
    }

    if ($('summary_placeholder') && $('cim10_details_placeholder')) {
      var dimensions = document.viewport.getDimensions();
      var summary = $('summary_cim');
      var details = $('cim10_details_placeholder').down('fieldset');
      summary.setStyle({height: dimensions.height - summary.cumulativeOffset().top - 10 + 'px'});
      details.setStyle(
        {
          height: dimensions.height - details.cumulativeOffset().top - 10 + 'px',
          width: dimensions.width - details.cumulativeOffset().left - 25 + 'px',
        }
      );
    }
  },

  /**
   * Display a code in the Cim10 view
   *
   * @param {String} code The code to display
   */
  showCode: function(code) {
    if ($('cim10_details')) {
      var url = new Url('cim10', 'ajax_code_cim');
      url.addParam('code', code);
      url.requestUpdate('cim10_details');
    }
    else {
      this.showCodeModal(code);
    }
  },

  /**
   *
   * @param code
   * @param element
   */
  foldChapter: function(code, element) {
    var i = element.down('i');
    if (i.hasClassName('fa-caret-square-right')) {
      i.removeClassName('fa-caret-square-right');
      i.addClassName('fa-caret-square-down');
      $('categories-' + code).show();
    }
    else {
      i.removeClassName('fa-caret-square-down');
      i.addClassName('fa-caret-square-right');
      $('categories-' + code).hide();
    }
  },

  clearQuickSearch: function() {
    $V(this.quick_search_form.elements['keywords_code'], '');
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
    var form = getForm('editFavoriCIM-' + code + uid);
    if (form) {
      Form.onSubmitComplete = CIM.updateFavoriForm.bind(CIM, form);
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
    var form = getForm('editFavoriCIM-' + code + uid);
    if (form) {
      $V(form.elements['del'], '1');
      Form.onSubmitComplete = CIM.updateFavoriForm.bind(CIM, form);
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
    if (object.favoris_id) {
      $V(form.elements['favoris_id'], object.favoris_id);
      $V(form.elements['favoris_code'], object.favoris_code);
      $V(form.elements['favoris_user'], object.favoris_user);
      $V(form.elements['del'], '0');
      $(form.name + '-add').hide();
      $(form.name + '-del').show();
    }
    else {
      $V(form.elements['favoris_id'], '');
      $V(form.elements['del'], '0');
      $(form.name + '-add').show();
      $(form.name + '-del').hide();
    }

    Form.onSubmitComplete = Prototype.emptyFunction;
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

    if ($('search-cim-results')) {
      ViewPort.SetAvlHeight($('search-cim-results'), 1.00);
    }
  },

  /**
   * Search the CIM
   *
   * @param {HTMLFormElement} form The form
   */
  search: function(form, object_class, object_id, target) {
    var url = new Url('cim10', 'ajax_search_cim');
    url.addParam('code', $V(form.elements['code']));
    url.addParam('keywords', $V(form.elements['keywords']));
    url.addParam('chapter', $V(form.elements['chapter']));
    url.addParam('category', $V(form.elements['category']));
    url.addParam('ged', $V(form.elements['ged']));

    if (object_class) {
      url.addParam('object_class', object_class);
    }

    if (object_id) {
      url.addParam('object_id', object_id);
    }

    if ($V(form.elements['sejour_type']) != '') {
      url.addParam('sejour_type', $V(form.elements['sejour_type']));
    }

    if ($V(form.elements['field_type']) != '') {
      url.addParam('field_type', $V(form.elements['field_type']));
    }

    if (form.elements['tag_id']) {
      url.addParam('tag_id', $V(form.elements['tag_id']));
    }

    if (form.elements['user_id']) {
      url.addParam('user_id', $V(form.elements['user_id']));
    }

    url.requestUpdate(target);
  },

  /**
   * Execute the callback
   *
   * @param {String} code The code
   */
  selectCode: function(code) {
    this.onSelectCode(code);
    Control.Modal.close();
  },

  /**
   * Empty the callback for the code selection
   */
  onCloseSearchModal: function() {
    this.onSelectCode = Prototype.emptyFunction;
  },

  /**
   * Refresh the categories for the selected chapter
   *
   * @param {HTMLInputElement} input
   */
  refreshCategories: function(input) {
    var url = new Url('cim10', 'ajax_refresh_cim_categories');
    url.addParam('chapter_code', input.down('option:checked').get('code'));
    url.requestUpdate(input.form.name + '-categories-placeholder');
  }
};
