/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ElasticDSN = {
  load:              function (dsn, container) {
    let url = new Url("system", "loadElasticDsn");
    url.addParam("dsn", dsn);
    url.addParam("dsn_uid", $(container).id);
    url.requestUpdate(container);
  }, create:         function (form) {
    return onSubmitFormAjax(form, null, "config-dsn-create-" + $V(form.dsn));
  }, test:           function (dsn, module) {
    let url = new Url("system", "testElasticDsn");
    url.addParam("dsn", dsn);
    url.addParam("module", module);
    url.requestModal("100%", "100%");
  }, edit:           function (dsn, container) {
    let url = new Url("system", "editElasticDsn");
    url.addParam("dsn", dsn);
    url.requestModal(500, 400, {
      onClose: function () {
        ElasticDSN.load(dsn, container);
      }
    });
  }, init:           function (dsn, module, container) {
    let url = new Url("system", "doInitElasticObject");
    url.addParam("dsn", dsn);
    url.addParam("setup_module", module);
    url.requestUpdate(container, {
      onComplete: function () {
        Control.Modal.refresh();
      }
    });
  }, createIndex:    function (dsn, module, container) {
    let url = new Url("system", "doCreateElasticIndex");
    url.addParam("dsn", dsn);
    url.addParam("setup_module", module);
    url.requestUpdate(container, {
      onComplete: function () {
        Control.Modal.refresh();
      }
    });
  }, createTemplate: function (dsn, module, container) {
    var url = new Url("system", "doCreateElasticTemplate");
    url.addParam("dsn", dsn);
    url.addParam("setup_module", module);
    url.requestUpdate(container, {
      onComplete: function () {
        Control.Modal.refresh();
      }
    });
  }, createILM:      function (dsn, module, container) {
    let url = new Url("system", "doCreateElasticILM");
    url.addParam("dsn", dsn);
    url.addParam("setup_module", module);
    url.requestUpdate(container, {
      onComplete: function () {
        Control.Modal.refresh();
      }
    });
  }, deleteIndex:    function (dsn, module, container) {
    Modal.confirm($T("ElasticObjectManager-msg-Delete index"), {
      onOK: function () {
        let url = new Url("system", "doDeleteElasticIndex");
        url.addParam("dsn", dsn);
        url.addParam("setup_module", module);
        url.requestUpdate(container, {
          onComplete: function () {
            Control.Modal.refresh();
          }
        });
      }
    });
  }, deleteTemplate: function (dsn, module, container) {
    Modal.confirm($T("ElasticObjectManager-msg-Delete template"), {
      onOK: function () {
        let url = new Url("system", "doDeleteElasticTemplate");
        url.addParam("dsn", dsn);
        url.addParam("setup_module", module);
        url.requestUpdate(container, {
          onComplete: function () {
            Control.Modal.refresh();
          }
        });
      }
    });
  }, deleteILM:      function (dsn, module, container) {
    Modal.confirm($T("ElasticObjectManager-msg-Delete ILM"), {
      onOK: function () {
        let url = new Url("system", "doDeleteElasticILM");
        url.addParam("dsn", dsn);
        url.addParam("setup_module", module);
        url.requestUpdate(container, {
          onComplete: function () {
            Control.Modal.refresh();
          }
        });
      }
    });
  },
};
