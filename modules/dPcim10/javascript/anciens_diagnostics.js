/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

AnciensDiagnostics = {
  setDiag: function(code, objectGuid, typeCode, isList) {
    var form = getForm(objectGuid + '-cim-' + typeCode);
    if (isList) {
      $V(form['_added_code_' + ((objectGuid.indexOf('CSejour') > -1) ? 'cim' : typeCode.toLowerCase())], code);
    }
    else {
      $V(form[typeCode], code);
    }
    this.submitDiag(code, objectGuid, typeCode, form, false, isList);
  },
  removeDiag: function(code, objectGuid, typeCode, isList) {
    var form =  getForm(objectGuid + '-cim-' + typeCode + ((isList) ? '-delete' : ''));
    if (isList) {
      $V(form['_deleted_code_' + ((objectGuid.indexOf('CSejour') > -1) ? 'cim' : typeCode.toLowerCase())], code);
    }
    else {
      $V(form[typeCode], '');
    }
    this.submitDiag(code, objectGuid, typeCode, form, true, isList);
  },
  submitDiag: function(code, objectGuid, typeCode, form, enableButtons, isList) {
    onSubmitFormAjax(form, function() {
      new Url('cim10', 'ancien_diagnostics_ajout')
        .addParam('object_guid', objectGuid)
        .requestUpdate(objectGuid + '_cim', function() {
          if (enableButtons) {
            this.enableDiagButtons(code, typeCode);
          }
          else {
            this.disableDiagButtons(code, typeCode, !isList);
          }
        }.bind(this));
    }.bind(this));
  },
  disableDiagButtons: function(code, typeCode, enableOthers) {
    $$('.'+typeCode+'-setter.add').each(function(element) { //tester avec.add
      if (element.get('cim') === code) { // tester sans le controle sur la classe .add
        element.disable();
      }
      else if (enableOthers) {
        element.enable();
      }
    });
  },
  enableDiagButtons: function(code, typeCode) {
    $$('.' + typeCode + '-setter.add').each(function(element) {
      if (element.get('cim') === code) {
        element.enable();
      }
    });
  }
};
