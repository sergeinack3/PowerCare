{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="8">{{tr var1=$date_min|date_format:$conf.date var2=$date_max|date_format:$conf.date}}CSejour-List sejours{{/tr}}</th>
  </tr>
  <tr>
    <th colspan="2">
      {{mb_title class=CPatient field=nom}}
      <br />
      ({{mb_title class=CPatient field=nom_jeune_fille}})
    </th>
    <th>
      {{tr}}CPrescription{{/tr}}
    </th>
    <th class="narrow">
      {{mb_title class=CSejour field=entree}}
    </th>
    <th class="narrow">
      {{mb_title class=CSejour field=sortie}}
    </th>
    <th>
      {{mb_title class=CSejour field=libelle}}
    </th>
    <th class="narrow">
      {{mb_title class=CSejour field=praticien_id}}
    </th>
    <th class="narrow">
      {{tr}}CSejour-back-consultations{{/tr}}
    </th>
  </tr>

  {{foreach from=$sejours item=_sejour}}
    <tbody id="line_sejour_{{$_sejour->_id}}">
      {{mb_include module=soins template=inc_line_sejour_reeducation}}
    </tbody>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="8">
        {{tr}}CSejour.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>