/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CISP = {
  tabs: null,
  mode: null, // "antecedent"|"pathologie"
  onSelectCode: Prototype.emptyFunction, // Callback selectCode

  /**
   * Deals with the showing/hidding details of different chapters
   *
   * @param li
   * @param cisp
   * @param codes_cim10
   * @param chapitre
   */
  showDetail: function(li, cisp, codes_cim10, chapitre) {
    li.up('ul').select('li').invoke('removeClassName', 'selected');

    li.addClassName('selected');

    cisp = JSON.parse(cisp);
    codes_cim10 = JSON.parse(codes_cim10);

    var cisp_detail = $('cisp_detail_' + chapitre).update();
    cisp_detail.insert(
      DOM.h1(null, cisp.libelle + ' (' + cisp.code_cisp + ')')
    );

    var has_content = false;

    ['description', 'inclusion', 'exclusion', 'consideration', 'note'].each(
      function(_property) {
        if (cisp[_property]) {
          has_content = true;
          cisp_detail.insert(
            DOM.h2(null, $T('CCISP-' + _property))
          )
            .insert(DOM.p({id:'cisp_detail_'+_property}, cisp[_property]))
        }
      }
    );

    if (!has_content) {
      cisp_detail.insert(
        DOM.div(null, $T('CCISP-No content available'))
      );
    }

    var div_cim = $('list_cim10_' + chapitre);

    while (div_cim.nodeName !== 'TR') {
      div_cim = div_cim.parentNode;
    }
    div_cim.style.display = 'table-row';

    var list_cim10 = $('list_cim10_' + chapitre).down('ul').update();

    codes_cim10.forEach(function(_cim10) {
      new Url('cim10', 'ajax_json_cim')
        .addParam('code', _cim10)
        .requestJSON(function (json) {
          if (json) {
            var longname = (json.longname) ? ' - ' + json.longname : '';
            var li = DOM.li({class:'transcoding', 'data-code': _cim10}, json.code + longname);
            li.observe('click', CISP.applySelectedCIM10.bind(this));
            list_cim10.insert(li);
          }
        });
    });
  },

  /**
   * Gets and applies the cim code in the underneaths form
   *
   * @param event
   */
  applySelectedCIM10: function(event) {
    var mode = CISP.mode;
    code_cim10 = event.target.dataset.code;

    var form = getForm('editDossier');

    $V(form.elements['codes_cim'], code_cim10);

    if (mode == "antecedents") {
      $V($('addAntFrmTamm').keywords_code, code_cim10);
      $V($('addAntFrmTamm').code_diag, code_cim10);
    }
    else if (mode == "pathologie") {
      addCodeCim10Pathologie(code_cim10);
    }

    Control.Modal.close();
  },

  /**
   * Deals showing and hiding cim codes
   */
  displayCimCodes: function () {
    var categories = Array.from($$('ul#tabs_cisp li a'));
    categories.invoke('observe', 'click', this._toggleCimCodes.bind(this));

    var liste = Array.from($$('ul.list-cisp li'));
    liste.invoke('observe', 'click', this._toggleCimCodes.bind(this));
  },
  _toggleCimCodes: function (event) {
    var target = event.target.parentElement;
    while (target.nodeName !== 'UL') {
      target = target.parentElement;
    }

    if (target.id === 'tabs_cisp') {
      var details = $$('.cim-codes .cim10-details');
      details.forEach(function(detail) {
        detail.style.display = 'none';
      });
    }

    if (target.classList.contains('list-cisp') && $$('.cim-codes')[0]) {
      $$('.cim-codes')[0].show();
    }
  },
  /**
   * Permet de sélectionner un code CISP (Dispo uniquement pour le benchmark)
   *
   * @param button
   */
  selectCode: function (button) {
    this.onSelectCode(button);
  }
};
