/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Context = {
  list: function (form) {
    var url = new Url('patients', 'CConstantesMedicales_configure_contexts');
    url.addParam('schema', $V(form['schema']));
    url.addParam('group', $V(form.group));
    url.requestUpdate('context_list', function () {
      ConstantConfig.setConstantsHeight();
      ConstantConfig.setConfigHeight();
    });

    this.select(form.group.down('option:selected'));
    ConstantConfig.reloadSelected();
    return false;
  },

  toggle: function () {
    var list = $('context_list');
    list.toggle();
    var icon = $('toggle_context_list');
    if (list.visible()) {
      icon.removeClassName('fa-chevron-circle-right');
      icon.addClassName('fa-chevron-circle-down');
    } else {
      icon.addClassName('fa-chevron-circle-right');
      icon.removeClassName('fa-chevron-circle-down');
    }

    ConstantConfig.setConstantsHeight();
    ConstantConfig.setConfigHeight();
  },

  oncheck: function (element, noreload) {
    if (element.checked) {
      if ($('selected_contexts').readAttribute('data-context_class') == element.readAttribute('data-class')) {
        this.add(element);
      } else {
        this.select(element);
      }
    } else {
      this.remove(element)
    }

    if (!noreload) {
      ConstantConfig.refreshList();
      ConstantConfig.reloadSelected();
    }
  },

  oncheckSector: function (element) {
    $$('ul#sector_' + element.readAttribute('data-guid') + ' input[type="checkbox"]').each(function (element, input) {
      if (input.checked != element.checked) {
        input.checked = element.checked
        this.oncheck(input, true);
      }
    }.bind(this, element));

    ConstantConfig.refreshList();
    ConstantConfig.reloadSelected();
  },

  select: function (element) {
    var list = $('selected_contexts');
    var guid = element.readAttribute('data-guid');
    list.writeAttribute('data-context_class', element.readAttribute('data-class'));
    list.update(DOM.span({
      class:       'circled',
      id:          'selected_context_' + guid,
      'data-guid': guid,
      style:       'font-weight: normal'
    }, element.readAttribute('data-name')));

    $V(getForm('constants_configs').object_guid, guid);
  },

  add: function (element) {
    var list = $('selected_contexts');
    var guid = element.readAttribute('data-guid');
    list.insert(DOM.span({
      class:       'circled',
      id:          'selected_context_' + guid,
      'data-guid': guid,
      style:       'font-weight: normal'
    }, element.readAttribute('data-name')));

    var guids = $V(getForm('constants_configs').object_guid).split('|');
    guids.push(guid);
    $V(getForm('constants_configs').object_guid, guids.join('|'));
  },

  remove: function (element) {
    var guid = element.readAttribute('data-guid');
    $('selected_context_' + guid).remove();

    if ($('selected_contexts').empty()) {
      this.select($('select_group').down('option:selected'));
    }

    var guids = $V(getForm('constants_configs').object_guid).split('|');
    guids.splice(guids.indexOf(guid), 1);

    if (guids.length == 0) {
      guids.push(getForm('selectSchema').group.down('option:selected').readAttribute('data-guid'));
    }

    $V(getForm('constants_configs').object_guid, guids.join('|'));
  },

  getSelected: function () {
    var context_guids = [];
    $$('#selected_contexts span.circled').each(function (element) {
      context_guids.push(element.readAttribute('data-guid'));
    });

    return context_guids;
  },

  getSchema: function () {
    return $V($('select_schema'));
  }
};

