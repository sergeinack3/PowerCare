/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * JS function Domain EAI
 */
Domain = {
  modal: null,
  url:   null,

  showDomain: function (domain_id, element) {
    if (element) {
      element.up("tr").addUniqueClassName('selected');
    }

    new Url("eai", "ajax_edit_domain")
      .addParam("domain_id", domain_id)
      .requestModal(800, 600);
  },

  showDetails: function (domain_id) {
    new Url("eai", "ajax_show_domain_details")
      .addParam("domain_id", domain_id)
      .requestUpdate("domain_details-" + domain_id);
  },

  createDomainWithIdexTag: function () {
    var url = new Url("eai", "ajax_add_domain_with_idex");
    url.requestModal(500, 300);
    Domain.modal = url.modalObject;
    Domain.modal.observe("afterClose", function () {
      Domain.refreshListDomains();
    });

    return false;
  },

  showDomainCallback: function (domain_id) {
    Domain.showDomain(domain_id);
  },

  refreshListDomains: function () {
    new Url("eai", "ajax_refresh_list_domains")
      .requestUpdate("vw_list_domains");
  },

  refreshListGroupDomains: function (domain_id) {
    new Url("eai", "ajax_refresh_list_group_domains")
      .addParam("domain_id", domain_id)
      .requestUpdate("vw_list_group_domains");

    Domain.refreshListDomains();
  },

  refreshListIncrementerActor: function (domain_id) {
    new Url("eai", "ajax_refresh_list_incrementer_actor")
      .addParam("domain_id", domain_id)
      .requestUpdate("vw_list_incrementer_actor");
  },

  editGroupDomain: function (group_domain_id, domain_id) {
    var url = new Url("eai", "ajax_edit_group_domain");
      url.addParam("group_domain_id", group_domain_id);
    url.addParam("domain_id", domain_id);
    url.requestModal(600);

    Domain.modal = url.modalObject;
    Domain.modal.observe("afterClose", function () {
      Domain.refreshListGroupDomains(domain_id);
    });

    return false;
  },

  editIncrementer: function (incrementer_id, domain_id) {
    new Url("dPsante400", "ajax_edit_incrementer")
      .addParam("incrementer_id", incrementer_id)
      .addParam("domain_id", domain_id)
      .requestModal(600, 500);
  },

  bindIncrementerDomain: function (domain_id, incrementer_id) {
    var oForm = getForm("editDomain");
    $V(oForm.incrementer_id, incrementer_id);

    onSubmitFormAjax(oForm, Domain.refreshIncrementerDomain.curry(domain_id));
  },

  bindActorDomain: function (actor_id, object) {
  },

  resolveConflicts: function (oForm) {
    new Url("eai", "ajax_resolve_conflicts")
      .addParam("domains_id", $V(oForm["domains_id[]"]).join("-"))
      .requestModal(600, 400);

    Domain.modal = url.modalObject;
    Domain.modal.observe("afterClose", function () {
      Domain.refreshListDomains();
    });

    return false;
  },

  selectMergeFields: function (oForm) {
    Domain.modal.close();

    new Url("eai", "ajax_select_merge_fields")
      .addFormData(oForm)
      .requestModal(600, 400, {method: "post", getParameters: {m: "eai", a: "ajax_select_merge_fields", dialog: 1}});

    Domain.modal = url.modalObject;
    Domain.modal.observe("afterClose", function () {
      Domain.refreshListDomains();
    });
    return false;
  },

  confirm: function () {
    Modal.confirm($('merge-confirm'), {onOK: Domain.perform});
    return false;
  },

  perform: function () {
    getForm("form-merge").onsubmit();
  },

  refreshIncrementerDomain: function (domain_id) {
    Control.Modal.close();
    new Url("eai", "ajax_refresh_domain_incrementer")
      .addParam("domain_id", domain_id)
      .requestUpdate("incrementer_domain");
  },

  refreshSuppressionIncrementerDomain: function (domain_id) {
    new Url("eai", "ajax_refresh_domain_incrementer")
      .addParam("domain_id", domain_id)
      .requestUpdate("vw_list_incrementer");
  },

  refreshCDomain: function (domain_id) {

    new Url("eai", "ajax_refresh_CDomain")
      .addParam("domain_id", domain_id)
      .requestUpdate("CDomain");
  },

  createDomain: function (domain_id) {
    Control.Modal.close();
    new Url("eai", "ajax_edit_domain")
      .addParam("domain_id", domain_id)
      .requestModal(800, 600);
  }
}
