/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Rapport = {
  refresh: function(date, object_guid, plage) {
    if ($(date)) {
      var url = new Url('facturation', 'print_rapport');
      url.addParam('date', date);
      url.requestUpdate(date);
    }
    else if(object_guid && plage) {
      $('line_facture_'+object_guid).addClassName('opacity-40');
      $(plage+'_total').addClassName('opacity-40');
    }
    Rapport.showObsolete();
  },

  showObsolete: function() {
    var style = {
      opacity: 0.9
    };
    $('obsolete-totals').setStyle(style).clonePosition('totals').show();
    $('totals').hide();
  },

  editReglement: function(reglement_id, date, object_guid, plage, on_close = null) {
    var url = new Url('cabinet', 'edit_reglement');
    url.addParam('reglement_id', reglement_id);
    url.requestModal(500, null, { onClose: on_close });

    if (!on_close) {
      url.modalObject.observe('afterClose', Rapport.refresh.curry(date, object_guid, plage));
    }
  },

  addReglement: function(object_guid, emetteur, montant, mode, date, plage, on_close = null) {
    var url = new Url('cabinet', 'edit_reglement');
    url.addParam('object_guid', object_guid);
    url.addParam('emetteur', emetteur);
    url.addParam('montant', montant && montant > 0.01 ? montant : '');
    url.addParam('mode', mode);
    url.requestModal(500, null, { onClose: on_close });

    if (!on_close) {
      url.modalObject.observe('afterClose', Rapport.refresh.curry(date, object_guid, plage));
    }
  },

  updateReglementsEtab: function(object_guid) {
    var url = new Url('facturation', 'ajax_reglements_fact_etab');
    url.addParam('object_guid', object_guid);
    url.requestUpdate(object_guid);
  },

  editReglementEtab: function(reglement_id, object_guid) {
    var url = new Url('cabinet', 'edit_reglement');
    url.addParam('reglement_id', reglement_id);
    url.addParam('force_regle_acte', 1);
    url.requestModal(400);
    url.modalObject.observe('afterClose', function(){
      Rapport.updateReglementsEtab(object_guid);
    });
  },

  addReglementEtab: function(object_guid, montant, mode) {
    var url = new Url('cabinet', 'edit_reglement');
    url.addParam('object_guid' , object_guid);
    url.addParam('emetteur'    , 'tiers');
    url.addParam('montant'     , montant);
    url.addParam('mode'        , mode);
    url.addParam('force_regle_acte', 1);
    url.requestModal(400);
    url.modalObject.observe('afterClose', function(){
      Rapport.updateReglementsEtab(object_guid);
    });
  }
}
