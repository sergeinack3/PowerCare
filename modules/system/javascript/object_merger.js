/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ObjectMerger = {
  base: '_choix_1',

  setField: function (field, source) {
    var form = source.form;
    var value = $V(source);
    var field = $(form.elements[field]);

    // Update Value
    $V(field, value);

    // Also check the source when clicking
    if (source.type == 'radio') {
      source.checked = true;
    }

    if (!field.hasClassName) {
      return;
    }

    var view = null;
    var props = field.getProperties();

    // Can't we use Calendar.js helpers ???
    if (props.dateTime) {
      view = $(form.elements[field.name + '_da']);
      $V(view, value ? Date.fromDATETIME(value).toLocaleDateTime() : "");
    }

    if (props.date) {
      view = $(form.elements[field.name + '_da']);
      $V(view, value ? Date.fromDATE(value).toLocaleDate() : "");
    }

    if (props.time) {
      view = $(form.elements[field.name + '_da']);
      $V(view, value);
    }

    var label = Element.getLabel(source);
    if (props.ref) {
      view = $(form.elements["_" + field.name + '_view']);
      $V(view, label.getText().strip());
    }

    if (props.mask) {
      $V(field, label.getText().strip(), false);
    }
  },

  updateOptions: function (field) {
    var form = field.form;
    $A(form.elements["_choix_" + field.name]).each(function (element) {
      element.checked = element.value.stripAll() == field.value.stripAll();
    });
  },

  confirm: function (fast) {
    $V(getForm("form-merge").fast, fast);
    Modal.confirm($('confirm-' + fast), {onOK: ObjectMerger.perform});
    return false;
  },

  updateWarning: function (element, form) {
    let mergeForm = element ? element.form : form;
    let base = ObjectMerger.base;

    switch ($V(mergeForm._objects_class)) {
      case 'CPatient':
        ObjectMerger.listPatientWarnings(mergeForm, base, element);
        break;
      default:
    }

    ObjectMerger.checkWarningListEmpty();
  },

  listPatientWarnings: function (form, base, element) {
    let warnings       = [];
    let base_status    = form[`${form.name}_${base}_status`].value;
    let top            = base === '_choix_1'? '_choix_2' : '_choix_1';
    let choosed_status = $V(form.status);

    if (!element || element.name === '_base_object_id' ||  choosed_status === base_status) {
      choosed_status = form[`${form.name}_${top}_status`].value;
    }

    if (choosed_status === 'VALI' && base_status === 'PROV') {
      warnings.push($T('CPatient-merge-warning-identity_status-conflict1'));
    }

    if ((['RECUP', 'QUAL'].includes(base_status) && ['PROV', 'VALI'].includes(choosed_status)) || (['PROV', 'VALI'].includes(base_status) && ['RECUP', 'QUAL'].includes(choosed_status))) {
      warnings.push($T('CPatient-merge-warning-identity_status-conflict2'));
    }
    ObjectMerger.displayWarningList(warnings);
  },

  displayWarningList: function (warnings) {
    let ul = $('frontWarnings').select('ul')[0];
    ul.innerHTML = null;
    warnings.forEach(warning => {
      let li = document.createElement("li");
      li.innerText = warning;
      ul.append(li);
    });
  },

  checkWarningListEmpty: function () {
    let list = $('warningList');
    list.select('li').length ? list.show() : list.hide();
  },

  perform: function () {
    let form = getForm("form-merge");
    checkForm(form) ? form.submit() : false;
  }
};
