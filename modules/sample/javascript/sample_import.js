/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

SampleImport = window.SampleImport || {
  importCategories: function () {
    if (confirm($T('SampleCategoryImport-Ask-Import-category?'))) {
      const url = new Url('sample', 'importCategories', 'dosql');
      url.requestUpdate('result-import-categories', {method: "post"});
    }
  },

  importNationalities: function () {
    if (confirm($T('SampleNationalityImport-Ask-Import-nationality?'))) {
      const url = new Url('sample', 'importNationalities', 'dosql');
      url.requestUpdate('result-import-nationalities', {method: "post"});
    }
  },

  importMovies: function (base_url) {
    if (confirm($T('SampleMovieImport-Ask-Import-movies?'))) {
      let result_elem = $('result-import-movies');

      fetch(base_url + '/api/sample/movies/import', {method: 'POST'})
        .then(function (response) {
          result_elem.removeClassName('ajax-loading');

          // Reset element
          result_elem.innerHTML = '';

          if (response.ok) {
            response.text().then(function (content) {
              result_elem.insert(
                DOM.div({className: 'info'}, $T('SampleMovieImport-Msg-Count-movies-imported', content))
              );
            });
          } else {
            response.text().then(function (content) {
              result_elem.insert(
                DOM.div({className: 'error'}, content)
              );
            });
          }
        });

      result_elem.addClassName('ajax-loading');
    }
  }
};
