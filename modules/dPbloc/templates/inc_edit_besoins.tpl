{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  {{if !$usage || $usage_bloc}}
    Main.add(function() {
      var form = getForm("addBesoin");
      var url = new Url("system", "ajax_seek_autocomplete");
      url.addParam("object_class", "CTypeRessource");
      url.addParam("field", "libelle");
      url.addParam("where[group_id]", "{{$g}}");
      
      url.autoComplete(form.elements.libelle, "besoins_area", {
        minChars: 3,
        method: "get",
        select: "view",
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          var id = selected.get("id");
          if (!id) {
            return;
          }
          {{if !$object_id}}
             addBesoinNonStored(id);
             $V(field, "");
          {{else}}          
            $V(field.form.type_ressource_id, id);
            field.form.onsubmit();
          {{/if}}
        }
      });
      {{if !$object_id}}
        refreshBesoinsNonStored();
      {{/if}}
    });
  {{/if}}
  
  onSubmitBesoins = function(form) {
    return onSubmitFormAjax(form, reloadModal);
  };
  
  onDelBesoin = function(besoin_id, nom) {
    var form = getForm("delBesoin");
    $V(form.besoin_ressource_id, besoin_id);
    confirmDeletion(form, {objName: nom, ajax: 1}, {onComplete: reloadModal});
  };
  
  onDelUsage = function(usage_id, nom) {
    var form = getForm("delUsage");
    $V(form.usage_ressource_id, usage_id);
    confirmDeletion(form, {objName: nom, ajax: 1}, {onComplete: reloadModal});
  };
  
  reloadModal = function() {
    getForm('delBesoin').up('div.modal').down('button.change').click();
  };
  
  addBesoinNonStored = function(type_ressource_id) {
    window.besoins_non_stored.push(type_ressource_id);
    refreshBesoinsNonStored();
  };
  
  delBesoinNonStored = function(type_ressource_id) {
    window.besoins_non_stored.splice(window.besoins_non_stored.indexOf(type_ressource_id), 1);
    refreshBesoinsNonStored();
  };
  
  refreshBesoinsNonStored = function() {
    var url = new Url("bloc", "ajax_list_besoins_non_stored");
    url.addParam("types_ressources_ids", window.besoins_non_stored.join(","));
    url.addParam("type", '{{$type}}');
    url.requestUpdate("list_besoins");
  };
  
  showPlanning = function(type_ressource_id, operation_id, usage_ressource_id, besoin_ressource_id, usage) {
    var url = new Url("bloc", "ajax_vw_planning_ressources");
    url.addParam("besoin_ressource_id", besoin_ressource_id);
    url.addParam("usage_ressource_id", usage_ressource_id);
    url.addParam("type_ressource_id", type_ressource_id);
    url.addParam("operation_id", operation_id);
    url.addParam("usage", usage);
    url.modal();
    url.modalObject.observe("afterClose", reloadModal);
  }
</script>

<form name="delBesoin" method="post">
  <input type="hidden" name="m" value="bloc" />
  <input type="hidden" name="dosql" value="do_besoin_ressource_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="besoin_ressource_id" />
</form>

<form name="delUsage" method="post">
  <input type="hidden" name="m" value="bloc" />
  <input type="hidden" name="dosql" value="do_usage_ressource_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="usage_ressource_id" />
</form>

<table class="tbl">
  <tr>
    <th colspan="{{if $type == "operation_id"}}3{{else}}2{{/if}}">
      {{if !$usage || $usage_bloc}}
        <div style="float: right;">
          <form name="addBesoin" method="post" onsubmit="onSubmitBesoins(this)">
            <input type="hidden" name="m" value="bloc" />
            <input type="hidden" name="dosql" value="do_besoin_ressource_aed" />
            <input type="text" name="libelle" class="autocomplete" />
            <input type="hidden" name="{{$type}}" value="{{$object_id}}"/>
            <input type="hidden" name="type_ressource_id" />
          </form>
          <div id="besoins_area" style="text-align: left;" class="autocomplete"></div>
        </div>
      {{/if}}
      Liste des besoins
    </th>
  </tr>
  <tbody id="list_besoins">
    {{if $object_id}}
      {{mb_include module=bloc template=inc_list_besoins}}
    {{/if}}
  </tbody>
</table>
