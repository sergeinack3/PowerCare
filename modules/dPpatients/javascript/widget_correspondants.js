/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Correspondants = window.Correspondants || Class.create({
  initialize: function (patient_id, options) {
    this.module = "patients";
    this.action = "widgetCorrespondants";

    this.patient_id = patient_id;
    this.options = Object.extend({
      container: null,
      popup:     false
    }, options || {});

    this.widget_id = "correspondants-patient_id-" + this.patient_id;

    if ($(this.widget_id)) {
      console.error("A widget with this id (" + this.widget_id + ") already exists");
    }

    var widget = '<div id="' + this.widget_id + '"></div>';

    if (this.options.container) {
      $(this.options.container).insert(widget);
    } else {
      document.write(widget);
    }

    $(this.widget_id).widget = this;
    //if (!this.options.popup) {
    this.refresh();
    /*}
    else {
      var button = new Element('button', {className: 'new', type: 'button'}).update("Correspondants médicaux");
      var that = this;
      button.observe('click', function() {that.popup()});
      $(this.widget_id).update(button);
    }*/
  },

  /*popup: function() {
    var url = new Url(this.module, this.action);
    url.addParam("patient_id", this.patient_id);
    url.addParam("widget_id", this.widget_id);
    url.popup(600, 400, "Correspondants");  
  },*/

  refresh: function () {
    new Url(this.module, this.action)
      .addParam("patient_id", this.patient_id)
      .addParam("widget_id", this.widget_id)
      .requestUpdate(this.widget_id);
  }
});
