/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * JS function Exchange Source
 */
ExchangeSource = {
  status_color  : ["red", "orange", "limegreen"],
  sources_actif : {},

  resfreshImageStatus: function(element, actor_actif, actor_parent_class) {
    if (!element.get('id')) {
      return;
    }

    var url = new Url("system", "ajax_get_source_status");

    element.title = "";

    url.addParam("source_guid", element.get('guid'));
    url.requestJSON(function(status) {
      if (actor_parent_class) {
        var link = $('tabs-actors').select("a[href=#"+actor_parent_class+"s]")[0];

        if (!ExchangeSource.sources_actif[actor_parent_class]) {
          ExchangeSource.sources_actif[actor_parent_class] = 0;
        }

        if (actor_actif == 1 && status.reachable != 2) {
          ExchangeSource.sources_actif[actor_parent_class] = ExchangeSource.sources_actif[actor_parent_class]+1;

          link.addClassName('wrong');
        }

        if (!ExchangeSource.sources_actif[actor_parent_class]) {
          link.removeClassName('wrong');
        }
      }

      element.setStyle({color:ExchangeSource.status_color[status.reachable]});

      element.onmouseover = function() {
        ObjectTooltip.createDOM(element, 
          DOM.div(null, 
            DOM.table({className:"main tbl", style:"max-width:350px"},
              DOM.tr(null,
                DOM.th(null, status.type)
              ),
              DOM.tr(null,
                DOM.td({className:"text"},
                  DOM.strong(null, "Nom : "), status.name)
              ),
              DOM.tr(null,
                DOM.td({className:"text"}, 
                  DOM.strong(null, "Message : "), status.message)
             ), 
             DOM.tr(null,
             DOM.td({className:"text"},
               DOM.strong(null, "Temps de réponse : "), status.response_time, " ms")
           )
           )
         ).hide()) 
      };
    });
  },

  manageFiles: function (source_guid) {
    new Url("system", "ajax_manage_files")
      .addParam("source_guid", source_guid)
      .requestModal(1000, 500);
  },

  showDirectory: function (source_guid) {
    new Url("system", "ajax_manage_directory")
      .addParam("source_guid", source_guid)
      .requestUpdate("listDirectory");

    ExchangeSource.showFiles(source_guid);
  },

  changeDirectory: function (source_guid, directory) {
    new Url("system", "ajax_manage_directory")
      .addParam("source_guid", source_guid)
      .addParam("new_directory", directory)
      .requestUpdate("listDirectory");

    ExchangeSource.showFiles(source_guid, directory);
  },

  showFiles: function (source_guid, current_directory) {
    new Url("system", "ajax_manage_file")
      .addParam("source_guid"      , source_guid)
      .addParam("current_directory", current_directory)
      .requestUpdate("listFiles");
  },

  deleteFile: function (source_guid, file,current_directory) {
    new Url("system", "ajax_manage_file")
      .addParam("source_guid", source_guid)
      .addParam("current_directory", current_directory)
      .addParam("delete", true)
      .addParam("file", file)
      .requestUpdate("listFiles");
  },

  renameFile: function (source_guid, file, current_directory) {
    var new_name = prompt("Etes-vous sûr de vouloir renommer le fichier '"+file+"'\nEntrez le nouveau nom du fichier", "");
    if (new_name === null || new_name === "") {
      return false;
    }

    new Url("system", "ajax_manage_file")
      .addParam("source_guid", source_guid)
      .addParam("current_directory", current_directory)
      .addParam("file", file)
      .addParam("new_name", new_name)
      .addParam("rename", true)
      .requestUpdate("listFiles");
    return true;
  },

  addFileForm: function (source_guid, current_directory) {
    new Url('system', 'ajax_add_file')
      .addParam("source_guid", source_guid)
      .addParam("current_directory", current_directory)
      .requestModal(700, 300)
      .modalObject.observe("afterClose", function () {ExchangeSource.showFiles(source_guid, current_directory)});
  },

  closeAfterSubmit : function(message) {
    window.parent.$("systemMsg").update("");
    if (message["resultNumber"] != '0'  ) {
      window.parent.SystemMessage.notify(DOM.div({class:"info"}, message["result"]+" x"+message["resultNumber"]+"<br/>"), true);
    }
    var length = message["error"].length;
    if (length !==0) {
      for (var i =0; i<length; i++) {
        window.parent.SystemMessage.notify(DOM.div({class:"error"}, message["error"][i]+"<br/>"), true);
      }
    }
    window.parent.Control.Modal.close();
  },

  addInputFile : function(elt) {
    var name = elt.name;
    var number_file = name.substring(name.lastIndexOf("[")+1,name.lastIndexOf("]"));
    number_file = parseInt(number_file);
    number_file += 1;
    var form = elt.up();
    var br = form.insertBefore(DOM.br(), elt.nextSibling);
    form.insertBefore(DOM.input({type: "file", name: "import["+number_file +"]", size: 0, onchange: "ExchangeSource.addInputFile(this); this.onchange=''"})
      , br.nextSibling);
  },

  editSource : function (guid, light, source_name, type, object_guid, callback) {
    new Url("eai", "ajax_edit_source")
      .addParam("source_guid", guid)
      .addParam("source_name", source_name)
      .addParam("light", light)
      .addParam("object_guid", object_guid)
      .requestModal(600)
      .modalObject.observe("afterClose", callback);
  },


  SourceReachable : function (container){
    console.log(container);
    var list = container.select("i");
    if (!list[0].id) {
      return;
    }

    var list = container.select("i");
    console.log(list);
    console.log(ExchangeSource.sources_actif);
    ExchangeSource.sources_actif[list[0].id] = 0;
    console.log(ExchangeSource.sources_actif);
    console.log(list);
    ExchangeSource.testReachable(list[0], list[0].id);
  },

  testReachable : function (element, id_div) {

    var url = new Url("system", "ajax_get_source_reachable");

    element.title = "";
    url.addParam("source_guid", element.get('guid'));
    url.requestJSON(function(status) {
      console.log(status);
      element.setStyle({color:ExchangeSource.status_color[status.reachable]});
      var tdtime = element.up().next();
      $(tdtime).update(status.response_time);
      var tdmessage = tdtime.next();
      $(tdmessage).update(status.message);
      if (status.active == 1 && status.reachable == 2) {
        ExchangeSource.sources_actif[id_div] = ExchangeSource.sources_actif[id_div]+1;
      }

      if (status.active == 1 && status.reachable != 2) {
        console.log(Control.Tabs);
        // var anchor = Control.Tabs.getTabAnchor(id_div);
        var anchor = Control.Tabs.getTabAnchor(id_div);
        console.log(anchor);
        anchor.addClassName('wrong');
      }

      Control.Tabs.setTabCount(id_div, ExchangeSource.sources_actif[id_div]);
    })
  },

  SourceAvailability : function (container) {
    if (!container.id) {
      return;
    }

    var list = container.select("i");
    ExchangeSource.sources_actif[container.id] = 0;

    for(var i=0;i<list.length;i++) {
      ExchangeSource.testAvailability(list[i], container.id);
    }
  },

  testAvailability : function (element, id_div) {
    var url = new Url("system", "ajax_get_source_status");

    element.title = "";

    url.addParam("source_guid", element.get('guid'));
    url.requestJSON(function(status) {
      element.setStyle({color:ExchangeSource.status_color[status.reachable]});
      var tdtime = element.up().next();
      $(tdtime).update(status.response_time);
      var tdmessage = tdtime.next();
      $(tdmessage).update(status.message);
      if (status.active == 1 && status.reachable == 2) {
        ExchangeSource.sources_actif[id_div] = ExchangeSource.sources_actif[id_div]+1;
      }

      if (status.active == 1 && status.reachable != 2) {
        var anchor = Control.Tabs.getTabAnchor(id_div);

        anchor.addClassName('wrong');
      }

      Control.Tabs.setTabCount(id_div, ExchangeSource.sources_actif[id_div]);
    })
  },

  refreshUserSources: function () {
    new Url('mediusers', 'ajax_edit_exchange_sources')
      .requestUpdate('edit-exchange_source');
  },

  refreshExchangeSource: function (source_name, type, dontCloseModal) {
    var url = new Url("system", "ajax_refresh_exchange_source")
      .addParam("type", type)
      .addParam("exchange_source_name", source_name);
    if (dontCloseModal) {
      url.addParam('dont_close_modal', 1);
    }
    url.requestUpdate('exchange_source-' + source_name);
  },

  unlock: function (exchange_source_name, exchange_source_class) {
    console.log("in unlock js ftp");
    new Url("eai", "ajaxUnlockAdvancedSource")
      .addParam("exchange_source_name", exchange_source_name)
      .addParam("exchange_source_class", exchange_source_class)
      .requestModal(500, 400);
  }
};
