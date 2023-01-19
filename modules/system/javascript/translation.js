/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Translation = {
  doTranslations: function (module) {
    var key = (module) ? '.translation-' + module : '.translation';

    var translations = [];
    $$(key).each(function (elem) {
      if (elem.checked) {
        translations.push(
          {key: elem.value, trad: elem.get('trad'), lang: elem.get('lang')}
        );
      }
    });

    var url = new Url('system', 'do_import_translations_overwrite', 'dosql');
    url.addParam('translations', Object.toJSON(translations));
    url.requestUpdate('systemMsg', {method: 'post', onComplete: function () {
      getForm('import-translations-form').onsubmit();
      }});
  },

  checkAllTranslation: function (checkbox, check_class) {
    $$('.' + check_class).each(function (elem) {
      elem.checked = checkbox.checked;
    });
  }
};