/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

InfoExamen = {
  /**
   * Formulaire de l'info
   */
  form: null,

  /**
   * Type d'examen concerné
   */
  type_examen: null,

  /**
   * Identifiant de l'établissement concerné pour l'autocomplete
   */
  group_id: null,

  /**
   * Initialisation des champs de formulaire
   */
  init: function () {
    if (this.type_examen === 'rayons_x') {
      this.form._hour_rayons_x.addSpinner({min: 0, max: 24, step: 1});
      this.form._minute_rayons_x.addSpinner({min: 0, max: 60, step: 1});
      this.form._seconde_rayons_x.addSpinner({min: 0, max: 60, step: 1});
    }

    var input_field = null;
    var field_view = null;
    var object_class = null;

    switch (this.type_examen) {
      case 'anapath':
      default:
        input_field = 'labo_anapath_id';
        field_view = '_labo_anapath_id_view';
        object_class = 'CLaboratoireAnapath';
        break;

      case 'labo':
        input_field = 'labo_bacterio_id';
        field_view = '_labo_bacterio_id_view';
        object_class = 'CLaboratoireBacterio';
        break;

      case 'rayons_x':
        input_field = 'ampli_id';
        field_view = '_ampli_id_view';
        object_class = 'CAmpli';
    }

    if (field_view && input_field) {
      new Url('system', 'ajax_seek_autocomplete')
        .addParam('object_class', object_class)
        .addParam('field', 'libelle')
        .addParam('input_field', field_view)
        .addParam("where[group_id]", this.group_id)
        .addParam('where[actif]', 1)
        .autoComplete(this.form[field_view], null,
          {
            minChars:           0,
            dropdown:           true,
            method:             'GET',
            afterUpdateElement: (function (input, selected) {
              $V(this.form[field_view], selected.down('.view').getText());
              $V(this.form[input_field], selected.get('id'));

              if (this.type_examen === 'rayons_x') {
                $V(this.form.unite_rayons_x, selected.down('.unite_rayons_x').dataset.unite_rayons_x);
                $V(this.form.unite_pds, selected.down('.unite_pds').dataset.unite_pds);
              }
            }).bind(this)
          }
        );
    }
  },

  /**
   * Construit le temps de rayons au format h:m:s
   */
  constructTimeRayonX: function () {
    var hours = $V(this.form._hour_rayons_x) ? $V(this.form._hour_rayons_x) : "0";
    if (hours < 10) {
      hours = "0" + hours;
    }
    var minutes = $V(this.form._minute_rayons_x) ? $V(this.form._minute_rayons_x) : "0";
    if (minutes < 10) {
      minutes = "0" + minutes;
    }
    var times = $V(this.form._seconde_rayons_x) ? $V(this.form._seconde_rayons_x) : "0";
    if (times < 10) {
      times = "0" + times;
    }
    $V(this.form.temps_rayons_x, hours + ":" + minutes + ":" + times)
  },
};
