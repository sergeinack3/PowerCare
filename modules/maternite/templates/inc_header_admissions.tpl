{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=manage_provisoire value="maternite CGrossesse manage_provisoire"|gconf}}

<tr>
  <th colspan="7" class="">Parturiente</th>
  <th colspan="4" class="">Enfants</th>
</tr>

<tr>
  <th class="narrow">Admettre</th>
  <th class="">{{tr}}CPatient{{/tr}}</th>
  <th class="narrow me-small-fields">
    <input type="text" size="3" onkeyup="Admissions.filter(this, 'admissions')" id="filter-patient-name" />
  </th>
  <th class="">{{mb_label class=CSejour field=entree}}</th>
  <th class="">Acc.</th>
  <th class=" narrow">Terme</th>
  <th class="">Praticiens</th>

  <th class="">Rangs / Heures</th>
  <th class="">Enfants</th>
  <th class="">Séjours</th>
  {{if $manage_provisoire}}
    <th class=""></th>
  {{/if}}
</tr>