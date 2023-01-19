/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * JS function Interop Actor EAI
 */
InteropActor = {
    actor_guid: null,
    modal: null,

    editActor: function (actor_guid, actor_class, parent_class) {
        var url = new Url("eai", "ajax_edit_actor");
        url.addParam("actor_guid", actor_guid);
        url.addParam("actor_class", actor_class)
        url.requestModal("700");
        InteropActor.modal = url.modalObject;
        InteropActor.modal.observe("afterClose", function () {
            if (!actor_guid) {
                InteropActor.refreshActors(parent_class);
            } else {
                InteropActor.refreshActor(actor_guid, actor_class);
            }
        });
    },

    refreshActor: function (actor_guid, actor_class) {
        new Url("eai", "ajax_refresh_actor")
            .addParam("actor_guid", actor_guid)
            .addParam("actor_class", actor_class)
            .requestUpdate("line_" + actor_guid);
    },

    viewActor: function (actor_guid, actor_class, element) {
        if (element) {
            element.addUniqueClassName('selected', 'table');
        }

        new Url("eai", "ajax_view_actor")
            .addParam("actor_guid", actor_guid)
            .addParam("actor_class", actor_class)
            .requestUpdate("actor");
    },

    refreshActors: function (parent_class) {
        new Url("eai", "ajax_refresh_actors")
            .addParam("actor_class", parent_class)
            .requestUpdate(parent_class + "s", Control.Modal.close);
    },

    refreshActorsAndActor: function (actor_id) {
        InteropActor.refreshActor(InteropActor.actor_guid.split('-')[0] + "-" + actor_id);
    },

    receive: function (actor_guid) {
        new Url("eai", "ajax_receive_files")
            .addParam("actor_guid", actor_guid)
            .requestUpdate("utilities-exchange-source-receive");
    },

    refreshFormatsAvailable: function (actor_guid) {
        new Url("eai", "ajax_refresh_formats_available")
            .addParam("actor_guid", actor_guid)
            .requestUpdate("formats_available_" + actor_guid);
    },

    refreshExchangesSources: function (actor_guid, message) {
        new Url("eai", "ajax_refresh_exchanges_sources")
            .addParam("actor_guid", actor_guid)
            .addParam("message", message)
            .requestUpdate("exchanges_sources_" + actor_guid);
    },

    viewMessagesSupported: function (actor_guid, exchange_class, refresh) {
        var url = new Url("eai", "ajax_vw_messages_supported");
        url.addParam("actor_guid", actor_guid);
        url.addParam("exchange_class", exchange_class);
        url.requestModal("90%", "85%");
        if (refresh) {
            InteropActor.modal = url.modalObject;
            InteropActor.modal.observe("afterClose", function () {
                InteropActor.refreshFormatsAvailable(actor_guid);
            });
        }
    },

    callbackConfigsFormats: function (config_id, object) {
        var actor_guid = object.sender_class + "-" + object.sender_id;
        InteropActor.refreshConfigsFormats(actor_guid);
    },

    refreshConfigsFormats: function (actor_guid) {
        new Url("eai", "ajax_refresh_configs_formats")
            .addParam("actor_guid", actor_guid)
            .requestUpdate("configs_formats_" + actor_guid);
    },

    refreshConfigsSender: function (actor_guid) {
      new Url('eai', 'showConfigObjectValues')
        .addParam('object_guid', actor_guid)
        .requestUpdate("configs_contextuelle_" + actor_guid)
    },

    viewConfigsFormat: function (actor_guid, config_guid) {
        new Url("eai", "ajax_vw_configs_format")
            .addParam("actor_guid", actor_guid)
            .addParam("config_guid", config_guid)
            .requestUpdate("format_" + config_guid);
    },

    refreshConfigObjectValues: function (object_id, object_configs_guid) {
        new Url("system", "ajax_config_object_values")
            .addParam("object_id", object_id)
            .addParam("object_configs_guid", object_configs_guid)
            .requestUpdate("actor_config_" + object_id);
    },

  refreshConfigsReceiver: function (object_guid) {
    var object_id = object_guid.split('-')[1]
    new Url("eai", "showConfigObjectValues")
      .addParam("object_guid", object_guid)
      .requestUpdate("actor_config_contextuelle_" + object_id);
  },

    refreshTags: function (actor_guid) {
        new Url("eai", "ajax_refresh_tags")
            .addParam("actor_guid", actor_guid)
            .requestUpdate("tags_" + actor_guid);
    },

    refreshLinkedObjects: function (actor_guid) {
        new Url("eai", "ajax_refresh_linked_objects")
            .addParam("actor_guid", actor_guid)
            .requestUpdate("linked_objects_" + actor_guid);
    },

    refreshTransformations: function (actor_guid) {
        new Url("eai", "ajax_refresh_eai_transformations")
            .addParam("actor_guid", actor_guid)
            .requestUpdate("transformations_" + actor_guid);
    },

    refreshRoutes: function (actor_guid) {
        new Url("eai", "ajax_refresh_sender_routes")
            .addParam("actor_guid", actor_guid)
            .requestUpdate("routes_" + actor_guid);
    },

    refreshEAITransformations: function (actor_guid) {
        new Url("eai", "ajax_refresh_eai_transformations")
            .addParam("actor_guid", actor_guid)
            .requestUpdate("eai_transformations_" + actor_guid);
    },

    openExchangesReceiver: function (actor_guid, exchange_class) {
        new Url("eai", "ajax_refresh_exchanges_data_format")
            .addParam("actor_guid", actor_guid)
            .addParam("modal", 1)
            .addParam("exchange_class", exchange_class)
            .requestModal("95%", "95%");
    },

    openExchangesSender: function (actor_guid) {
        new Url("eai", "ajax_view_all_exchanges_filter")
            .addParam("actor_guid", actor_guid)
            .addParam("modal", 1)
            .requestModal("95%", "95%");
    },

    enableActors: function (actor_role, actor_class, enable) {
        new Url("eai", "controllers/do_enable_actors")
            .addParam("actor_role", actor_role)
            .addParam("actor_class", actor_class)
            .addParam("enable", enable)
            .requestUpdate("systemMsg", {
                onComplete: function () {
                    InteropActor.refreshActors(actor_class)
                }
            });
    },

    duplicateReceiver: function (receiver_guid) {
        Modal.confirm(
            $T('CInteropReceiver-confirm-Do you really duplicate this receiver ?'),
            {
                onOK: function () {
                    new Url("eai", "controllers/do_duplicate_receiver")
                        .addParam("receiver_guid", receiver_guid)
                        .requestUpdate("systemMsg", {
                            onComplete: function () {
                                InteropActor.refreshActors('CInteropReceiver')
                            }
                        });
                }
            }
        );
    },

    duplicateSender: function (sender_guid) {
        Modal.confirm(
            $T('CInteropSender-confirm-Do you really duplicate this sender ?'),
            {
                onOK: function () {
                    new Url("eai", "controllers/do_duplicate_sender")
                        .addParam("sender_guid", sender_guid)
                        .requestUpdate("systemMsg", {
                            onComplete: function () {
                                InteropActor.refreshActors('CInteropSender')
                            }
                        });
                }
            }
        );
    },

    addProfilSupportedMessage: function () {
        new Url("eai", "ajax_add_profil_supported_messages")
            .requestUpdate("add_profil_supported_messages");
    },

    modeEasy: function (type) {
        if (type == "receiver") {
            new Url("eai", "ajax_create_receiver_easy")
                .requestModal("80%", "80%", {showReload: false});
        } else {
            new Url("eai", "ajax_create_sender_easy")
                .requestModal("80%", "80%");
        }
    },

    showExchangeReceiver: function (actor_guid, object) {
        Control.Modal.close();

        new Url("eai", "ajax_create_exchange_easy")
            .addParam("actor_guid", object._guid)
            .requestModal("80%", "80%", {
                showReload: false, onClose: function () {
                    InteropActor.refreshActors("CInteropReceiver");
                }
            });
    },

    refreshExchangeReceiver: function (actor_guid) {
        new Url("eai", "ajax_create_exchange_easy")
            .addParam("actor_guid", actor_guid)
            .requestUpdate("exchanges");
    },

    refreshSourceReceiver: function (actor_guid) {
        new Url("eai", "ajax_create_source_easy")
            .addParam("actor_guid", actor_guid)
            .requestUpdate("source");
    },

    refreshConfigurationReceiver: function (actor_guid) {
        new Url("eai", "ajax_create_config_easy")
            .addParam("actor_guid", actor_guid)
            .requestUpdate("configs_receiver");
    },

    showCategory: function (category_name) {
        $$(".category_" + category_name).each(function (form) {
            form.toggle();
        });
    },

    checkCategory: function (family_name, category_name, actor_guid, element) {
        var value_toggle = element.getAttribute("value");

        var toggle = null;
        if (value_toggle == 1) {
            element.setAttribute("value", 0);
            element.setAttribute("class", "fa fa-toggle-off");
            element.style.color = "";
            toggle = 0;
        } else {
            element.setAttribute("value", 1);
            element.setAttribute("class", "fa fa-toggle-on");
            element.style.color = "#449944";
            toggle = 1;
        }

        new Url("eai", "controllers/do_add_messages_supported")
            .addParam("family_name", family_name)
            .addParam("category_name", category_name)
            .addParam("actor_guid", actor_guid)
            .addParam("toggle", toggle)
            .requestUpdate("systemMsg", {
                onComplete: function () {
                    InteropActor.refreshSummaryReceiver(actor_guid)
                }
            });
    },

    chooseTypeSource: function (actor_guid, uid, source) {
        var form = getForm("create_source_" + actor_guid + "_" + uid);
        form.elements["@class"].value = source;
        form.elements["submit_button"].disabled = false;
    },

    onSubmitObjectConfigs: function (oForm, actor_guid) {
        return onSubmitFormAjax(oForm, function () {
            InteropActor.refreshConfigurationReceiver(actor_guid);
        });
    },

    refreshSummaryReceiver: function (actor_guid) {
        new Url("eai", "ajax_refresh_summmary_actor")
            .addParam("actor_guid", actor_guid)
            .requestUpdate("summary_actor")
    },

    refreshListActors: function (actor_class, parent_class, id_element_chevron) {
        // On affiche ou cache la liste
        var element = document.getElementById(id_element_chevron);
        var class_name_element = element.getAttribute('class');
        if (class_name_element.match(/circle-down/)) {
            element.setAttribute('class', 'fas fa-chevron-circle-up  notext me-tertiary');
            document.getElementById('list_actors_' + actor_class).style.display = 'contents';
        } else {
            element.setAttribute('class', 'fas fa-chevron-circle-down  notext me-tertiary');
            document.getElementById('list_actors_' + actor_class).style.display = 'none';
            return false;
        }


        var class_name_toggle = document.getElementById('toggleActor' + parent_class).getAttribute('class');
        var all_actors = null;
        if (class_name_toggle.match(/toggle-on/)) {
            all_actors = true;
        }

        new Url("eai", "ajax_refresh_actors_type")
            .addParam("actor_class", actor_class)
            .addParam("all_actors", all_actors)
            .requestUpdate("list_actors_" + actor_class)

        return false;
    },

    testAccessibilitySources: function (actor_guid) {
      var element = "";
      element.title = "";
        new Url("system", "ajax_get_source_reachable")
            .addParam("source_guid", actor_guid)
          .requestJSON(function (status) {
            element = document.getElementsByName(actor_guid)[0];
            element.setStyle({color: ExchangeSource.status_color[status.reachable]});
            var tdtime = element.up().next();
            $(tdtime).update(status.response_time);
            var tdmessage = tdtime.next();
            $(tdmessage).update(status.message);
            if (status.active == 1 && status.reachable == 2) {
              ExchangeSource.sources_actif[actor_guid] = ExchangeSource.sources_actif[actor_guid] + 1;
            }

            if (status.active == 1 && status.reachable != 2) {
              var anchor = Control.Tabs.getTabAnchor(actor_guid);
              anchor.addClassName('wrong');
            }

            Control.Tabs.setTabCount(actor_guid, ExchangeSource.sources_actif[actor_guid]);
          })
        return false;
    },

    updateMessageSupported: function (form) {
        new Url('eai', 'updateMessageSupported')
            .addFormData(form)
            .requestUpdate('systemMsg', {
                onComplete: function () {
                    if (form.elements.old_transaction) {
                        $V(form.elements.old_transaction, $V(form.elements.transaction))
                    }
                }
            });
    },

    viewINSActors: function (actor_class) {
        new Url("eai", "ajax_vw_ins_actors")
            .addParam("actor_class", actor_class)
            .requestModal("700");
    },

    migrationConfigs: function (form) {
        var button = document.getElementById('button_submit_migration_configs');
        button.addClassName("loading");

        new Url('eai', 'migrationConfigs')
            .addFormData(form)
            .requestUpdate('add_profil_supported_messages', {
                onComplete: function () {
                    button.removeClassName('loading');
                }
            });

        return false;
    }
};
