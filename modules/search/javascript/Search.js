/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Search = window.Search || {
  words_request: null,
  export_csv:    null,
  isScrollDown:  false,

  /**
   * Method to display result from search
   *
   * @param {Element} form The form
   *
   * @return bool
   */
  displayResults: function (form) {
    document.getElementById('list_result_elastic').innerHTML = '';
    Search.isScrollDown = true;
    $V(form.elements.start, 0);
    var url = new Url('search', 'resultSearch');
    url.addFormData(form);
    url.requestUpdate('list_result_elastic', {
      onComplete: function () {
        Search.isScrollDown = false;
      }
    });
    return false;
  },

  scrollDownToPagination: function () {
    // scroll div
    var div = document.getElementById('list_result_elastic');
    Search.resizeListResul();
    div.onscroll = function (ev) {
      if (!Search.isScrollDown) {
        if ((div.offsetHeight + div.scrollTop) >= div.scrollHeight) {
          Search.isScrollDown = true;
          Search.changePage();
        }
      }
    };
  },

  changePage: function () {
    var form = getForm("esSearch");
    var stop = parseInt($V(form.elements.stop));
    var nbResult = parseInt($V(form.elements.nbResult));

    if (stop < nbResult) {
      var parent = document.getElementById("list_result_elastic");
      document.body.style.cursor = "wait";

      // set next position
      $V(form.elements.start, stop);

      var url = new Url('search', 'ajax_result_search');
      url.addFormData(form);
      url.requestHTML(function (html) {
        var btnChangePages = document.getElementsByClassName('btnChangePage');
        for (var pas = 0; pas < btnChangePages.length; pas++) {
          btnChangePages[pas].remove();
        }
        parent.insert(html);
        document.body.style.cursor = "auto";
        Search.isScrollDown = false;
      });
    }
  },

  startWithJson: function (form, json) {
    if (json) {
      document.getElementById('advanced').click();
      $V(form.elements["aggregate"], json.agregation);
      $V(form.elements["fuzzy"], json.fuzzy);
      $V(form.elements["words"], json.words);
      if (json.types) {
        var types = json.types.split("|");
        $V(form.elements["names_types[]"], types);
      }
      document.getElementById('button_search').click();
    }
  },

  startWithAggregate: function (html, guid) {
    if (document.getElementById('advanced').checked == false) {
      document.getElementById('advanced').click();
    }
    document.getElementById('aggregate').checked = false;
    var form = getForm("esSearch");
    $V(form.elements["reference"], guid);
    document.getElementById('divReference').innerHTML = html;
    document.getElementById('divFiltreReference').style.display = '';

    Search.resizeListResul();

    document.getElementById('button_search').click();
  },

  resizeListResul: function () {
    var div = document.getElementById('list_result_elastic');
    ViewPort.SetAvlHeight(div, 1);
  },

  updateForm: function (start, stop, nbresult) {
    var form = getForm("esSearch");
    $V(form.elements.start, start);
    $V(form.elements.stop, stop);
    $V(form.elements.nbResult, nbresult);
  },

  requestCluster: function (form) {
    var url = new Url("search", "ajax_request_cluster");
    url.addFormData(form);
    url.requestModal("85%", "50%");
    return false;
  },

  /**
   * Method to configure the serveur
   */
  configServeur: function () {
    var url = new Url('search', 'ajax_configure_serveur');
    url.requestUpdate("CConfigServeur");
  },

  /**
   * Method to configure the serveur
   */
  configES: function () {
    var url = new Url('search', 'ajax_configure_es');
    url.requestUpdate("CConfigES");
    return false;
  },

  configStat: function () {
    var url = new Url('search', 'ajax_configure_stat');
    url.requestUpdate("CConfigStat");
  },

  configQuery: function () {
    var url = new Url('search', 'ajax_configure_query');
    url.requestUpdate("CConfigQuery");
  },

  toggleDivAdvancedSearch: function () {
    $("divAdvancedSearch").toggle();
    Search.resizeListResul();
  },

  closeFiltreReference: function () {
    document.getElementById('reference').value = '';
    document.getElementById('divFiltreReference').style.display = 'none';
    Search.resizeListResul();
  },

  /**
   * Method use in first indexing
   *
   */
  createData: function () {
    // TODO Traduire la chaine
    Modal.confirm($T('mod-search-aide-create-table-tampon'),
      {
        onOK: function () {
          var url = new Url('search', 'ajax_create_data');
          url.requestJSON(() => Search.configES());
        }
      });
  },

  /**
   * Method to index in mode routine
   */
  createIndex: function (type_index) {
    var url = new Url('search', 'ajax_create_index');
    url.addParam("type_index", type_index);
    url.requestUpdate("tab_config_es");
  },

  /**
   * Method to index in mode routine
   */
  indexData: function () {
    var form = getForm("search-index-data");
    if (form && $V(form.elements.continue)) {
      form.onsubmit();
    }
  },

  /**
   * Method to check checkboxes
   *
   * @param {Element} input
   * @param {string} name
   */
  checkAllCheckboxes: function (input, name) {
    var oform = input.form;
    var elements = oform.select('input[name="' + name + '"]');

    elements.each(function (element) {
      element.checked = input.checked;
    });
  },


  /**
   * Method to filter term
   *
   * @param {Element} input
   * @param {String}  classe
   * @param {Element} table
   *
   */
  filter:             function (input, classe, table) {
    table = $(table);
    table.select("tr").invoke("show");
    var nameClass = "." + classe;
    var terms = $V(input);
    if (!terms) {
      return;
    }
    terms = terms.split(" ");
    table.select(nameClass).each(function (e) {
      terms.each(function (term) {
        if (!e.getText().like(term)) {
          e.up("tr").hide();
        }
      });
    });
  },

  toggleColumn: function (toggler, column) {
    var visible = column.visible();
    toggler.toggleClassName("expand", visible);

    column.toggle();
  },

  progressBar: function (id, score) {
    var container = $('score_' + id);
    var color = '#f00';
    if (score > 25 && score < 75) {
      color = '#E8AC07';
    }
    else if (score >= 75) {
      color = '#93D23F';
    }
    var data = [
      {data: score, color: color},
      {data: 100 - score, color: '#BBB'}
    ];

    jQuery.plot(container, data, {
      series: {
        pie: {
          innerRadius: 0.4,
          show:        true,
          label:       {show: false}
        }
      },
      legend: {show: false}
    });
  },

  manageThesaurus: function (sejour_id, contexte, callback) {
    callback = callback || function () {
      Search.reloadSearchAuto(sejour_id, contexte)
    };
    new Url('search', 'vw_search_thesaurus')
      .addParam("sejour_id", sejour_id)
      .requestModal("90%", "90%", {onClose: callback});
  },

  download: function () {
    var form = getForm("esSearch");
    var url = new Url('search', 'resultSearch', 'raw');
    url.addFormData(form);
    url.addParam("export_csv", 1);
    url.addParam("aggregate", 0);
    url.addParam('suppressHeaders', 1);
    url.pop();
  },

  reloadSearchAuto: function (sejour_id, contexte) {
    var container = 'table_main';
    if (contexte == 'pmsi') {
      container = "tab-search";
    }
    if (contexte == 'classique') {
      return;
    }
    new Url('search', 'vw_search_auto')
      .addParam("sejour_id", sejour_id)
      .addParam("contexte", contexte)
      .requestUpdate(container);
  },

  getAutocomplete: function (form) {
    var element_input = form.elements.words;
    var contextes = ["generique", $V(form.elements.contexte)];

    var url = new Url("search", "ajax_seek_autocomplete");
    url.addParam("object_class", "CSearch");
    url.addParam("input_field", element_input.name);
    url.addParam("user_id", User.id);
    url.addParam("contextes[]", contextes, true);
    url.autoComplete(element_input, null, {
      minChars:      2,
      method:        "get",
      dropdown:      true,
      updateElement: function (selected) {
        var _name = selected.down("span", "2").getText();
        if (_name != "") {
          $V(element_input, _name);
          form.elements.aggregate.checked = parseInt(selected.down().get("aggregation"));
          form.elements.fuzzy.checked = parseInt(selected.down().get("fuzzy"));
          var types = selected.down().get("types");
          if (types) {
            types = types.split("|");
          }
          $V(form.elements["names_types[]"], types);

          if (document.getElementById('advanced').checked == false) {
            document.getElementById('advanced').click();
          }
        }
      }
    });
  },

  showAdvancedSearchView: function () {
      new Url('search', 'showAdvancedSearch')
          .requestModal(500, 700);
  },

  buildAdvancedSearchQuery: function (form) {
      var words = '';
      var policy = null;
      if ($V(form.contains_words)) {
          words = $V(form.contains_words);
          policy = 0;
      } else if ($V(form.exact)) {
          words = $V(form.exact);
          policy = 1;
      } else if ($V(form.contains_word)) {
          words = $V(form.contains_word);
          policy = 2;
      }

      if (!words && !$V(form.without_words)) {
          Modal.alert('Vous n\'avez pas rempli de champs de recherche');
          return;
      }

      new Url('search', 'do_buildQuery', 'dosql')
          .addParam('words', words)
          .addParam('without_words', $V(form.without_words))
          .addParam('policy', policy)
          .addParam('date_min', $V(form._min_date))
          .addParam('date_max', $V(form._max_date))
          .addParam('user_id', $V(form.user_id))
          .addParam('patient_id', $V(form.patient_id))
          .addParam('atc_code', $V(form.atc))
          .addParam('cim_code', $V(form.cim))
          .addParam('ccam_code', $V(form.ccam))
          .requestJSON(function (json) {
                Control.Modal.close();
                $('words').value = json.expression;
          }, {method: 'post'});
  }
};
