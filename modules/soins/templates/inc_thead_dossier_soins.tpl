{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=with_thead value=0}}
{{mb_default var=colspan value=2}}

{{if !$with_thead}}
  {{mb_return}}
{{/if}}

<thead>
  <tr>
    <th class="title" colspan="{{$colspan}}">
      {{$sejour->_view}}
      {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
      <br/>
      {{$sejour->_ref_curr_affectation->_ref_lit}}
    </th>
  </tr>
</thead>
