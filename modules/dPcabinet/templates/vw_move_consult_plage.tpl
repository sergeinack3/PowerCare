{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="category" colspan="5">
      {{$nb_consult}} consultation(s) {{if $resolve}}corrigée(s){{else}}à corriger{{/if}}
    </th>
  </tr>
  {{if $resolve}}
    <tr>
      <td colspan="5">{{$limit}} consultations corrigées</td>
    </tr>
  {{else}}
    {{foreach from=$consultations item=_consult}}
      <tr>
        <td>{{$_consult->_ref_patient->_view}}</td>
        <td>{{mb_value object=$_consult field=heure}}</td>
        <td>{{mb_value object=$_consult->_ref_plageconsult field=date}}</td>
        <td>{{mb_value object=$_consult->_ref_plageconsult field=debut}}</td>
        <td>{{mb_value object=$_consult->_ref_plageconsult field=fin}}</td>
      </tr>
    {{/foreach}}
  {{/if}}
  <tr>
    <td colspan="5">...</td>
  </tr>
  <tr>
    <td class="button" colspan="5">
      <button type="button" class="erase" onclick="Control.Modal.close();correctionPlagesConsult(1);">Corriger</button>
      <button type="button" class="cancel" onclick="Control.Modal.close();">Fermer</button>
    </td>
  </tr>
</table>