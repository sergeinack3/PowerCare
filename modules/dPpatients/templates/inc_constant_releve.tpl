{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr {{if !$_constant->active}}class="opacity opacity-50" {{/if}}>
  <th colspan="2"><strong>{{$_constant->getViewName()}}</strong></th>
  <td>
    {{mb_value object=$_constant field=_view_value}}
    {{$_constant->getViewUnit()}}
    {{mb_include module=dPpatients template=inc_view_alert}}
  </td>
</tr>
