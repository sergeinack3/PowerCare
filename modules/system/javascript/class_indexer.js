/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ClassIndexer = {
  /**
   *
   * @param autocomplete_input Input to autocomplete after search
   * @param input Input to update after clicking element
   * @param options
   */
  autocomplete: function (autocomplete_input, input, options) {
    options = Object.merge(options, {
      profile:       'className',
      width:         '400px',
      data:          'class',
      queryCallback: function (input, queryString) {
        return queryString;
      }
    });

    const class_autocomplete = new Url('system', 'autocompleteClasses');
    class_autocomplete.addParam('input_field', autocomplete_input.name);
    class_autocomplete.addParam('profile', options.profile);

    class_autocomplete.autoComplete(autocomplete_input, null, {
      minChars:      2,
      method:        'get',
      width:         options.width,
      callback:      options.queryCallback,
      updateElement: function (selected) {
        const _data = selected.get(options.data);

        if (_data) {
          $V(input, _data);
          $V(autocomplete_input, _data);
        }
      }
    });
  }
};
