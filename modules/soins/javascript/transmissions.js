/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Transmission = {
  form: null,

  init: function(form_name) {
    this.form = getForm(form_name);
  },
  copyValues: function (button) {
    var fields = ['data', 'action', 'result'];

    var is_macrocible = this.form.select('textarea').length === 1;

    fields.forEach(
      (function (value) {
        this.form.elements[is_macrocible ? 'text' :  ('_text_' + value)].value +=
          $$('#transmission-'+button.dataset.key+' .copy-'+value)[0].textContent.trim();
      }).bind(this)
    );
  }
};