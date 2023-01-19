/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ParametrageMode = {
  //Contrainte à appliquer pour la destination
  contrainteDestination : {
    'mutation'  : ['', 1, 2, 3, 4],
    'transfert' : ['', 1, 2, 3, 4],
    'normal'    : ['', 0, 6, 7],
    'deces'     : ['', 0]
  },

  //Contrainte à appliquer pour l'orientation
  contrainteOrientation : {
    'mutation'  : ['', 'HDT', 'HO', 'SC', 'SI', 'REA', 'UHCD', 'MED', 'CHIR', 'OBST'],
    'transfert' : ['', 'HDT', 'HO', 'SC', 'SI', 'REA', 'UHCD', 'MED', 'CHIR', 'OBST'],
    'normal'    : ['', 'FUGUE', 'SCAM', 'PSA', 'REO'],
    'deces'     : ['']
  },

  editModePec : function(mode_pec_id) {
    this.editModePecDestination(mode_pec_id, 'pec');
  },

  editModeDestination : function(mode_dest_id) {
    this.editModePecDestination(mode_dest_id, 'destination');
  },

  editModePecDestination : function(mode_id, mode_type) {
    new Url('planningOp', 'vw_edit_mode_pec_destination')
      .addParam((mode_type === 'destination') ? 'mode_dest_id' : 'mode_pec_id', mode_id)
      .addParam('type', mode_type)
      .requestModal(null, null);
  },

  reloadListModeDestPec : function(type) {
    new Url('planningOp', 'vw_list_mode_pec_destination')
      .addParam('type', type)
      .requestUpdate((type === 'pec') ? 'tab-CModePECSejour' : 'tab-CModeDestinationSejour');
  },

  submitPecDestination : function(form, type) {
    return onSubmitFormAjax(form, function() {
      this.reloadListModeDestPec(type);
      Control.Modal.close();
    }.bind(this));
  },

  deletePecDestination : function(form, type) {
    return confirmDeletion(form,
      {ajax: true, typeName:'', objName:$V(form.code)},
      function() {
        this.reloadListModeDestPec(type);
        Control.Modal.close();
      }.bind(this)
    );
  },

  editModeEntreeSortie : function(mode_class, mode_id) {
    new Url('planningOp', 'edit_mode_entree_sortie_sejour')
      .addParam('mode_class', mode_class)
      .addParam('mode_id',    mode_id)
      .requestModal(600, 500);
  },

  importModeEntreeSortie : function(mode_class) {
    new Url('planningOp', 'import_mode_entree_sortie_sejour')
      .addParam('mode_class', mode_class)
      .popup(800, 600);
  },

  exportModeEntreeSortie : function(mode_class) {
    new Url('planningOp', 'export_mode_entree_sortie_sejour', 'raw')
      .addParam('mode_class', mode_class)
      .open();
  },

  editCharge : function(charge_id) {
    new Url('planningOp', 'edit_charge_price_indicator')
      .addParam('charge_id', charge_id)
      .requestModal(null, null);
  },

  importModeTraitement : function() {
    new Url('planningOp', 'import_mode_traitement_sejour')
      .popup(800, 600);
  },

  exportModeTraitement : function() {
    new Url('planningOp', 'export_mode_traitement_sejour', 'raw')
      .open();
  },

  submitSaveForm : function(form) {
    return onSubmitFormAjax(
      form,
      function(){
        Control.Modal.close();
        this.refreshLists();
      }.bind(this));
  },

  submitRemoveForm : function(form, objName) {
    return confirmDeletion(
      form,
      {ajax: true, typeName:'', objName: objName},
      function() {
        Control.Modal.close();
        this.refreshLists();
      }.bind(this));
  },

  refreshLists: function() {
    new Url('planningOp', 'vw_parametrage')
      .addParam('refresh_mode', 1)
      .requestUpdate('mode_parametrage_container');
  },

  changeDestination : function(form) {
    var destination = form.elements.destination;
    var mode_sortie = $V(form.elements.mode);

    // Aucun champ trouvé
    if (!destination) {
      return this;
    }

    //Pas de mode de sortie, activation de tous les options
    if (!mode_sortie) {
      $A(destination).each(function (option) {
        option.disabled = false
      });
      return this;
    }

    //Application des contraintes
    $A(destination).each(function (option) {
      option.disabled = !this.contrainteDestination[mode_sortie].include(option.value);
    }.bind(this));

    if (destination[destination.selectedIndex].disabled) {
      $V(destination, '');
    }

    if (!$V(destination) && destination.hasClassName('notNull') && (mode_sortie == 'deces' || mode_sortie == 'normal')) {
      $V(destination, '0');
    }

    return this;
  },

  changeOrientation : function(form) {
    var orientation = form.elements.orientation;
    var mode_sortie = $V(form.elements.mode);

    // Aucun champ trouvé
    if (!orientation) {
      return this;
    }

    //Pas de mode de sortie, activation de tous les options
    if (!mode_sortie) {
      $A(orientation).each(function (option) {
        option.disabled = false
      });

      return this;
    }

    //Application des contraintes
    $A(orientation).each(function (option) {
      option.disabled = !this.contrainteOrientation[mode_sortie].include(option.value);
    }.bind(this));
    if (orientation[orientation.selectedIndex].disabled) {
      $V(orientation, '');
    }

    return this;
  },

  toggleEtabExterne : function(form) {
    $$('.etab_externe').invoke($V(form.mode) === 'transfert' ? 'show' : 'hide');
    return this;
  },

  prepareDestinationEtabExt : function(objectClass) {
    new Url('etablissement', 'ajax_autocomplete_etab_externe')
      .addParam('field', 'etablissement_sortie_id')
      .addParam('input_field', 'etablissement_sortie_id_view')
      .addParam('view_field', 'nom')
      .autoComplete(getForm('edit-mode-' + objectClass).etab_externe_id_view, null, {
        minChars: 0,
        method: 'get',
        select: 'view',
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          var id = selected.getAttribute('id').split('-')[2];
          $V(getForm('edit-mode-' + objectClass).etab_externe_id, id);
          if ($('edit-mode-' + objectClass + '_destination')) {
            $V(getForm('edit-mode-' + objectClass).destination, selected.down('span').get('destination'));
          }
          this.changeDestination(form)
            .changeOrientation(form);
        }.bind(this)
      });
  }
};
