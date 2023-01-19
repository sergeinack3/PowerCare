/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ModeleEtiquette = {
  nb_printers: 0,

  print: function (object_class, object_id, modele_etiquette_id) {
    if (ModeleEtiquette.nb_printers > 0) {
      var url = new Url("compteRendu", "ajax_choose_printer");

      if (modele_etiquette_id) {
        Control.Modal.close();
        url.addParam("modele_etiquette_id", modele_etiquette_id);
      }

      url.addParam("mode_etiquette", 1);
      url.addParam("object_class", object_class);
      url.addParam("object_id", object_id);
      url.requestModal(400);
    }
    else {
      new Url('hospi', 'print_etiquettes', "raw")
        .addParam('object_class', object_class)
        .addParam('object_id', object_id)
        .addNotNullParam('modele_etiquette_id', modele_etiquette_id)
        .open();

      if (modele_etiquette_id) {
        Control.Modal.close();
      }
    }
  },

  chooseModele: function (object_class, object_id, afterClose) {
    new Url("hospi", "ajax_choose_modele_etiquette")
      .addParam("object_class", object_class)
      .addParam("object_id", object_id)
      .requestModal(400, {onClose: Object.isFunction(afterClose) ? afterClose : Prototype.emptyFunction});
  },

  refreshList: function () {
    var form = getForm("Filter");
    var url = new Url("hospi", "ajax_list_modele_etiquette");
    url.addNotNullElement(form.filter_class);
    url.requestUpdate("list_etiq");
    return false;
  },

  onSubmit: function (form) {
    return onSubmitFormAjax(form, ModeleEtiquette.refreshList);
  },

  onSubmitComplete: function (guid) {
    Control.Modal.close();
    var id = guid.split("-")[1];
    ModeleEtiquette.edit(id);
  },

  edit: function (modele_etiquette_id) {
    Form.onSubmitComplete = modele_etiquette_id == "" ?
      ModeleEtiquette.onSubmitComplete :
      Prototype.emptyFunction;

    var selected = $("modele_etiq-" + modele_etiquette_id);
    if (selected) {
      selected.addUniqueClassName("selected");
    }

    var url = new Url("hospi", "ajax_edit_modele_etiquette");
    url.addParam("modele_etiquette_id", modele_etiquette_id);
    url.requestModal(800, {onClose: ModeleEtiquette.refreshList});
  },

  confirmDeletion: function (form) {
    var options = {
      typeName: "Le modèle ",
      objName:  $V(form.nom)
    };

    confirmDeletion(form, options, Control.Modal.close);
  },

  preview: function () {
    var form_edit = getForm("edit_etiq");
    var form_download = getForm("download_prev");
    $V(form_download.largeur_page, $V(form_edit.largeur_page));
    $V(form_download.hauteur_page, $V(form_edit.hauteur_page));
    $V(form_download.nb_lignes, $V(form_edit.nb_lignes));
    $V(form_download.nb_colonnes, $V(form_edit.nb_colonnes));
    $V(form_download.marge_horiz, $V(form_edit.marge_horiz));
    $V(form_download.marge_vert, $V(form_edit.marge_vert));
    $V(form_download.marge_horiz_etiq, $V(form_edit.marge_horiz_etiq));
    $V(form_download.marge_vert_etiq, $V(form_edit.marge_vert_etiq));
    $V(form_download.hauteur_ligne, $V(form_edit.hauteur_ligne));
    $V(form_download.nom, $V(form_edit.nom));
    $V(form_download.texte, $V(form_edit.texte));
    $V(form_download.texte_2, $V(form_edit.texte_2));
    $V(form_download.texte_3, $V(form_edit.texte_3));
    $V(form_download.texte_4, $V(form_edit.texte_4));
    $V(form_download.font, $V(form_edit.font));
    $V(form_download.show_border, $V(form_edit.show_border));
    $V(form_download.text_align, $V(form_edit.text_align));
    form_download.submit();
  },

  insertField: function (elem) {
    var texte_etiq = window.text_focused;
    if (!texte_etiq) {
      texte_etiq = $("edit_etiq_texte");
    }
    var caret = texte_etiq.caret();
    var form = elem.form;
    var bold = $V(form._write_bold);
    var upper = $V(form._write_upper);
    var size = $V(form._field_size);

    var content = elem.value;
    var mark_left = "[";
    var mark_right = "]";

    if (bold == "1") {
      if (upper == "1") {
        mark_left = mark_right = "#";
      }
      else {
        mark_left = mark_right = "*";
      }
    }
    else if (upper == "1") {
      mark_left = mark_right = "+";
    }

    content = mark_left + (size ? size : "") + content + mark_right;

    texte_etiq.caret(caret.begin, caret.end, content + " ");
    texte_etiq.caret(texte_etiq.value.length);
    texte_etiq.fire("ui:change");
    $V(getForm("edit_etiq").fields, "");
  },

  removeSelected: function () {
    var list_etiq = $("list_etiq").select(".selected")[0];
    if (list_etiq) {
      list_etiq.removeClassName("selected");
    }
  }
};
