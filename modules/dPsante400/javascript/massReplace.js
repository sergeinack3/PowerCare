/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

MassReplace = {

  /**
   * Compte le nombre d'étiquettes correspondant aux critères de recherche
   */
  count: function () {
    const loader = document.getElementById("loader");
    loader.style.display = "block";
    const error_tag = document.getElementById("error_tag");
    error_tag.style.display = "none";

    const object_class = document.getElementById("object_class").value;
    const tag = document.getElementById("tag").value;
    const values = document.getElementById("values").value;
    const url = new Url('dPsante400', 'countTags');
    url.addParam("object_class", object_class);
    url.addParam("tag", tag);
    url.addParam("values", values);
    url.requestJSON(function (data) {
      loader.style.display = "none";
      if (data.error_tag) {
        error_tag.style.display = "block";
      } else {
        const compteur = document.getElementById("count");
        compteur.innerText = 'Nombre d\'étiquettes correspondant aux critères de recherche : ' + data.nb_tags;
      }
    });
  },

  /**
   * Modification des étiquettes correspondant aux critères de recherche
   */
  edit: function () {
    const loader = document.getElementById("loader");
    loader.style.display = "block";
    const error_tag = document.getElementById("error_tag");
    error_tag.style.display = "none";

    const object_class = document.getElementById("object_class").value;
    const tag = document.getElementById("tag").value;
    const values = document.getElementById("values").value;
    const new_tag = document.getElementById("new_tag").value;
    const url = new Url('dPsante400', 'editTags');
    url.addParam("object_class", object_class);
    url.addParam("tag", tag);
    url.addParam("values", values);
    url.addParam("new_tag", new_tag);
    url.requestJSON(function (data) {
      loader.style.display = "none";
      if (data.error_tag) {
        error_tag.style.display = "block";
      } else {
        const success = document.getElementById("success");
        success.innerText = 'Nombre d\'étiquettes modifiée(s) avec succès : ' + data.nb_success + ' / ' + data.nb_tags;
        const error = document.getElementById("error");
        error.innerText = 'Nombre de tentatives de modification échouée(s) : ' + data.nb_error + ' / ' + data.nb_tags;
      }
    });
  },

  /**
   * Gestion de la visibilité du bouton d'édition
   */
  manageEditButtonVisibility: function () {
    const new_tag_field = document.getElementById("new_tag");
    const editButton = document.getElementById("edit_button");
    editButton.disabled = new_tag_field.value === '';
  },
};
