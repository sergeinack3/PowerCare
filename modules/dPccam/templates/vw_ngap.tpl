{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function() {
    var form = getForm('filtersNGAP');
    Calendar.regField(form.elements['date']);
    form.onsubmit();
  });

  searchCodesNGAP = function(form) {
    var url = new Url('ccam', 'ngapCodeSearch');
    url.addFormData(form);
    url.requestUpdate('codes_ngap');
    return false;
  }
</script>

<form name="filtersNGAP" method="get" action="?" onsubmit="return searchCodesNGAP(this);">
  <table class="form">
    <tr>
      <th>
        <label for="date">Date</label>
      </th>
      <td>
        <input type="hidden" name="date" value="{{$date}}">
      </td>
      <th>
        <label for="spec">Spécialité</label>
      </th>
      <td>
        <select name="spec">
          {{foreach from=$specs item=_spec}}
            <option value="{{$_spec->spec_cpam_id}}"{{if $_spec->spec_cpam_id == $spec->spec_cpam_id}} selected{{/if}}>
              {{$_spec}}
            </option>
          {{/foreach}}
        </select>
      </td>
      <th>
        <label for="zone">Zone</label>
      </th>
      <td>
        <select name="zone">
          <option value="metro" selected>Métropole</option>
          <option value="antilles">Antilles</option>
          <option value="guyane-reunion">Guyane/Ile de la Réunion</option>
          <option value="mayotte">Mayotte</option>
        </select>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="6">
        <button type="button" class="search" onclick="this.form.onsubmit();">
          {{tr}}Filter{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>

<div id="codes_ngap" class="me-padding-0"></div>
