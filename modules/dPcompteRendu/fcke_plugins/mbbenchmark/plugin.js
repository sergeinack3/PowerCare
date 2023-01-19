/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('mbbenchmark', {
  requires: ['dialog'],
  init: function(editor) {
    CKEDITOR.dialog.add('mbbenchmark_dialog', function() {
      return {
        title : $T('CCompteRendu-plugin-action-Insert a benchmark'),
      };
    });

    editor.addCommand('mbbenchmark', {exec: mbbenchmarks_onclick});
    editor.ui.addButton('mbbenchmark', {
      label:   $T('CCompteRendu-plugin-mbbenchmark'),
      command: 'mbbenchmark',
    });
  },
  selectBenchmark: selectBenchmark
});

function mbbenchmarks_onclick(editor) {
  let form = getForm('editFrm');

  new Url('compteRendu', 'viewBenchmark')
    .requestModal('80%', '85%')
}

function selectBenchmark(benchmark) {
  switch (benchmark) {
    case "CIM10" :
      new Url('cim10', 'view_search_cim')
        .addParam('ged', 1)
        .requestUpdate("benchmark");
      break;
    case "LOINC" :
      new Url('loinc', 'vw_search_loinc_filter')
        .addParam('ged', 1)
        .requestUpdate("benchmark");
      break;
    case "CCAM" :
      new Url('ccam', 'selectorCodeCcam')
        .addParam('ged', 1)
        .requestUpdate("benchmark");
      break;
    case "ATC" :
      new Url('medicament', 'httpreq_vw_livret_arbre_ATC')
        .addParam('ged', 1)
        .requestUpdate("benchmark");
      break;
    case "DRC" :
      new Url('cim10', 'drc')
        .addParam('ged', 1)
        .requestUpdate("benchmark");
      break;
    case "CISP" :
      new Url('cim10', 'ajax_cisp')
        .addParam('ged', 1)
        .requestUpdate("benchmark");
      break;
  }
}
