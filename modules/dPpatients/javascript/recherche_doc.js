/**
 * @package Mediboard\Patient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

RechercheDoc = {
  form:   null,
  cr_ids: [],

  listDocs: function (page) {
    var url = new Url('patients', 'ajax_recherche_docs');
    url.addFormData(this.form);
    if (!Object.isUndefined(page)) {
      url.addParam('page', page)
    }
    url.requestUpdate('docs_area');
  },

  filterByContext: function (context) {
    var filtres_context = $$('.filtres_context');

    filtres_context.invoke('hide');

    var fieldset_filter = $('filtre_' + context);

    if (!fieldset_filter) {
      return;
    }

    fieldset_filter.show();
  },

  openDocs: function () {
    $$('input[type=checkbox]:checked.doc').each(
      function (input) {
        Document.edit(input.value, true);
      }
    );
  },

  printDocs: function () {
    var cr_ids = $$('input[type=checkbox]:checked.doc').pluck('value');

    if (!cr_ids.length) {
      return;
    }

    var url = new Url('compteRendu', 'print_docs', 'raw');

    cr_ids.each(
      function (cr_id) {
        url.addParam('nbDoc[' + cr_id + ']', 1);
      }
    );

    url.open();
  },

  deleteDocs: function () {
    var cr_ids = $$('input[type=checkbox]:checked.doc').pluck('value');

    if (!cr_ids.length) {
      return;
    }

    if (!confirm($T('CCompteRendu-Confirm delete selected docs'))) {
      return;
    }

    var form = getForm('delDocs');
    $V(form.compte_rendu_ids, cr_ids.join('-'));

    onSubmitFormAjax(form, listDocs);
  },

  sendDocs: function () {
    this.cr_ids = $$('input[type=checkbox]:checked.doc').pluck('value');

    this.sendDoc();
  },

  sendDoc: function () {
    var cr_id = this.cr_ids.shift();

    if (!cr_id) {
      return;
    }

    new Url('compteRendu', 'ajax_view_mail')
      .addParam('object_guid', "CCompteRendu-" + cr_id)
      .requestModal(700, 320, {onClose: this.sendDoc.bind(this)});

  },
  //Requete pour telecharger les documents si aucun selectionne retourne une alert
  downloadDocs: function() {

    let cr_ids = $$('input[type=checkbox]:checked.doc').pluck('value');

    if (!cr_ids.length) {
      alert($T('no_document_selected'));
      return;
    }

    let url = new Url('compteRendu', 'downloadZipFile', 'raw');

    cr_ids.each(
      function (cr_id) {
        url.addParam('nbDoc[' + cr_id + ']', 1);
      }
    );
  url.pop(500, 300, "Téléchargement document(s)");
  }
};
