{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Ressource = {
    editRessource: function(ressource_id, type_ressource_id) {
      var url = new Url("bloc", "ajax_edit_ressource");
      url.addParam("ressource_id", ressource_id);
      url.addParam("type_ressource_id", type_ressource_id);
      url.requestModal();
    },

    afterEditRessource: function(ressource_id, obj) {
      Control.Modal.close();
      TypeRessource.refreshListTypeRessources(obj.type_ressource_id);
    }
  };
  
  TypeRessource = {
    editTypeRessource: function(type_ressource_id) {
      var url = new Url("bloc", "ajax_edit_type_ressource");
      url.addParam("type_ressource_id", type_ressource_id);
      url.requestModal(400);
    },
    
    refreshListTypeRessources: function(type_ressource_id) {
      var url = new Url("bloc", "ajax_list_type_ressources");
      if (type_ressource_id) {
        url.addParam("type_ressource_id", type_ressource_id);
      }
      url.requestUpdate("list_type_ressources");
    },
    
    afterEditTypeRessource: function(type_ressource_id) {
      Control.Modal.close();
      this.refreshListTypeRessources(type_ressource_id);
    }
  };
  
  Indispo = {
    editIndispo: function(indispo_ressource_id) {
      var url = new Url("bloc", "ajax_edit_indispo");
      url.addParam("indispo_ressource_id", indispo_ressource_id);
      url.requestModal(400, 250);
    },
    refreshListIndispos: function(indispo_ressource_id, date_indispo) {
      var url = new Url("bloc", "ajax_list_indispos");
      if (indispo_ressource_id) {
        url.addParam("indispo_ressource_id", indispo_ressource_id);
      }
      if (date_indispo) {
        url.addParam("date_indispo", date_indispo);
      }
      url.requestUpdate("list_indispos");
    },
    afterEditIndispo: function(indispo_ressource_id) {
      Control.Modal.close();
      this.refreshListIndispos(indispo_ressource_id);
    }
  };
  
  updateSelected = function(table_name, tr) {
    $(table_name).select('tr').invoke("removeClassName", "selected");
    if (tr) {
      tr.addClassName("selected");
    }
  };

  refreshCommandes = function() {
    var url = new Url("bloc", "vw_idx_materiel");
    url.requestUpdate("list_commandes");
  };

  Main.add(function() {
    Control.Tabs.create("manage_ressources", true);

    var tab_name = Control.Tabs.loadTab("manage_ressources");
    if (tab_name == "list_type_ressources" || !tab_name) {
      TypeRessource.refreshListTypeRessources('{{$type_ressource_id}}');
    }
    else if (tab_name == "list_indispos") {
      Indispo.refreshListIndispos('{{$indispo_ressource_id}}','{{$date_indispo}}');
    }
    else {
      refreshCommandes();
    }
  })
</script>

<ul id="manage_ressources" class="control_tabs">
  <li onmousedown="TypeRessource.refreshListTypeRessources()">
    <a href="#list_type_ressources">{{tr}}CRessourceMaterielle.all{{/tr}}</a>
  </li>
  <li onmousedown="Indispo.refreshListIndispos()">
    <a href="#list_indispos">Indisponibilités</a>
  </li>
  <li onmousedown="refreshCommandes()">
    <a href="#list_commandes">Commandes</a>
  </li>
</ul>

<div id="list_type_ressources" style="display: none"></div>
<div id="list_indispos"        style="display: none"></div>
<div id="list_commandes"       style="display: none"></div>
