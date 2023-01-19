/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

DRC = {
  ponderations_id: {
    1: 'mandatory',
    2: 'one',
    3: 'two',
    4: 'three',
    5: 'four',
    6: 'optional',
  },

  onSelectCode: Prototype.emptyFunction, // Callback selectCode

  search: function(form) {
    var url = new Url('cim10', 'ajax_search_drc');
    url.addParam('keywords', $V(form.elements['keywords']));
    url.addParam('result_class_id', $V(form.elements['result_class_id']));
    url.addParam('age', $V(form.elements['age']));
    url.addParam('sex', $V(form.elements['sex']));
    url.requestUpdate('list_drc');
  },

  selectResult: function(result_id) {
    var selected = $$('div#list_drc ul li.selected');
    if (selected.length) {
      selected.each(function(element) {
        element.removeClassName('selected');
      });
    }

    var element = $$('div#list_drc ul li[data-result_id="' + result_id + '"]');
    if (element.length) {
      element = element[0];
      element.addClassName('selected');

      $('result_selected').innerHTML = element.innerHTML;
      $('result_selected').show();
      $('show_details').show();

      this.displaySiblings(result_id);
      this.displayCriteria(result_id);
      this.displayPositions(result_id);
      this.displayFollowUp(result_id);
      this.displayCriticalDiagnoses(result_id);
      this.displayTranscoding(result_id);
      this.displayDetails(result_id);
      $('result_actions').show();
    }
  },

  showResultDetails: function() {
    Modal.open('result_details', {title: $T('CDRCConsultationResult-details'), showClose: true, width: '600px'})
  },

  displayCriteria: function(result_id) {
    this.displayInformation(result_id, 'criteria', 'list_criteria');
  },

  displayCriticalDiagnoses: function(result_id) {
    this.displayInformation(result_id, 'diagnoses', 'list_diagnoses');
  },

  displayFollowUp: function(result_id) {
    this.displayInformation(result_id, 'follow_up', 'follow_up');
  },

  displayPositions: function(result_id) {
    this.displayInformation(result_id, 'positions', 'list_positions');
  },

  displaySiblings: function(result_id) {
    this.displayInformation(result_id, 'siblings', 'list_siblings');
  },

  displayTranscoding: function(result_id) {
    this.displayInformation(result_id, 'transcodings', 'list_transcodings');
  },

  displayDetails: function(result_id) {
    this.displayInformation(result_id, 'details', 'result_details');
  },

  displayInformation: function(result_id, information, element) {
    var url = new Url('cim10', 'ajax_display_drc_information');
    url.addParam('result_id', result_id);
    url.addParam('information', information);
    url.requestUpdate(element);
  },

  setCodeCIM: function(code_cim, mode) {
    /* If no code CIM is given, we get the automatically selected code, and check if several codes are applicable */
    if (!code_cim) {
      var transcoding = $$('li.transcoding.selected');
      if (transcoding.length) {
        transcoding = transcoding[0];

        var transcodings = $$('li.transcoding[data-criteria="' + transcoding.get('criteria') + '"]');
        if (transcodings.length > 1 && !transcoding.hasClassName('by_user')) {
          var list = $('list_codes_cim');
          list.innerHTML = '';
          transcodings.each(function(element) {
            var radio = DOM.input({type: 'radio', name: 'code_cim', value: element.get('code')});
            if (element.hasClassName('selected')) {
              radio.checked = true;
            }

            list.insert(DOM.li({}, DOM.label({}, radio, element.innerHTML)));
          });

          $('btn_select_code').observe('click', DRC.selectCodeCim.curry('set_code'));

          Modal.open('cim_code_selection', {showClose: true, title: $T('CDRCConsultationResult-action-select_code')});
          return;
        }
        else {
          code_cim = transcoding.get('code');
        }
      }
    }

    if (!code_cim) {
      return;
    }

    code_cim = code_cim.gsub('.', '');

    if (mode == 'antecedents') {
      var form = getForm('addCIM');
      if (!form) {
        form = getForm('addAntFrmTamm');
        $V(form.keywords_code, code_cim);
        $V(form.code_diag, code_cim);
        return;
      }

      $V(form.elements['code_diag'], code_cim);
      form.onsubmit();
    }
    else if(mode == 'pathologie'){
      if (transcoding) {
        text = transcoding.get("libelle");
      }
      else{
        text = "";
      }
      addCodeCim10Pathologie(code_cim, text);
    }
    else {
      var form = getForm('editDossier');
      var codes = $V(form.elements['codes_cim']);
      if (codes != '') {
        codes = codes.split('|');
        codes.push(code_cim);
        codes = codes.join('|');
      }
      else {
        codes = code_cim;
      }
      $V(form.elements['codes_cim'], codes);
      form.onsubmit();
    }
  },

  selectCodeCim: function(action) {
    var form = getForm('CodeCIMSelection');
    var selected = $$('ul.list li.transcoding.selected');
    if (selected.length) {
      selected[0].removeClassName('selected');
    }

    var transcoding = $$('ul.list li.transcoding[data-code="' + $V(form.elements['code_cim']) + '"]');
    if (transcoding.length) {
      transcoding[0].addClassName('selected');
      transcoding[0].addClassName('by_user');
    }

    Control.Modal.close();
    if (action == 'set_code') {
      DRC.setCodeCIM($V(form.elements['code_cim']));
    }
    else {
      DRC.displayCopyResult($V(form.elements['code_cim']));
    }
  },

  displayCopyResult: function(code_cim) {
    if (!code_cim) {
      var transcoding = $$('li.transcoding.selected');
      if (transcoding.length) {
        transcoding = transcoding[0];

        var transcodings = $$('li.transcoding[data-criteria="' + transcoding.get('criteria') + '"]');
        if (transcodings.length > 1 && !transcoding.hasClassName('by_user')) {
          var list = $('list_codes_cim');
          list.innerHTML = '';
          transcodings.each(function (element) {
            var radio = DOM.input({type: 'radio', name: 'code_cim', value: element.get('code')});
            if (element.hasClassName('selected')) {
              radio.checked = true;
            }

            list.insert(DOM.li({}, DOM.label({}, radio, element.innerHTML)));
          });

          $('btn_select_code').observe('click', DRC.selectCodeCim.curry('copy'));

          Modal.open('cim_code_selection', {showClose: true, title: $T('CDRCConsultationResult-action-select_code')});
          return;
        }
        else {
          code_cim = transcoding.get('code');
        }
      }
    }

    var form = getForm('copy_result_text');
    $V(form.elements['_result_text'], this.formatResultText(code_cim));
    $('btn_clipboard').observe('click', function() {
      var form = getForm('copy_result_text');
      form.elements['_result_text'].select();
      try {
        return document.execCommand("copy");
      } catch (ex) {
        return false;
      }
    });
    Modal.open('copy_result', {showClose: true, title: $T('CDRCConsultationResult-action-copy')});
  },

  copyToField: function(field) {
    var form = getForm('copy_result_text');
    $V(form.elements[field], $V(form.elements['_result_text']));
    form.onsubmit();
  },

  formatResultText: function(code_cim) {
    var text = $('result_selected').innerHTML.trim();
    var form_dp = getForm('diagnostic_position_form');
    var form_follow_up = getForm('follow_up_form');
    if ($V(form_dp.elements['position']) || $V(form_follow_up.elements['follow_up'])) {
      text = text + ' (';
      if ($V(form_dp.elements['position'])) {
        text = text + $T('CDRCConsultationResult-' + $V(form_dp.elements['position']) + '-court');
        if ($V(form_follow_up.elements['follow_up'])) {
          text = text + ', ';
        }
      }

      if ($V(form_follow_up.elements['follow_up'])) {
        text = text + $V(form_follow_up.elements['follow_up']);
      }

      text = text + ')';
    }

    text = text + '\n ';

    if (form_follow_up.elements['asymptomatic'].checked) {
      text = text + ' ' + $T('CDRConsultationResult-asymptomatic');
    }

    if (form_follow_up.elements['ALD'].checked) {
      text = text + ' ' + $T('CDRConsultationResult-ALD');
    }

    if (code_cim) {
      text = text + ' Code CIM10: ' + code_cim;
    }

    text = text + '\n\n';

    $$('li.criterion.selected').each(function(criterion) {
      var spacing = 2 + 4 * parseInt(criterion.get('level'));
      var title = criterion.down('span').innerHTML;
      text = text + title.padStart(spacing + title.length, ' ') + '\n';
    });

    text = text.unescapeHTML();

    return text;
  },

  toggleCriterion: function(criterion_id) {
    var element = this.getCriterionElement(criterion_id);
    /* If the criterion is already selected, we unselect it */
    if (element.hasClassName('selected')) {
      this.unselectCriterion(element, true)
    }
    /* Otherwise, we check if it can be selected */
    else {
      this.selectCriterion(element);
    }
  },

  selectCriterion: function(element) {
    var criterion_id = element.get('criterion_id');
    var children = this.getChildrenCriterionElements(criterion_id);
    var parent_id = element.get('parent');

    /* If the criterion has childs, we check if the conditions are ok before selecting it */
    if (children.length) {
      if (this.checkPonderation(children)) {
        element.addClassName('selected');
      }
    }
    else {
      element.addClassName('selected');
    }

    /* If the element is a child, we check if all the conditions are met to check it's parent */
    if (parent_id != 0) {
      this.selectCriterion(this.getCriterionElement(parent_id));
    }
    else {
      this.checkTranscodings();
    }
  },

  unselectCriterion: function(element, check_children) {
    var criterion_id = element.get('criterion_id');
    var children = this.getChildrenCriterionElements(criterion_id);
    var parent_id = element.get('parent');

    /* If the criterion has childs, we check unselect all of it's children */
    if (children.length && check_children) {
      element.removeClassName('selected');
      children.each(function(child) {
        DRC.unselectCriterion(child, true);
      });
    }
    else {
      element.removeClassName('selected');
    }

    /* If the element is a child, we check if all the conditions are still met for it's parent */
    if (parent_id != 0) {
      children = this.getChildrenCriterionElements(parent_id);
      if (!this.checkPonderation(children)) {
        this.unselectCriterion(this.getCriterionElement(parent_id), false);
      }
    }
    this.checkTranscodings();
  },

  getCriterionElement: function(criterion_id) {
    return $$('li.criterion[data-criterion_id="' + criterion_id + '"]')[0];
  },

  getChildrenCriterionElements: function(criterion_id) {
    return $$('li.criterion[data-parent="' + criterion_id + '"]');
  },

  checkTranscodings: function() {
    var transcodings = $$('ul.list li.transcoding');

    if (transcodings.length == 1) {
      var element = transcodings[0];
      if (!element.hasClassName('selected')) {
        element.addClassName('selected');
      }
    }
    else {
      var selected = false;

      transcodings.each(function(element) {
        if (!selected && element.get('conditions') == '1' && element.get('criteria') !== '') {
          var criteria = element.get('criteria').split('|');
          var condition = true;
          criteria.each(function(criterion) {
            if (!$$('li.criterion.selected[data-criterion_id="' + criterion + '"]').length) {
              condition = false;
            }
          });

          if (condition) {
            var previous = $$('ul.list li.transcoding.selected');
            if (previous.length) {
              previous[0].removeClassName('selected');
            }

            element.addClassName('selected');
            selected = true;
          }
        }
      });

      /* If no transcoding is applicable, and one was previously selected, we unselect it */
      var previous = $$('ul.list li.transcoding.selected');
      if (!selected && previous.length) {
        previous[0].removeClassName('selected');
      }

      /* Selecting the default Cim10 code if no other is applicable */
      var def = $$('ul.list li.transcoding[data-default="1"]');
      if (!selected && def.length) {
        def[0].addClassName('selected');
      }
    }
  },

  checkPonderation: function(elements) {
    var ponderations = $H();
    /* For each ponderation present in the elements, we count its occurences,
     * and how many element are selected for each ponderation
     */
    elements.each(function(element) {
      var ponderation = DRC.ponderations_id[element.get('ponderation')];
      if (ponderations.keys().indexOf(ponderation) == -1) {
        ponderations.set(ponderation, {total: 0, selected: 0});
      }

      var data = ponderations.get(ponderation);
      data.total = data.total + 1;
      if (element.hasClassName('selected')) {
        data.selected = data.selected + 1;
      }

      ponderations.set(ponderation, data);
    });

    var condition = true;
    /* For each ponderation, we check if the conditions are met */
    ponderations.keys().each(function(ponderation) {
      var data = ponderations.get(ponderation);
      switch (ponderation) {
        case 'mandatory':
          if (data.total > data.selected) {
            condition = false;
          }
          break;
        case 'one':
          if (data.selected < 1) {
            condition = false;
          }
          break;
        case 'two':
          if (data.selected < 2) {
            condition = false;
          }
          break;
        case 'three':
          if (data.selected < 3) {
            condition = false;
          }
          break;
        case 'four':
          if (data.selected < 4) {
            condition = false;
          }
          break;
      }
    });

    return condition;
  },
  /**
   * Permet de sélectionner les correspondances CIM10 (Dispo uniquement pour le benchmark)
   *
   */
  selectCode: function () {
    this.onSelectCode();
  }
};
