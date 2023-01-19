/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PreselectorUfs = {
  form: null,

  listingPreselectionUfs: function() {
    var url = new Url('hospi', 'ajax_vw_association_uf_form');
    url.addParam('uf_soins_id'      , $V(this.form.uf_soins_id));
    url.addParam('uf_medicale_id'   , $V(this.form.uf_medicale_id));
    url.addParam('uf_hebergement_id', $V(this.form.uf_hebergement_id));
    url.addParam('service_id'       , $V(this.form.service_id));
    switch (this.form.name) {
      case 'editSejour':
        url.addParam('object_guid', 'CSejour-'+$V(this.form.sejour_id));
        url.addParam('prat_id', $V(this.form.praticien_id));
        url.addParam('entree', $V(this.form._date_entree_prevue));
        url.addParam('sortie', $V(this.form._date_sortie_prevue));
        url.addParam('type'  , $V(this.form.type));
        break;
      default:
        url.addParam('object_guid', 'CProtocole-'+$V(this.form.protocole_id));
        url.addParam('prat_id'    , $V(this.form.chir_id));
        url.addParam('function_id', $V(this.form.function_id));
    }

    url.modal();
  },

  applyUfs: function(form) {
    $V(this.form.uf_hebergement_id, $V(form.uf_hebergement_id));
    $V(this.form.uf_medicale_id   , $V(form.uf_medicale_id));
    $V(this.form.uf_soins_id      , $V(form.uf_soins_id));
  }
};