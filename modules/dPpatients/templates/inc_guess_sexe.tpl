{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="2">{{$objects|@count}} / {{$total}} résultats</th>
  </tr>

  <tr>
    <th style="width: 80%;">Prénom</th>
    <th>Sexe</th>
  </tr>
  {{foreach from=$objects item=_object}}
    <tr>
      <td>
        {{$_object->$field_prenom}}
      </td>
      <td>
        {{$_object->getFormattedValue($field_sexe)}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty">{{tr}}{{$object_class}}.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>