/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ExamGir = {
  /**
   * Liste des variables nécessitant un sous-codage
   */
  variables_ss_codage:       [
    'coherence',
    'orientation',
    'elimination',
    'toilette',
    'alimentation',
    'habillage'
  ],
  /**
   * Mets à jour le score GIR en fonction des réponses
   */
  updateScoreGir:            function () {
    var form_fs = $$('fieldset.fs_exam_gir');
    form_fs.forEach(
      function (elt) {
        this.ExamGir.calculCodageIntermediaire(elt);
      }
    );
    ExamGir.calculCodageSsVariable();
    ExamGir.calculScoreGir();
  },
  /**
   * Calcule le groupe iso-ressources correspondant
   */
  calculScoreGir:            function () {
    var codage = getForm('editScoreGIR');
    new Url('dPcabinet', 'ajax_compute_score_gir')
      .addFormData(codage)
      .requestJSON(
        function (response) {
          $V('editScoreGIR_score_gir', response);
        }
      )
  },
  /**
   * Mets à jour les modalités (STCH) et le codage des variables
   *
   * @param fieldset
   */
  calculCodageIntermediaire: function (fieldset) {
    var variable = fieldset.getAttribute('name').replace('_fs', ""),
      codage_c = fieldset.up('.set-checkbox-container').select('.set-checkbox-c'),
      codage_a = fieldset.up('.set-checkbox-container').select('.set-checkbox-a'),
      count = 0,
      input = $('codage_' + variable);
    fieldset.select('.hidden-stch').forEach(
      function (elt) {
        // On compte les cases cochées du fieldset
        if (!$V(elt)) {
          // 0 case cochée = C
          codage_c[0].checked = true;
          $V(input, "C");
          return;
        }
        count = $V(elt).split('|').length;
        if (count === 4) {
          // Toutes les cases cochées = A
          codage_a[0].checked = true
          $V(input, "A");
          //Decocher tout b
          var checkboxes_b = elt.up('.set-checkbox-container').select('.set-checkbox-b-container .set-checkbox')
          checkboxes_b.forEach(
            function (element) {
              element.checked = false;
              $V(element, 0);
            }
          )
        } else {
          // Toutes les autres combinaisons = B
          codage_a[0].checked = false
          codage_c[0].checked = false
          $V(input, "B");
        }
      }
    );
  },
  /**
   * Codage des variables sous-codées
   */
  calculCodageSsVariable:    function () {
    this.variables_ss_codage.forEach(
      function (variable) {
        var inputs = $$('td.ss_codage_' + variable);
        var concat = "";
        inputs.forEach(
          function (elt) {
            concat += $V(elt.down('input'));
          }
        );
        switch (variable) {
          default:
          case 'coherence':
          case 'orientation':
          case 'elimination':
            switch (concat) {
              case "AA":
                $V('codage_' + variable, 'A');
                break;
              case "AB":
              case "BA":
              case "BB":
                $V('codage_' + variable, 'B');
                break;
              default:
                $V('codage_' + variable, 'C');
                break;
            }
            break;
          case 'toilette':
            switch (concat) {
              case "AA":
                $V('codage_' + variable, 'A');
                break;
              case "CC":
                $V('codage_' + variable, 'C');
                break;
              default:
                $V('codage_' + variable, 'B');
                break;
            }
            break;
          case 'alimentation':
            switch (concat) {
              case "AA":
                $V('codage_' + variable, 'A');
                break;
              case "AC":
              case "BC":
              case "CA":
              case "CB":
              case "CC":
                $V('codage_' + variable, 'C');
                break;
              default:
                $V('codage_' + variable, 'B');
                break;
            }
            break;
          case 'habillage':
            switch (concat) {
              case "AAA":
                $V('codage_' + variable, 'A');
                break;
              case "CCC":
                $V('codage_' + variable, 'C');
                break;
              default:
                $V('codage_' + variable, 'B');
                break;
            }
            break;
        }
      }
    );
  },
  /**
   * Coche toutes les cases SCTH pour avoir le codage A
   *
   * @param modality
   */
  codageA:                   function (modality) {
    var toggle = $V(modality),
      cont = $(modality).up('.set-checkbox-container').select('.set-checkbox-b-container .set-checkbox'),
      checkbox_codage_c = $(modality).up('.set-checkbox-container').select('.set-checkbox-c'),
      hidden_input = $(modality).up('.set-checkbox-container').down('.hidden-stch');
    // On décoche la case 'C' si elle est cochée
    checkbox_codage_c[0].checked = !toggle;
    $V(hidden_input, (toggle) ? 's|t|c|h' : '')
    cont.each(
      // On décoche les cases STCH (mais on garde la valeur)
      function (element) {
        element.checked = false;
        $V(element, 0);
      }
    );
    this.updateScoreGir()
  },
  codageB:                   function (modality) {
    // Si A ou C coché on décoche et on vide la valeur
    var checkbox_codage_a = $(modality).up('.set-checkbox-container').select('.set-checkbox-a'),
      checkbox_codage_c = $(modality).up('.set-checkbox-container').select('.set-checkbox-c'),
      hidden_input = $(modality).up('.set-checkbox-container').down('.hidden-stch');
    if (checkbox_codage_a[0].checked) {
      checkbox_codage_a[0].checked = false
      $V(hidden_input, '')
    }
  },
  /**
   * Décoche toutes les cases SCTH pour avoir le codage C
   *
   * @param modality
   */
  codageC:                   function (modality) {
    var toggle = $V(modality),
      cont = $(modality).up('.set-checkbox-container').select('.set-checkbox-b-container .set-checkbox'),
      checkbox_codage_a = $(modality).up('.set-checkbox-container').select('.set-checkbox-a'),
      hidden_input = $(modality).up('.set-checkbox-container').down('.hidden-stch');
    // On décoche la case 'A' si elle est cochée
    checkbox_codage_a[0].checked = !toggle;
    $V(hidden_input, (toggle) ? '' : 's|t|c|h')
    cont.each(
      // On décoche les cases STCH
      function (element) {
        if (element.checked) {
          element.checked = false;
        }
        $V(element, 0);
      }
    );
    this.updateScoreGir()
  }
};
