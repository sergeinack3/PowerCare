{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th class="category" colspan="4">{{tr}}CMediusers-praticien{{/tr}}</th>
</tr>
<tr>
  <th>{{tr}}common-Practitioner{{/tr}} </th>
  <td>{{$praticien->_user_first_name}} {{$praticien->_user_last_name}}</td>
  <th>{{tr}}common-Phone{{/tr}} </th>
  <td>{{$praticien->_user_phone}}</td>
</tr>
<tr>
  <th>{{tr}}Address{{/tr}} </th>
  <td>{{$praticien->_user_adresse}} {{$praticien->_user_cp}} {{$praticien->_user_ville}}</td>
  <th>{{tr}}CMediusers-_p_email{{/tr}} </th>
  <td>{{$praticien->_user_email}}</td>
</tr>
