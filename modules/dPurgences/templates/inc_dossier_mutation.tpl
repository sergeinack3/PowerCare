{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<td class="warning" colspan="{{$colspan}}">
  <strong>Mutation à {{$rpu->_ref_sejour->sortie|date_format:$conf.time}}</strong> <br/>
  <span onmouseover="ObjectTooltip.createEx(this, 'CSejour-{{$rpu->mutation_sejour_id}}')">
    {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$rpu->_ref_sejour_mutation}}
  </span> 
</td>
