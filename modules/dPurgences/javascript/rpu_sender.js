/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

RPU_Sender = {
  extract_passages_id: null,

  popupImport: function(module) {
    new Url("dPurgences", "ajax_import_key")
      .addParam("module", module)
    .pop(500, 400, "Import de la clé publique");

    return false;
  },

  updateKey: function(fingerprint) {
    var message = fingerprint ? '<div class="info">Clé publique ajoutée au trousseau : '+fingerprint+'<\/div>' :
      '<div class="error">Impossible d\'importer la clé publique.<\/div>';

    $("import_key").update(message);
  },

  showEncryptKey: function() {
    var url = new Url("dPurgences", "ajax_show_encrypt_key");
    url.requestUpdate('show_encrypt_key');
  },

  extract: function(form, type) {
    if (!checkForm(form)) {
      return;
    }

    new Url("dPurgences", "ajax_extract_passages_"+type)
      .addParam("debut_selection", $V(form.debut_selection))
      .addParam("fin_selection"  , $V(form.fin_selection))
      .requestUpdate('td_extract_'+type, {
        onComplete: function(){
          if (!$('td_extract_'+type).select('.error, .warning').length) {
            $('encrypt_'+type).disabled = false;
          }
      }});
  },

   encrypt: function(type) {
    new Url("dPurgences", "ajax_encrypt_passages")
      .addParam("extract_passages_id", RPU_Sender.extract_passages_id)
      .requestUpdate('td_encrypt_'+type, {
        onComplete: function(){
        if (!$('td_encrypt_'+type).select('.error, .warning').length) {
          $('transmit_'+type).disabled = false;
        }
      }});
  },

   transmit: function(type) {
    new Url("dPurgences", "ajax_transmit_passages")
      .addParam("extract_passages_id", RPU_Sender.extract_passages_id)
      .requestUpdate('td_transmit_'+type);
  }
};