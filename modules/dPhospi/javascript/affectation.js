/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

if (!window.Affectation) {
  Affectation = {
    from_tempo:     false,
    delAffectation: function (form, lit_id, sejour_guid) {
      return onSubmitFormAjax(form, function () {
        if (window.refreshMouvements) {
          refreshMouvements(loadNonPlaces, lit_id);
        }
        if (sejour_guid) {
            if ($("view_affectations")){
                // Normal path
                $("view_affectations").select("." + sejour_guid).each(function (div) {
                    var div_lit_id = div.get("lit_id");
                    if (div_lit_id != lit_id) {
                        refreshMouvements(loadNonPlaces, div_lit_id);
                    }
                });
            } else {
                if (window.Rafraichissement) {
                    // If module "Urgences"
                    Rafraichissement.init();
                }
            }
        }
      });
    },
    edit:           function (affectation_id, lit_id, urgence, from_tempo, callback) {
      var url = new Url("hospi", "ajax_edit_affectation");
      url.addParam("affectation_id", affectation_id);

      if (!Object.isUndefined(lit_id)) {
        url.addParam("lit_id", lit_id);
      }
      if (!Object.isUndefined(urgence)) {
        url.addParam("urgence", urgence);
      }

      url.addParam("from_tempo", Affectation.from_tempo ? "1" : "0");

      if (window.Placement && window.Placement.stop) {
        Placement.stop();
      }
      url.requestModal("70%", "95%", {
        showReload: false,
        onClose:    function () {
          if (window.Placement) {
            Placement.resume();
          } else if (window.Rafraichissement) {
              // If module "Urgences"
              Rafraichissement.init();
          }

          if (callback) {
            callback();
          }
        }
      });
    }
  }
}
