/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

AvisMaternite = {
  updater : null,
  frequency : null,

  init: function(frequency) {
    this.frequency = frequency || this.frequency;

    var url = new Url('urgences', 'ajax_avis_maternite');
    AvisMaternite.updater = url.periodicalUpdate('avis_maternite', {
      frequency: this.frequency
    });
  },

  start: function(delay, frequency) {
    this.stop();
    this.init.delay(delay, frequency);
  },

  stop: function() {
    if (this.updater) {
      this.updater.stop();
    }
  },

  retourUrgences: function(sejour_id) {
    new Url('urgences', 'ajax_retour_urgences')
      .addParam('sejour_id', sejour_id)
      .requestModal('600px');
  },

  submitRetour: function(form) {
    onSubmitFormAjax(form, function() {
      Control.Modal.close();

      AvisMaternite.start();

      if (window.Urgences && Urgences.tabs && window.MainCourante) {
        Urgences.tabs.setActiveTab('holder_main_courante');
        MainCourante.start();
      }

      if (window.Rafraichissement && Rafraichissement.tabs) {
        Rafraichissement.tabs.setActiveTab('urgence');
        Rafraichissement.init();
      }
    });
  }
};