ConstantConfig = {
  filter: function (input) {
    var table = $('constants');
    table.select('tr').invoke('show');

    table.select('td.constant').each(function (element) {
      if (!element.innerHTML.like($V(input))) {
        element.up('tr').hide();
      }
    });
  },

  changeValue: function (feature) {
    var form = getForm('constants_configs');
    var value = $V(form[feature + '-form']) + '|' + $V(form[feature + '-graph']) + '|' + $V(form[feature + '-color'])
      + '|' + $V(form[feature + '-mode']) + '|' + $V(form[feature + '-min']) + '|' + $V(form[feature + '-max'])
      + '|' + $V(form[feature + '-norm_min']) + '|' + $V(form[feature + '-norm_max']);
    var input = $A(form.elements['c[' + feature + ']']).filter(function (element) {
      return !element.hasClassName('inherit-value');
    });

    $V(input[0], value);
  },

  changeValueComment: function (feature, valueRadio) {
    var form = getForm('constants_configs');

    var value = $V(form[feature + '-' + valueRadio]);

    if (valueRadio == 1) {
      form[feature + '-0'].checked = false;
    } else {
      form[feature + '-1'].checked = false;
    }

    $V(form.elements['c[' + feature + ']'], value);
  },

  changeMode: function (feature) {
    var constant = feature.split(' ').last();
    var form = getForm('constants_configs');
    var mode = $V(form[feature + '-mode']);

    $('label_min_' + constant).title = $T('config-dPpatient-CConstantesMedicales-selection-min_' + mode + '-desc');
    $('label_max_' + constant).title = $T('config-dPpatient-CConstantesMedicales-selection-max_' + mode + '-desc');
    this.changeValue(feature);
  },

  oncheck: function (element) {
    if (element.checked) {
      this.load(element);
    } else {
      this.remove(element);
    }
  },

  checkAll: function (element) {
    $$('table#constants input.check_constant').each(function (checkbox) {
      checkbox.checked = element.checked;
    });

    if (element.checked) {
      if ($('no_constant_selected').visible()) {
        $('no_constant_selected').hide();
        $('div-submit-constant_configs').show();
      }
      this.reloadSelected();
    } else {
      $('no_constant_selected').show();
      $('div-submit-constant_configs').hide();
      $('config_header_row').remove();
      ConstantConfig.fixSubmitButton();
      $('configurations').update();
    }
  },

  getCheckedConstants: function () {
    var constants = [];
    $$('table#constants input.check_constant:checked').each(function (element) {
      constants.push(element.name);
    });

    return constants;
  },

  load: function (element) {
    var url = new Url('patients', 'CConstantesMedicales_configure_get');
    url.addParam('constant', element.name);
    url.addParam('schema', Context.getSchema());
    url.addParam('context_guids', Context.getSelected().join('|'));
    url.requestUpdate('configurations', {
      method:        'post',
      getParameters: {m: 'patients', a: 'CConstantesMedicales_configure_get'},
      insertion:     function (element, content) {
        if ($('no_constant_selected').visible()) {
          $('no_constant_selected').hide();
          $('div-submit-constant_configs').show();
        }
        element.insert(content);
        ConstantConfig.fixSubmitButton();
      }
    });
  },

  remove: function (element) {
    $('config_' + element.name).remove();
    if ($('configurations').empty()) {
      $('no_constant_selected').show();
      $('div-submit-constant_configs').hide();
      $('config_header_row').remove();
    }
    ConstantConfig.fixSubmitButton();
  },

  reload: function (element) {
    var name = element.name;
    var url = new Url('patients', 'CConstantesMedicales_configure_get');
    url.addParam('constant', element.name);
    url.addParam('schema', Context.getSchema());
    url.addParam('context_guids', Context.getSelected().join('|'));
    url.requestUpdate('configurations', {
      method:        'post',
      getParameters: {m: 'patients', a: 'CConstantesMedicales_configure_get'},
      insertion:     function (element, content) {
        $('config_' + name).replace(content);
      }
    });
  },

  reloadSelected: function () {
    var constants = this.getCheckedConstants();

    if (constants.length > 0) {
      var url = new Url('patients', 'CConstantesMedicales_configure_get');
      url.addParam('constant', constants.join('|'));
      url.addParam('schema', Context.getSchema());
      url.addParam('context_guids', Context.getSelected().join('|'));
      url.requestUpdate('configurations', {
        method:        'post',
        getParameters: {m: 'patients', a: 'CConstantesMedicales_configure_get'},
        onComplete:    ConstantConfig.fixSubmitButton.curry()
      });
    }
  },

  refreshList: function () {
    var url = new Url('patients', 'CConstantesMedicales_configure_list');
    url.addParam('constants', this.getCheckedConstants().join('|'));
    url.addParam('context_guids', Context.getSelected().join('|'));
    url.requestUpdate('constants', {
      method:        'post',
      getParameters: {m: 'patients', a: 'CConstantesMedicales_configure_list'},
    });
  },

  editAlert: function (constant) {
    var url = new Url('patients', 'CConstantesMedicales_configure_alert');
    url.addParam('constant', constant);
    url.addParam('schema', Context.getSchema());
    url.addParam('context_guids', Context.getSelected().join('|'));
    url.requestModal(null, null, {
      method:        'post',
      getParameters: {m: 'patients', a: 'CConstantesMedicales_configure_alert'}
    });
  },

  resetAll: function () {
    $$('div.custom-value button.cancel:enabled').each(function (element) {
      ConstantConfig.toggleCustom(element, false);
    });
  },

  toggleCustom: function (element, enable) {
    var customValue = element.up('div.custom-value');
    var inheritValue = element.up('td');

    if (enable) {
      this.enableForm(customValue);
      inheritValue.down('input.inherit-value').disable();
      customValue.down("button.edit").hide();
      customValue.down("button.edit").disable();
      customValue.down("button.cancel").show();
      customValue.down("button.cancel").enable();
    } else {
      this.disableForm(customValue).show();
      inheritValue.down('input.inherit-value').enable();
      customValue.down("button.edit").show();
      customValue.down("button.edit").enable();
      customValue.down("button.cancel").hide();
      customValue.down("button.cancel").disable();
    }
  },

  submit: function (form) {
    $('div-submit-constant_configs').down('button').disable();
    return onSubmitFormAjax(form, function () {
      $('div-submit-constant_configs').down('button').enable();
    });
  },

  enableForm: function (element) {
    element.fire("conf:enable");
    var inputs = element.select("input,select,textarea,button:not(.keepEnable)");
    inputs.invoke("enable");
    return element.show();
  },

  disableForm: function (element) {
    element.fire("conf:disable");
    var inputs = element.select("input,select,textarea,button:not(.keepEnable)");
    inputs.invoke("disable");
    return element.hide();
  },

  setConstantsHeight: function () {
    ViewPort.SetAvlHeight($('constants_container'), 1.0);
  },

  setConfigHeight: function () {
    ViewPort.SetAvlHeight($('config_container'), 1.0);
  },

  fixSubmitButton: function () {
    var height = $('table_constant_configs').getHeight() + $('table_constant_configs').cumulativeOffset().top;
    var documentHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
    if (height > documentHeight) {
      $('div-submit-constant_configs').setStyle({
        position: 'fixed',
        bottom:   '0px',
        left:     $('table_constant_configs').cumulativeOffset().left + 'px',
        width:    $('table_constant_configs').getWidth() + 'px'
      });
      if (!$('submit_row')) {
        var row = getForm('constants_configs').down('table').insertRow(-1);
        row.setStyle({height: '2.5em'});
        row.id = 'submit_row';
        row.insert(DOM.td({colspan: 5}));
      }
    } else {
      $('div-submit-constant_configs').setStyle({position: 'static', bottom: 'auto'});
      if ($('submit_row')) {
        $('submit_row').remove();
      }
    }
  }
};