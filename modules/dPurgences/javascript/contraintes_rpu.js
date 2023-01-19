/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ContraintesRPU = {
  contraintesProvenance: [],
  contraintesDestination: [],
  contraintesOrientation: [],

  updateProvenance: function(mode_entree, clearField) {
    var oSelect = document.editRPU._provenance;

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
  },

  checkObligatory : function(rpu_id, form, callback) {
    var url = new Url("urgences", "ajax_check_obligatory")
      .addParam("rpu_id", rpu_id);
    if (form && form.elements['mode_sortie']) {
      url.addParam('mode_sortie', $V(form.elements['mode_sortie']));
    }
    url.requestJSON(function (data) {
      if (data.length == 0) {
        callback();
      }
      else {
        var miss_input = [];
        for(var i=0;i<data.length;i++) {
          miss_input[i] = DOM.li(null, $T(data[i]));
        }
        Modal.alert(
          DOM.div(null,
            DOM.p(null, "Le paramétrage de votre établissement impose la saisie de certaines données ou actions."),
            DOM.p(null, "Veuillez renseigner les données suivantes :"),
          DOM.ul(null, miss_input)), {className: "modal alert big-info"});
      }
    });

    return false;
  },

  updateDestination: function(mode_sortie, clearField) {
    var oSelect = document.editRPUDest._destination;

    // Le champ peut être caché
    if (!oSelect) {
      return;
    }

    // On remet la valeur à zéro
    if (clearField) {
      oSelect.value = "";
    }

    if (mode_sortie == "") {
      $A(oSelect).each( function(input) {
        if (input.value !== '') {
          input.disabled = true;
        }
      });
      return;
    }

    var valeursPossibles = this.contraintesDestination[mode_sortie];

    if(!valeursPossibles){
      $A(oSelect).each( function(input) {
        input.disabled = true;
      });
      return;
    }

    $A(oSelect).each( function(input) {
      input.disabled = !valeursPossibles.include(input.value);
    });
  },

  updateOrientation: function(mode_sortie, clearField) {
    var oSelect = document.editRPUDest.orientation;

    // Le champ peut être caché
    if (!oSelect) {
      return;
    }

    // On remet la valeur à zéro
    if (clearField) {
      oSelect.value = "";
    }

    if (mode_sortie == "") {
      $A(oSelect).each( function(input) {
        if (input.value !== '') {
          input.disabled = true;
        }
      });
      return;
    }

    var valeursPossibles = this.contraintesOrientation[mode_sortie];

    if(!valeursPossibles){
      $A(oSelect).each( function(input) {
        input.disabled = true;
      });
      return;
    }

    $A(oSelect).each( function(input) {
      input.disabled = !valeursPossibles.include(input.value);
    });
  },

  //@todo a factoriser avec updateOrientation
  //Changement de l'orientation en fonction du mode sortie
  changeOrientation : function(form) {
    //Contrainte à appliquer pour l'orientation
    var contrainteOrientation = {
      "mutation"  : ["", "NA", "HDT", "HO", "SC", "SI", "REA", "UHCD", "MED", "CHIR", "OBST"],
      "transfert" : ["", "NA", "HDT", "HO", "SC", "SI", "REA", "UHCD", "MED", "CHIR", "OBST"],
      "transfert_acte" : ["", "NA", "HDT", "HO", "SC", "SI", "REA", "UHCD", "MED", "CHIR", "OBST"],
      "normal"    : ["", "NA", "FUGUE", "SCAM", "PSA", "REO"],
      "deces"     : ["", "NA"]
    };

    var orientation = form.elements.orientation;
    var mode_sortie = $V(form.elements.mode_sortie);

    // Aucun champ trouvé
    if (!orientation) {
      return true;
    }

    //Pas de mode de sortie, désactivation de tous les options
    if (!mode_sortie) {
      $A(orientation).each(function (option) {
        if (option.value !== '') {
          option.disabled = true;
        }
      });

      return true;
    }

    //Application des contraintes
    $A(orientation).each(function (option) {
      option.disabled = !contrainteOrientation[mode_sortie].include(option.value);
    });
    if (orientation[orientation.selectedIndex].disabled) {
      $V(orientation, "");
    }

    return true;
  }
};
