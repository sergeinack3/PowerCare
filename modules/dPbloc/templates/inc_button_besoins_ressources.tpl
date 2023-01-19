{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=type value=operation_id}}
{{mb_default var=usage_bloc value=0}}
{{mb_default var=usage value=0}}
{{if $usage_bloc}}
  {{assign var=usage value=1}}
{{/if}}
{{mb_default var=from_dhe value=0}}

{{unique_id var=uniq_id}}

<script>
  window.checking_ressources = window.checking_ressources || [];
  // Dans le cas d'une intervention, il faut vérifier
  // que pour chaque type de ressource exprimé par un besoin, une ressource
  // au moment de l'intervention est disponible.
  
  Main.add(function() {
    {{if $type == "operation_id"}}
      checkRessources("{{$object_id}}");
    {{/if}}
    window.besoins_non_stored = [];
  });

  editBesoins = function (object_id) {
    var url = new Url("bloc", "ajax_edit_besoins");
    url.addParam("type", "{{$type}}");
    url.addParam("object_id", object_id);
    url.addParam("usage", "{{$usage}}");
    url.addParam("usage_bloc", "{{$usage_bloc}}");
    url.requestModal(500, 380, {showReload: true});

    url.modalObject.observe("afterClose", function() {
      {{if !$object_id}}
        {{if $type == "operation_id"}}
          var form = getForm("editOp");
        {{elseif $type == "protocole_id"}}
          var form = getForm("editProtocole");
        {{/if}}
        if (form) {
          // Un protocole a pu être appliqué, donc garder les besoins
          $V(form._types_ressources_ids, window.besoins_non_stored.join(","));
        }
      {{/if}}
      // Arprès fermeture de la modale, on réactualise
      // la couleur de la bordure des boutons
      {{if $type == "operation_id"}}
        checkAllOps();
      {{/if}}
    });

  };
  
  synchronizeTypes = function(types) {
    window.besoins_non_stored = types.split(",");
  };

  checkAllOps = window.checkAllOps || function() {
    var objects_ids = [];
    $$(".bouton_materiel").each(function(button) {
      objects_ids.push(button.get("object_id"));
    });

    objects_ids = objects_ids.uniq();

    if (objects_ids.length) {
      objects_ids.each(function(object_id) {
        checkRessources(object_id);
      });
    }
  };

  checkRessources = window.checkRessources || function(object_id) {
    // Gestion de la concurrence pour la vérification du matériel pour la même intervention
    // Boutons présents 2 fois dans la DHE (mode normal et simplifié)
    if (window.checking_ressources && window.checking_ressources[object_id] == true) {
      return;
    }
    window.checking_ressources[object_id] = true;
    var url = new Url("bloc", "ajax_check_ressources");
    url.addParam("type", "{{$type}}");
    url.addParam("object_id", object_id);
    url.requestJSON(function(object) {
      // Ajout d'une bordure sur le bouton suivant l'état des besoins
      $$(".ressource_bouton_"+object_id).each(function(button) {
        button.setStyle({border: "2px solid #"+object.color});
        {{if $object_id}}
          button.down('span').update("("+object.count+")");
        {{/if}}
      });
      {{if $from_dhe}}
      if (object.color == "a00") {
        alert($T("CBesoinRessource-_missing_materiel"));
      }
      {{/if}}
      window.checking_ressources[object_id] = false;
    });
  }
</script>

<button type="button" class="search bouton_materiel me-tertiary ressource_bouton_{{$object_id}}" data-object_id="{{$object_id}}"
        onclick="editBesoins('{{$object_id}}');">Matériel <span></span></button>
