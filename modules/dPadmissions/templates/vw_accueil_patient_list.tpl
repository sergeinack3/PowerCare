{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tbody>
    {{foreach from=$sejours item=_sejour}}
      <tbody id="line_{{$_sejour->_guid}}">
        {{mb_include module=admissions template=inc_accueil_patient_list}}
      </tbody>
    {{foreachelse}}
      <tr>
        <td colspan="9" class="empty">{{tr}}CSejour.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </tbody>
  <thead>
    <tr>
      <th class="section" colspan="3">{{tr}}CPatient{{/tr}}</th>
      <th class="section" colspan="6">{{tr}}CSejour{{/tr}}</th>
    </tr>
    <tr>
      <th>{{mb_colonne class=CSejour field=patient_id order_col=$order_col order_way=$order_way function=sortByAccueil}}</th>
      <th>{{mb_label class=CPatient field=sexe}}</th>
      <th>{{mb_label class=CPatient field=_age}}</th>
      <th>{{mb_colonne class=CSejour field=entree_prevue order_col=$order_col order_way=$order_way function=sortByAccueil}}</th>
      <th>{{mb_colonne class=CSejour field=praticien_id order_col=$order_col order_way=$order_way function=sortByAccueil}}</th>
      <th>{{tr}}CAffectation{{/tr}}</th>
      <th class="narrow">
        {{mb_colonne class=CSejour field=entree_reelle order_col=$order_col order_way=$order_way function=sortByAccueil}}
      </th>
      <th class="narrow">
        {{mb_colonne class=CSejour field=pec_accueil order_col=$order_col order_way=$order_way function=sortByAccueil}}
      </th>
      <th class="narrow">
        {{mb_colonne class=CSejour field=pec_service order_col=$order_col order_way=$order_way function=sortByAccueil}}
      </th>
    </tr>
  </thead>
</table>