/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ProtocoleRPU = {
  protocoles: {},
  contraintesProvenance: [],
  contraintesDestination: [],
  forbidden_values: ["group_id", "actif"],

  edit: function(protocole_rpu_id) {
    Form.onSubmitComplete = protocole_rpu_id ? Prototype.emptyFunction : ProtocoleRPU.onSubmitComplete;

    new Url("urgences", "ajax_edit_protocole_rpu")
      .addParam("protocole_rpu_id", protocole_rpu_id)
      .requestModal(800, 650, {onClose: ProtocoleRPU.refreshList});
  },

  onSubmitComplete: function(guid, object) {
    var id = guid.split("-")[1];
    ProtocoleRPU.edit(id);
  },

  refreshList: function() {
    new Url("urgences", "ajax_list_protocoles_rpu")
      .requestUpdate("protocoles_rpu");
  },

  applyProtocole: function() {
    var form = getForm("editRPU");

    var protocole = ProtocoleRPU.protocoles[$V(form.protocole_id)];

    Object.keys(protocole).each(function(key) {
      if (ProtocoleRPU.forbidden_values.indexOf(key) !== -1) {
        return;
      }

      var property;

      if (form.elements[key]) {
        property = key;
      }
      else if (form.elements["_" + key]) {
        property = "_" + key;
      }
      else if (key === "_mode_entree_id_view" && form.elements["_mode_entree_id_autocomplete_view"]) {
        // Cas particulier du mode d'entrée personnalisé
        property = "_mode_entree_id_autocomplete_view";
      }

      if (!property) {
        return;
      }

      var field = form.elements[property];

      if (property === "_docitems_guid") {
        updateDocItemsInput("CRPU", field, protocole[key]);
        return;
      }

      $V(field, protocole[key]);

      if (field.name === "box_id") {
        field.fire("protocole:change");
      }
    });

    Control.Modal.close();
  },

  cancelProtocole: function() {
    var form = getForm("editRPU");

    $V(form.protocole_id, "");

    Control.Modal.close();
  },
  updateProvenance: function(mode_entree, clearField) {
    var oSelect = document.editProtocoleRPU.provenance;

    // Le champ peut être caché
    if (!oSelect) {
      return;
    }

    // On remet la valeur à zéro
    if (clearField) {
      oSelect.value = "";
    }

    if (mode_entree == "") {
      $A(oSelect).each( function(input) {
        input.disabled = false;
      });
      return;
    }

    var valeursPossibles = this.contraintesProvenance[mode_entree];

    if (!valeursPossibles) {
      $A(oSelect).each( function(input) {
        input.disabled = true;
      });
      return;
    }

    $A(oSelect).each( function(input) {
      input.disabled = !valeursPossibles.include(input.value);
    });
  }
};
