/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

InfoGroup = {
  infoServiceContainer: null,
  infoServiceDate:      null,
  infoServiceId:        null,

  infoServiceDelay:   null,
  infoServiceTimeout: null,

  infoGroupShowInactive: 0,

  setTabInformation: function (count) {
    if (!this.infoServiceContainer) {
      return false;
    }
    Control.Tabs.setTabCount(this.infoServiceContainer, count);
    Control.Tabs.getTabAnchor(this.infoServiceContainer)[(count > 0) ? 'removeClassName' : 'addClassName']('empty');
  },

  getInfoTypeViewRequest: function (modal) {
    return new Url('hospi', 'ajax_list_info_types')
      .addNotNullParam('modal', modal);
  },

  getViewRequest: function () {
    return new Url('hospi', 'ajax_vw_infos_group')
      .addNotNullParam('show_inactive', this.infoGroupShowInactive);
  },

  getEditViewRequest: function (forceType) {
    return new Url('hospi', 'ajax_modal_info_group')
      .addParam('force_type', forceType);
  },

  openInfoGroup: function () {
    this.getViewRequest()
      .requestModal(900, 700);
  },

  editInfoGroup: function (infoGroupId, options) {
    this.getEditViewRequest(0)
      .addParam('info_group_id', infoGroupId)
      .requestModal(700, 520, options ? options : {
        onClose: this.updateListInfosGroup.bind(this)
      });
  },

  loadListInfosGroup: function (show_inactive) {
    this.infoGroupShowInactive = show_inactive;
    this.updateListInfosGroup();
  },

  updateListInfosGroup: function () {
    this.getViewRequest()
      .requestUpdate('infos_group');
  },

  listInfoTypes: function () {
    if (window.Rafraichissement) {
      window.clearTimeout(Rafraichissement.handler_init);
    }
    this.getInfoTypeViewRequest(1)
      .requestModal(250, null, {
        onClose: function () {
          if (this.infoServiceId) {
            this.refreshInfoServices();
          }
          else {
            this.updateListInfosGroup();
          }
        }.bind(this)
      });
  },

  updateInfoTypes: function () {
    this.getInfoTypeViewRequest(null)
      .requestUpdate('info_types');
  },

  editInfoType: function (info_type_id) {
    new Url('hospi', 'ajax_edit_info_type')
      .addParam('info_type_id', info_type_id)
      .requestModal(null, null, {
        onClose: function () {
          this.updateInfoTypes();
        }.bind(this)
      });
  },

  listInfoServices:        function (container, currentDate, serviceId) {
    this.infoServiceContainer = container;
    this.infoServiceDate = currentDate;
    this.infoServiceId = serviceId;
    this.refreshInfoServices();
  },
  refreshInfoServices:     function () {
    window.clearTimeout((window.Rafraichissement) ? Rafraichissement.handler_init : this.infoServiceTimeout);
    new Url('hospi', 'information_service')
      .addParam('date', this.infoServiceDate)
      .addParam('service_id', this.infoServiceId)
      .addNotNullParam('show_inactive', this.infoGroupShowInactive.toString())
      .requestUpdate(this.infoServiceContainer, function () {
        if (!window.Rafraichissement) {
          this.infoServiceTimeout = this.refreshInfoServices.bind(this).delay(this.infoServiceDelay);
        }
        else {
          Rafraichissement.start();
        }
      }.bind(this));
  },
  infoServiceShowInactive: function (showInactive) {
    this.infoGroupShowInactive = showInactive;
    this.refreshInfoServices();
  },
  addInfoService:          function () {
    this.editInfoService(null);
  },
  editInfoService:         function (infoGroupId) {
    if (window.Rafraichissement) {
      window.clearTimeout(Rafraichissement.handler_init);
    }
    this.getEditViewRequest(1)
      .addParam('service_id', this.infoServiceId)
      .addNotNullParam('info_group_id', infoGroupId)
      .requestModal(700, 650, {
        onClose: function () {
          this.refreshInfoServices();
        }.bind(this)
      });
  },
};
