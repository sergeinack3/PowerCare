{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.setTabCount('avis_maternite', '{{$sejours|@count}}');
  });
</script>

<table class="tbl">
  <tr>
    <th>
      {{tr}}CLit{{/tr}}
    </th>
    <th style="width: 40%;">
      {{tr}}CPatient{{/tr}}
    </th>
    <th style="width: 40%;">
      {{tr}}CSejour{{/tr}}
    </th>
    <th class="narrow"></th>
  </tr>

  {{foreach from=$sejours item=_sejour}}
    <tr>
      <td>
        {{$_sejour->_ref_curr_affectation->_view}}
      </td>
      <td>
        <span onmouseover="ObjectToolTip.createEx(this, '{{$_sejour->_ref_patient->_guid}}');">
          {{tr}}{{$_sejour->_ref_patient->_view}}{{/tr}}
        </span>
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
          {{$_sejour->_shortview}}
        </span>
      </td>
      <td>
        {{if $_sejour->_ref_consult_atu->_id}}
          <a class="button search" href="?m=urgences&tab=edit_consultation&selConsult={{$_sejour->_ref_consult_atu->_id}}">
            {{tr}}CRPU-see_pec{{/tr}}
          </a>
        {{/if}}
        <button type="button" class="door-in" onclick="AvisMaternite.retourUrgences('{{$_sejour->_id}}');">
          {{tr}}CRPU-Retour urgences{{/tr}}
        </button>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="4">
        {{tr}}CSejour.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>