/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ReferencesCheck = window.ReferencesCheck || {
  auto_refresh: true,
  refresh_timeout: null,

  fillTable: function () {
    Modal.confirm($T('CRefCheck-msg-Confirm table fill'),
      {
        onOK: function () {
          var url = new Url('dPdeveloppement', 'ajax_fill_integrity_table');
          url.requestUpdate("systemMsg", {onComplete: function () {location.reload();}});
        }
      }
    );
  },

  nextStep: function () {
    var form = getForm('exec-integrity-checker');
    if ($V(form.elements.continue) == 0) {
      return;
    }

    // TODO update fields before check continue
    form.onsubmit();
  },

  startIntegrityCheck: function () {
    var form = getForm('exec-integrity-checker');
    $V(form.elements.continue, 1);

    $("integrity-start").disable();
    $("integrity-stop").enable();

    form.onsubmit();
  },

  stopIntegrityCheck: function () {
    var form = getForm('exec-integrity-checker');
    $V(form.elements.continue, 0);

    $("integrity-start").enable();
    $("integrity-stop").disable();
  },

  displayClass: function (class_name) {
    var url = new Url('dPdeveloppement', 'vw_class_integrity');
    url.addParam('class', class_name);
    url.requestModal();
  },

  filterTable: function (input, class_name) {
    var tr = $$('tr.' + class_name);

    tr.invoke('show');

    var search = $V(input);
    if (!search) {
      return;
    }

    tr.each(function (elem) {
      if (!elem.down('td.display-class-name').getText().like(search)) {
        elem.hide();
      }
    })
  },

  updateInfos: function (class_name, field) {
    var form = getForm('exec-integrity-checker');
    $V(form.elements.class, class_name);
    $V(form.elements.field, field);
  },

  reloadTable: function (class_name, order) {
    clearTimeout(ReferencesCheck.refresh_timeout);

    if (!order) {
      order = $V(getForm('order-way-integrity').elements.order);
    }

    var url = new Url('dPdeveloppement', 'ajax_vw_references');
    url.addParam('class', class_name);
    url.addParam('order', order);
    url.requestUpdate('ref-check-tables');
  },

  changeAutoRefresh: function (btn) {
    if (ReferencesCheck.auto_refresh) {
      ReferencesCheck.auto_refresh = false;
      btn.removeClassName('pause');
      btn.toggleClassName('play');
      clearTimeout(ReferencesCheck.refresh_timeout);
    }
    else {
      ReferencesCheck.auto_refresh = true;
      btn.removeClassName('play');
      btn.toggleClassName('pause');
      ReferencesCheck.refresh_timeout = setTimeout('ReferencesCheck.reloadTable();', 60000);
    }
  },

  displayFieldErrors: function (field_id) {
    var url = new Url('dPdeveloppement', 'ajax_display_errors');
    url.addParam('field_id', field_id);
    url.requestModal();
  },

  resetIntegrityCheck: function () {
    Modal.confirm($T('CRefCheckTable-msg-Confirm reset'),
      {
        onOK: function () {
          var url = new Url('dPdeveloppement', 'do_reset_check_errors', 'dosql');
          url.requestUpdate("systemMsg", {method: 'post', onComplete: function () {ReferencesCheck.reloadTable()}});
        }
      }
    );
  }
};