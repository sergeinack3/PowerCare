/**
 * @package Mediboard\SallOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

TopHoraire = {
  operation_id: null,

  refresh: function(timing) {
    new Url('salleOp', 'ajax_top_horaire')
      .addParam('operation_id', this.operation_id)
      .addParam('timing', timing)
      .requestUpdate('top-horaire-' + this.operation_id + '-' + timing);
  },

  setupTopHoraire: function(timing, value) {
    var div_top_horaire = $('top-horaire-' + this.operation_id + '-' + timing);

    if (value) {
      if (div_top_horaire) {
        if (!div_top_horaire.hasClassName('top_horaire_locked')) {
          div_top_horaire.onclick = Prototype.emptyFunction();
        }
        div_top_horaire.removeClassName('top_horaire_to_fill');
        div_top_horaire.addClassName('top_horaire_filled');
      }
    }
    else {
      if (div_top_horaire) {
        if (!div_top_horaire.hasClassName('top_horaire_locked')) {
          div_top_horaire.onclick = function () {
            this.down('form').onsubmit();
          };
        }
        div_top_horaire.removeClassName('top_horaire_filled');
        div_top_horaire.addClassName('top_horaire_to_fill');
      }
    }
  }
};