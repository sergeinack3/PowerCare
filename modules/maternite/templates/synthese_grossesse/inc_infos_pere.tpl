{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th class="category" colspan="4">{{tr}}CGrossesse-pere_id informations{{/tr}}</th>
</tr>
<tr>
  <th>{{tr}}CGrossesse-pere_id{{/tr}}</th>
  <td>{{$pere->nom}} {{$pere->prenom}}</td>
  <th>{{mb_label object=$pere field=tel2}}</th>
  <td>{{mb_value object=$pere field=tel2}}</td>
</tr>
<tr>
  <th>{{mb_label object=$pere field=adresse}}</th>
  <td>
      {{mb_value object=$pere field=adresse}},
      {{mb_value object=$pere field=cp}} {{mb_value object=$patient field=ville}}
  </td>
  <th>{{mb_label object=$pere field=email}}</th>
  <td>{{mb_value object=$pere field=email}}</td>
</tr>
