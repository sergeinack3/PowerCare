/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

SourceIdentite = {
  patient_id: null,
  copying_prenom: false,
  callback: null,
  traits_stricts: [
    'nom_jeune_fille', 'prenom', 'prenoms',
    'naissance', 'sexe', '_pays_naissance_insee', 'lieu_naissance', 'cp_naissance',
    '_ins_type'
  ],

  /**
   *
   */
  openList: function() {
    new Url('patients', 'vw_sources_identite')
      .addParam('patient_id', this.patient_id)
      .requestModal('70%', '50%');
  },

  /**
   * Affiche les logs INSi d'un patient
   * @param patient_id
   */
  showLogINSi: function(patient_id) {
    new Url('ameli', 'viewListLog')
      .addParam('patient_id', patient_id)
      .popup("100%", "100%", "history_insi");
  },

  /**
   * Rafraîchit la liste des sources d'identité
   */
  refreshList: function() {
    new Url('patients', 'ajax_list_sources_identite')
      .addParam('patient_id', SourceIdentite.patient_id)
      .requestUpdate('sources_patient_area');
  },

  /**
   * Affichage de la widget d'upload de fichier si un type de justificatif est sélectionné
   *
   * @param select
   */
  toggleFile: function(select) {
    select.form.select('.justificatif_file').invoke($V(select) ? 'show' : 'hide');
  },

  /**
   * Vérification du formulaire avant enregistrement de la source d'identité
   *
   * @param form
   * @returns {Boolean|boolean}
   */
  onSubmit: function (form) {
    return onSubmitFormAjax(form, {
      onComplete: (function () {
        Control.Modal.close();

        if (this.callback) {
          this.callback();
        }
      }).bind(this)
    });
  },

  copyPrenom: function(element) {
    if (SourceIdentite.copying_prenom) {
      return;
    }

    SourceIdentite.copying_prenom = true;

    var split_prenoms = $V(element.form.prenoms).split(' ');

    switch (element.name) {
      default:
      case 'prenom_naissance':
        $V(element.form.prenoms, $V(element));

        // Retrait de la première entrée de la liste des prénoms
        split_prenoms.shift();

        // Ajout des autres prénoms à la liste
        if (split_prenoms.length) {
          $V(element.form.prenoms, $V(element.form.prenoms) + ' ' + split_prenoms.join(' '));
        }

        break;

      case 'prenoms':
        if (split_prenoms.length) {
          $V(element.form.prenom_naissance, split_prenoms[0]);
        }
    }

    SourceIdentite.copying_prenom = false;
  },

  copyData: function(data, is_diff_prenom) {
    let form                    = getForm('editFrm'),
        form_identito_vigilance = getForm('editFrmIdentitoVigilance');

    if (!form) {
      return;
    }

    $V(form._mode_obtention, 'insi');
    $V(form._previous_ins, JSON.stringify(data._previous_ins));
    $V(form._map_source_form_fields, 1);
    $V(form._force_manual_source, 0);

    Object.keys(data).each(function (_key) {
      if (form.elements[_key]) {
        $V(form.elements[_key], data[_key]);
      }
    });

    if (is_diff_prenom && form_identito_vigilance) {
      $V(form.prenom, $V(form_identito_vigilance.prenom));
      $V(form.prenoms, data._source_prenoms);
      $V(form._source_prenom, $V(form_identito_vigilance.prenom));
      $V(form._source_prenoms, data._source_prenoms);
    }

    Control.Modal.close();

    form.onsubmit();
  },

  copyFile: (elt_file) => {
    var form = getForm('addJustificatif');
    var file_name_copy = $('file_name_copy');

    $V(form._copy_file_id, elt_file.dataset.fileId);
    file_name_copy.removeClassName('empty');
    file_name_copy.update(elt_file.dataset.filename);

    Control.Modal.close();
  },

  selectSource: (source_identite_id) => {
    new Url('patients', 'do_patients_aed', 'dosql')
      .addParam('patient_id', SourceIdentite.patient_id)
      .addParam('source_identite_id', source_identite_id)
      .requestUpdate('systemMsg', {method: 'POST', onComplete: () => { document.location.reload(); }});
  },

  disableSource: (source_identite_id, mode_obtention) => {
    if (mode_obtention === 'insi' && !confirm($T('CSourceIdentite-Confirm disable insi source'))) {
      return;
    }

    new Url()
      .addParam('@class', 'CSourceIdentite')
      .addParam('source_identite_id', source_identite_id)
      .addParam('active', '0')
      .requestUpdate('systemMsg', {method: 'POST', onComplete: () => { document.location.reload(); }});
  },

  manageIdentityProofType: (select) => {
    SourceIdentite.toggleIdInterpreter(select);
    SourceIdentite.toggleValidateIdentity(select);
  },

  toggleIdInterpreter: (select) => {
    let active_identity_proof = select.options[select.selectedIndex].dataset.code;
    $$('.id-interpreter').invoke(
      (
        (active_identity_proof === 'ID_CARD')
        || (active_identity_proof === 'PASSEPORT')
        || (active_identity_proof === 'RESIDENT_PERMIT')
      ) ? 'show' : 'hide'
    );
  },

  toggleValidateIdentity: (select) => {
    let validate_identity_field = select.form.___source__validate_identity,
        tr = validate_identity_field.up('tr'),
        trust_level = select.options[select.selectedIndex].dataset.trustLevel,
        validate_identity = select.options[select.selectedIndex].dataset.validateIdentity;

    if (trust_level === '3') {
      // Haut niveau : on coche et on affiche la case à cocher
      tr.show();
      validate_identity_field.checked = validate_identity === '1';
      select.form._source__validate_identity.value = Number((validate_identity === '1'));
    }
    else {
      // Dans tous les autres cas, on décoche et on masque
      tr.hide();
      if (validate_identity_field.checked) {
        validate_identity_field.checked = false;
        select.form._source__validate_identity.value = Number(false);
      }
    }
  }
};
