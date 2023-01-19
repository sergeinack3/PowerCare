{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=CMbObject_view}}

{{if $object->_ref_new_facture && $object->_ref_new_facture->_id}}
  <table class="tbl">
    <tr>
      <td>
        <strong>
          <label title="{{tr}}CFacture-_ref_new_facture-desc{{/tr}}">{{tr}}CFacture-_ref_new_facture{{/tr}}</label>
        </strong>:
        <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_ref_new_facture->_guid}}');">
          {{$object->_ref_new_facture->_view}}
        </span>
      </td>
    </tr>
  </table>
{{/if}}