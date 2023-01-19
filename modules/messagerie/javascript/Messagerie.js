/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Messagerie = {
  account_url: null,
  canMessageBackgroundTask: true,
  shortcut_container_id: 'messagerie-external-navigation-container',
  external_counter_id: 'messagerie-external-total-counter',

  internal_container_id: '',
  internal_counter_id: 'messagerie-internal-total-counter',

  laboratory_countainer_id: '',
  laboratory_counter_id: 'messagerie-laboratories-total-counter',

  initExternalMessagerie: (element_id, counter_id) => {
    if (element_id) {
      Messagerie.shortcut_container_id = element_id;
    }

    if (counter_id) {
      Messagerie.external_counter_id = counter_id;
    }

    let container = $(Messagerie.shortcut_container_id);

    const ul = DOM.ul({id: 'messagerie-external-accounts', className: 'messagerie-menu', style: 'display:none;'});
    document.body.insert(ul);

    Messagerie.loadExternalMessagerieContent(ul.id);

    Event.observe(container, 'click', ObjectTooltip.createDOM.bind(ObjectTooltip, container, ul, {duration: 0, newHideSystem: true, offsetLeft: false, borderless: true}));
  },

  initLaboratories: (element_id, counter_id) => {
    Messagerie.laboratory_countainer_id = element_id;
    Messagerie.laboratory_counter_id = counter_id;
  },

  loadExternalMessagerieContent: (container) => {
    let url = new Url('messagerie', 'ajax_reload_external');
    url.requestUpdate(container);
  },

  periodicalCount: (internal_element_id, internal_counter_id) => {
    Messagerie.internal_container_id = internal_counter_id;
    Messagerie.internal_counter_id = internal_counter_id;

    if (App.config.internal_mail_refresh_frequency > 0) {
      new PeriodicalExecuter(Messagerie.refreshShortcut, (App.config.internal_mail_refresh_frequency / 1000));
    }
  },

  refreshShortcut: () => {
    if (!Messagerie.canMessageBackgroundTask) {
      return;
    }

    new Url('messagerie', 'ajax_get_messagerie_info').addParam('session_no_revive', '1').requestJSON(function (data) {
      Messagerie.processData(data);
    });
  },

  processData: (data) => {
    if (data === null || Object.isUndefined(data)) {
      return Messagerie.canMessageBackgroundTask = false;
    }

    Object.keys(data).each(function(category) {
      if (category == 'external') {
        Messagerie.updateCounter($(Messagerie.external_counter_id), 'total', parseInt(data['external']['total']));

        Object.keys(data[category]).each(function(key) {
          Messagerie.updateCounter($('messagerie-' + category + '-' + key  + '-total-counter'), key, parseInt(data[category][key]));
        });
      }
      else if (category == 'internal') {
        Messagerie.updateCounter($(Messagerie.internal_counter_id), 'total', parseInt(data[category]['total']));
      } else if (category == 'laboratories') {
        Messagerie.updateCounter($(Messagerie.laboratory_counter_id), 'total', parseInt(data[category]['total']));
      }
    });
  },

  updateCounter: (counter, index, value) => {
    if (counter) {
      if (value > 99) {
        value = '99+';
      }

      counter.update(value);
      if (value) {
        counter.show();
        if (index == 'total') {
          counter.up().removeClassName('none');
        }
      } else {
        counter.hide();
        if (index == 'total') {
          counter.up().addClassName('none');
        }
      }
    }
  },

  openModal: function(account_guid) {
    var url = new Url('messagerie', 'ajax_view_messagerie');
    url.addParam('account_guid', account_guid);
    url.modal({width: 1200, height: 800});
  },

  manageAccounts: function() {
    Messagerie.account_url = new Url('messagerie', 'ajax_manage_accounts');
    Messagerie.account_url.requestModal(500);
  },

  refreshManageAccounts: function() {
    Messagerie.account_url.refreshModal();
  },

  addAccount: function() {
    var url = new Url('messagerie', 'ajax_add_account');
    url.requestModal(500, 600, {onClose: Messagerie.refreshManageAccounts.curry()});
  },

  linkMail: function (mail_id, mail_type) {
    new Url('messagerie', 'viewLink')
      .addParam('mail_id', mail_id)
      .addParam('mail_type', mail_type)
      .requestModal(1000, 600);
  }
};
