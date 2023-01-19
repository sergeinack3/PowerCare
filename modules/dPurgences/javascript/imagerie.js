/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Imagerie = {
  updater : null,
  frequency : null,
  
  init: function(frequency) {
    this.frequency = frequency || this.frequency;
    
    var url = new Url("dPurgences", "ajax_refresh_imagerie");
    Imagerie.updater = url.periodicalUpdate('imagerie', {
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

  resume: function() {
    if (this.updater) {
      this.updater.resume();
    }
  },
  filter: function(input, indicator) {
    $$("#imagerie tr").invoke("show");
    indicator = $(indicator);
    
    var term = $V(input);
    if (!term) return;
    
    if (indicator) {
      indicator.show();
      this.stop();
    }
    
    $$("#imagerie .CPatient-view").each(function(p) {
      if (!p.innerHTML.like(term)) {
        p.up("tr").hide();
      }
    });
  } 
};
