{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  addLineSejour = function(value) {
    var url = new Url('pmsi', 'ajax_add_sejour_reception_multiple');
    url.addParam('nda', value);
    url.requestUpdate('sejour_new_line', function() {
      var form = getForm('addSejour');
      $V(form.NDA, '');
      form.NDA.focus();
    });

    return false;
  }

  deleteLineSejour = function(guid) {
    $(guid).remove();

    if ($$('tr.sejour').length == 0) {
      $('sejour_empty').show();
    }
  }

  receiptSejours = function() {
    var sejour_guids = [];
    var lines = $$('tr.sejour');
    lines.each(function(line) {
      sejour_guids.push(line.id);
    });

    var url = new Url('pmsi', 'do_receipt_sejours', 'dosql');
    url.addParam('sejour_guids', sejour_guids.join('|'));
    url.requestUpdate('systemMsg', {method: 'post', onComplete: function() {
      sejour_guids.each(function(guid) {
        deleteLineSejour(guid);
      });
    }})
  }
</script>

<form name="addSejour" action="" method="get" onsubmit="return addLineSejour($V(this.NDA));">
  <table class="form">
    <tr>
      <td>
        {{me_form_field mb_class="CSejour" mb_field="_NDA"}}
          <input id="input_nda" type="text" name="NDA" class="barcode" value="" />
          <button type="button" onclick="addLineSejour($V(this.form.NDA));" class="search notext" title="Rechercher le dossier"></button>
        {{/me_form_field}}
      </td>
    </tr>
    <tr>
      <td class="button" colspan="4">
        <button type="button" class="tick me-primary" onclick="receiptSejours();">{{tr}}mod-dPpmsi-tab-vw_recept_dossiers{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<table class="tbl" id="list-sejours">
  <tr>
    <th class="title" colspan="7">
      {{tr}}CSejour|pl{{/tr}}
    </th>
  </tr>
  <tr>
    <th>
      {{mb_title class=CSejour field=_NDA}}
    </th>
    <th>
      {{mb_title class=CSejour field=entree_reelle}}
    </th>
    <th>
      {{mb_title class=CSejour field=sortie_reelle}}
    </th>
    <th>
      {{mb_title class=CSejour field=patient_id}}
    </th>
    <th>
      {{mb_title class=CSejour field=praticien_id}}
    </th>
    <th>
      {{tr}}CSejour{{/tr}}
    </th>
    <th class="narrow"></th>
  </tr>
  <tr id="sejour_empty">
    <td colspan="6" class="empty" style="text-align: center;">
      {{tr}}CSejour.none{{/tr}}
    </td>
  </tr>
  <tr id="sejour_new_line" style="display: none;"></tr>
</table>