/**
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
ImportCampaign = window.ImportCampaign || {
  editCampaign: function (campaign_id) {
    var url = new Url('import', 'vw_edit_import_campaign');
    url.addParam('campaign_id', campaign_id);
    url.requestModal('30%', '50%', {
      onClose: function () {
        getForm('search-import-campaign').onsubmit();
      }
    });
  },

  showCreateCampaign: function () {
    var url = new Url('import', 'vw_edit_import_campaign');
    url.requestModal('30%', '50%', {
      onClose: function () {
        getForm('search-import-campaign').onsubmit();
      }
    });
  },

  loadObjectTab: function (container, start = 0) {
    var url = new Url('import', 'ajax_list_campaign_objects');
    url.addParam('class_name', container.get('classe'));
    url.addParam('campaign_id', container.get('campaign'));
    url.addParam('show_errors', container.get('show_errors'));
    url.addParam('start', start);
    url.requestUpdate(container.id);
  },

  changeObjectPage: function (page, elem_id) {
    var container = $(elem_id);
    ImportCampaign.loadObjectTab(container, page);
  },

  resetEntities: function (campaign_id, type) {
    if (confirm($T('CImportEntity-Ask-Delete all entities for type', type)))
    {
      var url = new Url('import', 'do_reset_entities', 'dosql');
      url.addParam('campaign_id', campaign_id);
      url.addParam('type', type);
      url.requestUpdate('systemMsg', {
        method: 'post', onComplete: function () {
          getForm('search-campaign-objects').onsubmit()
        }
      });
    }
  },

  refreshCampaign: function (module, campaign_id) {
    new Url(module, 'vw_import_fw')
      .addParam('import_campaign_id', campaign_id)
      .requestUpdate('vw_import_fw');
  }
};
