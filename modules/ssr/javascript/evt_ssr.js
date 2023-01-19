/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Evt_SSR = {
  saved_elements: [],                                                         //tableau contenant les champs préremplis

  toggleOtherReeducs: function(button) {
    var form = getForm('editEvenementSSR');
    form.select('.other_reeduc').invoke('toggle');
    Element.classNames(button).flip('up', 'down');
  },
  /**
   * Ouvre la modale permettant de propager les codes d'un patient sur ceux d'une même séance collective
   *
   * @param evenement_id int
   *
   * @return
   */
  spreadCodes: function(evenement_id) {
    this.saved_elements = [];
    $$('.change-nb-patient input').each(                                       //Parcours des Nb patients et intervenants présents
      function(input) {
        var _evenement_id = input.up('.change-nb-patient').get('evenement_id');
        if (!this.saved_elements[_evenement_id]) {
          this.saved_elements[_evenement_id] = [];
          this.saved_elements[_evenement_id]['other'] = [];
        }
        this.saved_elements[_evenement_id]['other'][input.id] = $V(input);
      }.bind(this)
    );
    $$('.codes_container textarea[name=\'_transmission\']').each(                     //Parcours des transmissions
      function(textarea) {
        var _evenement_id = textarea.up('.codes_container').get('evenement_id');
        if (!this.saved_elements[_evenement_id]) {
          this.saved_elements[_evenement_id] = [];
          this.saved_elements[_evenement_id]['other'] = [];
        }
        this.saved_elements[_evenement_id]['other'][textarea.id] = $V(textarea);
      }.bind(this)
    );
    $$('.editLineCsarr input[class=\'modulateur\']').each(                        //Parcours des modulateurs
      function(input) {
        var _evenement_id = input.up('.codes_container').get('evenement_id');
        var _code = input.up('.editLineCsarr').get('code');
        if (!this.saved_elements[_evenement_id][_code]) {
          this.saved_elements[_evenement_id][_code] = [];
        }
        this.saved_elements[_evenement_id][_code][input.id] = $V(input);
      }.bind(this)
    );
    $$('.editLineCsarr select[class=\'extension\']').each(                        //Parcours des extensions documentaires
      function(select) {
        var _evenement_id = select.up('.codes_container').get('evenement_id');
        var _code = select.up('.editLineCsarr').get('code');
        if (!this.saved_elements[_evenement_id][_code]) {
          this.saved_elements[_evenement_id][_code] = [];
        }
        this.saved_elements[_evenement_id][_code][select.id] = select.selectedIndex;
      }.bind(this)
    );
    new Url('ssr', 'ajax_edit_codes_patients')
      .addParam('evenement_id', evenement_id)
      .requestModal('33%','50%', {onClose:Control.Modal.refresh});
  },

  /**
   * Restaure les éléments à remplir dans le modale de seance collective, lors de la fermeture de la modale de propagationde codes
   *
   * @returns {boolean}
   */
  restoreSavedElements: function() {
    if (!Object.keys(this.saved_elements)) {
      return false;
    }
    this.saved_elements.forEach(
      function(_evenement, _evenement_id) {
        Object.keys(_evenement).each(
          function (_code) {
            Object.keys(_evenement[_code]).each(
              function (key) {
                var element = $(key);
                if (element) {
                  if (element.className == 'extension') {
                    element.selectedIndex = _evenement[_code][key];
                  }
                  else {
                    $V(element, _evenement[_code][key]);
                  }
                }
              }
            )
          }
        )
      }.bind(this)
    );
  },

  /**
   * Se déclenche lors de la propagation d'un code. Propage aussi sur saved_elements les champs préremplis de l'acte propagé.
   *
   * @param {int} evenement_ssr_id          L'id de l'evenement de gauche
   * @param {int} evenement_ssr_id_origine  L'id de l'evenement de droite
   * @param {str} acte_code                 Le nom du code propagé
   */
  spreadSavedElements: function(evenement_ssr_id, evenement_ssr_id_origine, acte_code) {
    if (!this.saved_elements[evenement_ssr_id]) {
      this.saved_elements[evenement_ssr_id] = [];
    }
    var code_cible = this.saved_elements[evenement_ssr_id][acte_code] = [];
    var origin_code = this.saved_elements[evenement_ssr_id_origine][acte_code];
    if (origin_code) {
      Object.keys(origin_code).each(
        function (key) {
          var new_id = key.split('-');
          code_cible[new_id[0] + '-' + acte_code + '-' + evenement_ssr_id] = origin_code[key];
        }
      )
    }
  },

  /**
   * Supprime l'acte selectionné
   */
  deletePatientActe: function(acte_id, acte_type, acte_key, acte_name) {
    this.resetFormPatientActe(acte_type, acte_key);
    var form = getForm('editPatientCodeLine'+acte_type);
    $V(form[acte_key], acte_id);
    confirmDeletion(form, {ajax: true, typeName:$T('CActe-The act'), objName:acte_name}, {onComplete: Control.Modal.refresh});
  },

  /**
   * réinitialise les champs du formulaire permettant d'ajouter/supprimer un acte
   *
   * @param acte_type string
   * @param acte_key int
   */
  resetFormPatientActe: function(acte_type, acte_key) {
    var form = getForm('editPatientCodeLine'+acte_type);
    $V(form[acte_key], '');
    $V(form.evenement_ssr_id, '');
    $V(form.code, '');
  },

  /**
   * Ajoute un acte à propager
   *
   * @param {int} acte_id
   * @param {str} acte_type
   * @param {str} acte_key
   * @param {str} acte_code                 Le nom du code propagé
   * @param {int} evenement_ssr_id          L'id de l'evenement de gauche
   * @param {int} evenement_ssr_id_origine  L'id de l'evenement de droite
   */
  addPatientActe: function(acte_id, acte_type, acte_key, acte_code, evenement_ssr_id, evenement_ssr_id_origine) {
    this.resetFormPatientActe(acte_type, acte_key);
    var form = getForm('editPatientCodeLine'+acte_type);
    $V(form.evenement_ssr_id, evenement_ssr_id);
    $V(form.code, acte_code);
    form.onsubmit();
    if(acte_type == 'csarr') {    //propage les champs préremplis, présents uniquement pour csarr
      this.spreadSavedElements(evenement_ssr_id, evenement_ssr_id_origine, acte_code);
    }
  },

  /**
   * Ajoute tous les acte propageables
   *
   * @param button
   */
  addAllPatientActes: function(button) {
    button.up('tr').down('.codesToSpread').childElements().each(
      function(child) {
        var acteButton = child.down('button');
        if (acteButton){
          acteButton.click();
        }
      }
    )
  },
  /**
   * Ajoute tous les acte propageables pour l'ensemble des types du patient
   *
   * @param button
   */
  addAllTypesPatientActes: function(button) {
    button.up('table').select("button.addAllActesButton").each(
      function(button) {
        button.click();
      }
    )
  },

  selectedOptionsDuplication: function () {
    var selected = getForm('duplicateSelectedEvent').period;
    var possible_all_selected = ($('planning-sejour').select('.seance_collective_id.selected').length > 0) ? 0 : 1;
    getForm('duplicateSelectedEvent').period.options[2].disabled = possible_all_selected ? false : true;
    getForm('duplicateSelectedEvent').period.options[3].disabled = possible_all_selected ? false : true;
    var options_selected = selected.selectedIndex;
    if (!possible_all_selected && (options_selected == 2 || options_selected == 3)) {
      selected.selectedIndex = 0;
    }
  },
  cleanCodes: function(form) {
    form.select('input.checkbox-prestas_ssr:checked').each(function(checkbox) {
      $V(checkbox, 0);
    });

    form.select('input.checkbox-other-prestas_ssr').each(function(input_other) {
      input_other.up().remove();
    });
  },
  showSSRPrioriteMsg: function(niveau, container) {
    if (container.get('can-be-displayed') === '0') {
      return true;
    }
    container.update(
      DOM.div(
        {
          className: "info"
        },
        $T('CElementPrescription.niveau_ssr_msg', $T('CElementPrescription.niveau_ssr.' + niveau))
      )
    );
    container.show();
  }
};
