{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $commentaires|@count}}
  <table class="tbl">
    <tr>
      <th colspan="2" class="title" style="width:300px;white-space: normal;">
        Derniers commentaires pour {{tr}}CConstantesMedicales-{{$constant}}{{/tr}} ({{$commentaires|@count}})
      </th>
    </tr>
    <tr>
      <th>{{mb_title class=CConstantesMedicales field=datetime}}</th>
      <th>{{mb_title class=CConstantComment field=comment}}</th>
    </tr>
    {{foreach from=$commentaires item=_commentaire}}
      <tr>
        <td>{{mb_value object=$_commentaire->_ref_constant field=datetime}}</td>
        <td>{{mb_value object=$_commentaire field=comment}}</td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}