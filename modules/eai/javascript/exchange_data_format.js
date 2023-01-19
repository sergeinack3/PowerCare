/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * JS function Exchange Data Format EAI
 */
ExchangeDataFormat = window.ExchangeDataFormat || {
  evenements : null,  
  target:      "exchange_data_format",
  modal:       null,

  editExchange : function (exchange_guid) {
    new Url("eai", "ajax_edit_exchange")
      .addParam("exchange_guid", exchange_guid)
      .requestModal(800, 600);
  },

  refreshExchanges : function(exchange_class, exchange_type, group_id){
    new Url("eai", "ajax_refresh_exchanges_data_format")
      .addParam("exchange_class", exchange_class)
      .addParam("exchange_type" , exchange_type)
      .addParam("group_id"      , group_id)
      .requestUpdate("exchanges", { onComplete : function() {
      if (!exchange_type) {
        return;
      }
      var form = getForm("filterExchange");
      
      if (form) {
        ExchangeDataFormat.refreshExchangesList(form);
      }
    } });
  },
  
  fillSelect : function(source, dest, mod_name) {
    var selected = $V(source);
    dest.update();
    dest.disabled = selected ? false : true;
    dest.insert(new Element('option', {value: ''}).update('&mdash; Liste des événements &mdash;'));
    dest.insert(new Element('option', {value: 'inconnu'}).update($T(mod_name+'-evt-none')));

    if (!Object.isArray(ExchangeDataFormat.evenements[selected])) {
      $H(ExchangeDataFormat.evenements[selected]).each(function(pair){
        var v = pair.key;
        dest.insert(new Element('option', {value: v}).update($T(mod_name+'-evt_'+selected+'-'+v)));
      });
    }
  },
  
  refreshExchangesList : function(form) {
    var url = new Url("eai", "ajax_refresh_echanges_data_format_list")
      url .addFormData(form)

    // Add null value for each elements disabled (clear elements in session)
      Array.from(form.getElements()).forEach(function(element) {
        if (!element.disabled) {
          return;
        }

        url.addParam(element.name, '')
      });
      url.requestUpdate("exchangesList");
    return false;
  },

  reprocessAndExchangeDetails : function (exchange_guid) {
    Control.Modal.close();
    ExchangeDataFormat.viewExchange(exchange_guid);
    ExchangeDataFormat.reprocessing(exchange_guid);
  },

  showFileHL7 : function (exchange_guid) {
    new Url("eai", "ajax_show_file_hl7")
      .addParam("exchange_guid", exchange_guid)
      .requestUpdate("tools-show-file");
    return false;
  },

  storeMessage : function (form) {
    new Url("hl7", "ajax_edit_message")
      .addFormData(form)
      .requestUpdate("systemMsg", {onComplete : function () {
        Control.Modal.close();
        ExchangeDataFormat.viewExchange(form.elements.exchange_guid.value);
      }});
    return false;
  },

  showLogModification : function (exchange_guid) {
    new Url("eai", "ajax_show_log_modification")
      .addParam("exchange_guid", exchange_guid)
      .popup(900, 530);
    return false;
  },
  
  viewExchange : function(exchange_guid) {
    new Url("eai", "ajax_vw_exchange_details")
      .addParam("exchange_guid", exchange_guid)
      .requestModal(900, 530);
  },
  
  reprocessing : function(exchange_guid){
    new Url("eai", "ajax_action_exchange")
      .addParam("exchange_guids", [exchange_guid])
      .addParam("action", 'reprocess')
      .addParam("quiet", 0)
      .requestUpdate("systemMsg", { onComplete:
        ExchangeDataFormat.refreshExchange.curry(exchange_guid)
    });
  },

  refreshExchange : function(exchange_guid){
    new Url("eai", "ajax_refresh_exchange")
      .addParam("exchange_guid", exchange_guid)
      .requestUpdate("exchange_"+exchange_guid);
  },
  
  treatmentExchanges : function(source_guid){
    new Url("eai", "ajax_treatment_exchanges")
      .addParam("source_guid", source_guid)
      .requestUpdate("CExchangeDataFormat-treatment_exchanges");
  },

  sendMessage : function(exchange_guid, callback){
    new Url("eai", "ajax_action_exchange")
      .addParam("exchange_guids", exchange_guid)
      .addParam('action', 'send')
      .addParam("quiet", 0)
      .requestUpdate("systemMsg",  callback || ExchangeDataFormat.refreshExchange.curry(exchange_guid));
  },
  
  changePage : function(page) {
    $V(getForm('filterExchange').page,page);
  },
  
  hide: function() {
    $(this.target).hide();    
  },
    
  show: function() {
    $(this.target).appear();    
  },
    
  toggle: function() {
    this[$(this.target).visible() ? "hide" : "show"]();
  },

  viewAllFilter: function(form) {
    var url = new Url("eai", "ajax_view_all_exchanges_filter");
    if (form) {
      url.addFormData(form);
    }
    url.requestUpdate("exchanges");
    return false;
  },

  viewAll: function(form) {
    var url = new Url("eai", "ajax_view_all_exchanges");
    if (form) {
      url.addFormData(form);
    }
    url.requestUpdate("vw_all_exchanges");
    return false;
  },

  viewAllTLFilter: function (form) {
    var url = new Url("eai", "ajax_view_all_exchanges_tl_filter");
    if (form) {
      url.addFormData(form);
    }
    url.requestUpdate("exchanges");
    return false;
  },

  viewAllTL: function (form) {
    var url = new Url("eai", "ajax_view_all_exchanges_tl");
    if (form) {
      url.addFormData(form);
    }
    url.requestUpdate("vw_all_exchanges");
    return false;
  },

  doesExchangeExist : function(exchange_class, exchange_id) {
    if (exchange_id) {
      new Url('eai', 'ajax_does_exchange_exist')
        .addParam('exchange_class', exchange_class)
        .addParam('exchange_id'   , exchange_id)
        .requestJSON(
          function(id) {
            if (id) {
              ExchangeDataFormat.viewExchange(exchange_class+"-"+id);
            }
            else {
              SystemMessage.notify("<div class='error'>"+$T('CExchangeDataFormat-doesnt-exist')+"</div>");
            }
        });
    }

    return false;
  },

  defineMasterIdexMissing : function(exchange_guid){
    var url = new Url("eai", "ajax_define_master_idex_missing")
      .addParam("exchange_guid", exchange_guid)
      .requestModal(400, 150);

    ExchangeDataFormat.modal = url.modalObject;
    ExchangeDataFormat.modal.observe("afterClose", function(){
      ExchangeDataFormat.refreshExchange(exchange_guid);
    });
  },

  refreshActiveMessageSupported : function(message_supported_id, family_name, category_name, uid, category_uid) {
    new Url('eai', 'ajax_refresh_active_message_supported_form')
      .addParam('message_supported_id', message_supported_id)
      .addParam('family_name', family_name)
      .addParam('category_name', category_name)
      .addParam('uid', uid)
      .addParam('category_uid', category_uid)
      .requestUpdate('actor_message_supported_' + uid);
  },

  fillMessageSupportedID : function(uid, category_uid, message_supported_id) {
    var form = getForm('editActorMessageSupported-'+uid);
    $V(form.message_supported_id, message_supported_id);

    ExchangeDataFormat.refreshActiveMessageSupported(message_supported_id, $V(form.profil), $V(form.transaction), uid, category_uid);
  },

  refreshExchangesTransport : function(exchange_class) {
    new Url("eai", "ajax_refresh_exchanges_transport")
      .addParam("exchange_class"   , exchange_class)
      .requestUpdate("exchanges");
  },

  refreshExchangesListTransport : function(form) {
    new Url("eai", "ajax_refresh_echanges_transport_list")
      .addFormData(form)
      .requestUpdate("exchangesTransportList");
    return false;
  },

  viewExchangeTransport: function (exchange_guid) {
    new Url("eai", "ajax_vw_exchange_transport_details")
      .addParam("exchange_guid", exchange_guid)
      .requestModal(900, 530);
  },

  refreshExchangeList: function (exchange_class) {
    new Url("eai", "ajax_refresh_exchanges")
      .addParam("exchange_class", exchange_class)
      .requestUpdate(exchange_class + "s", Control.Modal.close);
  },

  showReportCDA: function (exchange_cda_id) {
    new Url('eai', 'ajax_show_report_cda')
      .addParam('exchange_cda_id', exchange_cda_id)
      .requestModal('80%', '80%');
  }
};
