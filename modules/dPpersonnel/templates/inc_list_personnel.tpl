{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="3">Liste du personnel ({{$personnels|@count}} résultat(s))</th>
  </tr>

  <tr>
    <th>{{mb_title class=CPersonnel field=user_id}}</th>
    <th>{{mb_title class=CPersonnel field=emplacement}}</th>
    <th>{{tr}}CPersonnel-back-affectations{{/tr}}</th>
  </tr>

  {{foreach from=$personnels item=_personnel}}
  <tr {{if $_personnel->_id == $personnel_id}}class="selected"{{/if}}>
    <td><a href="#1" onclick="Personnel.edit('{{$_personnel->_id}}')">{{$_personnel->_ref_user}}</a></td>
    
    <td>{{tr}}CPersonnel.emplacement.{{$_personnel->emplacement}}{{/tr}}</td>
    {{if $_personnel->actif}}
    <td style="text-align: center;">{{$_personnel->_count.affectations}}</td>
    {{else}}
    <td class="cancelled">INACTIF</td>
    {{/if}}
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="3" class="empty">
      {{tr}}CPersonnel.none{{/tr}}
    </td>
  </tr>
  {{/foreach}}
</table>
