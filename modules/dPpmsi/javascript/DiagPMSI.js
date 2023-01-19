/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

DiagPMSI = window.DiagPMSI || {

  getAutocompleteCim10PMSI : function(form, input_field, nullify_imput) {
    var oform = form;
    var url = new Url("pmsi", "ajax_seek_cim10_pmsi");
    url.addParam("object_class", "CCIM10");
    url.autoComplete(oform.keywords_code_pmsi, '', {
      minChars:           1,
      dropdown:           true,
      width:              "250px",
      select:             "code",
      afterUpdateElement: function (oHidden) {
        $V(input_field, oHidden.value);
        if (!nullify_imput) {
          $V(form.keywords_code, oHidden.value);
        }
      }
    });
  },

  getAutocompleteCim10 : function (form, input_field, nullify_imput, sejour_type, field_type) {
    var element= input_field;
    CIM.autocomplete(
      form.keywords_code,
      null,
      {
        sejour_type: sejour_type,
        field_type: field_type,
        afterUpdateElement: function(input) {
          $V(element, input.value);
          if (!nullify_imput) {
            $V(form.keywords_code, input.value);
          }
        }
      }
    );
  },

  deleteDiag : function (form, input_field) {
    var oForm = form;
    $V(oForm.keywords_code, "");
    $V(input_field, "");
    oForm.onsubmit();
  },

  input_diag :null,
  initDiagCimPMSI : function (field) {
    this.input_diag = field;
    var url = new Url("pmsi", "vw_cim10_explorer");
    url.addParam("modal", true);
    url.requestModal("95%","95%");
  },

  selectDiag : function (code) {
    $V(this.input_diag, code);
  }

};