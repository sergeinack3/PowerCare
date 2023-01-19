{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th>{{mb_title class=CMedecin field=nom}}</th>
    <th>{{mb_title class=CMedecin field=prenom}}</th>
    <th>{{mb_title class=CMedecin field=type}}</th>
    <th>{{mb_title class=CMedecin field=tel}}</th>
    <th>{{mb_title class=CMedecin field=fax}}</th>
    <th>{{mb_title class=CMedecin field=email}}</th>
    <th>{{mb_title class=CMedecin field=adresse}}</th>
    <th>{{mb_title class=CMedecin field=cp}}</th>
    <th>{{mb_title class=CMedecin field=adeli}}</th>
    <th>{{mb_title class=CMedecin field=rpps}}</th>
    <th>Total</th>
  </tr>
  
  {{foreach from=$counts item=_count key=_medecin_id}}
    {{assign var=_medecin value=$medecins.$_medecin_id}}
    <tr>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_medecin->_guid}}')">{{mb_value object=$_medecin field=nom}}</span>
      </td>
      <td>{{mb_value object=$_medecin field=prenom}}</td>
      <td>{{mb_value object=$_medecin field=type}}</td>
      <td>{{mb_value object=$_medecin field=tel}}</td>
      <td>{{mb_value object=$_medecin field=fax}}</td>
      <td>{{mb_value object=$_medecin field=email}}</td>
      <td class="compact">{{mb_value object=$_medecin field=adresse}}</td>
      <td>{{mb_value object=$_medecin field=cp}}</td>
      <td>{{mb_value object=$_medecin field=adeli}}</td>
      <td>{{mb_value object=$_medecin field=rpps}}</td>
      <td style="font-size: 1.2em; font-weight: bold;">{{$_count}}</td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="11">{{tr}}No result{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>