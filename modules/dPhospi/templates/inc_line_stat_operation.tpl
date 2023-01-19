{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <td class="text">
    {{mb_include module=system template=inc_vw_mbobject object=$_operation->_ref_patient}}
  </td>
  <td class="text">{{mb_value object=$_operation field=_datetime}}</td>
  <td>{{$_operation->duree_uscpo}} nuit(s)</td>
  <td class="text">{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_chir}}</td>
  <td class="text">
    {{if $_operation->libelle}}
      {{$_operation->libelle}}
    {{else}}
      {{" ; "|implode:$_operation->_codes_ccam}}
    {{/if}}
  <td class="text">
    <a class="button edit notext" href="?m=planningOp&tab=vw_edit_planning&operation_id={{$_operation->_id}}"></a>
  </td>
</tr>