{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Psycho-mere-{{$dossier->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier}}
  {{mb_key   object=$dossier}}
  <input type="hidden" name="_count_changes" value="0" />
  <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
  <table class="form me-no-box-shadow me-no-align me-small-form">
    <tr>
      <th class="title" colspan="4">Sur le plan psychologique (mère)</th>
    </tr>
    <tr>
      <th colspan="2">{{mb_label object=$dossier field=enfants_foyer}}</th>
      <td colspan="2">{{mb_field object=$dossier field=enfants_foyer}}</td>
    </tr>
    <tr>
      <th class="narrow">{{mb_field object=$dossier field=situation_part_enfance typeEnum=checkbox}}</th>
      <td class="halfPane"><strong>{{mb_label object=$dossier field=situation_part_enfance}}</strong></td>
      <th class="narrow">{{mb_field object=$dossier field=situation_part_familiale typeEnum=checkbox}}</th>
      <td class="halfPane"><strong>{{mb_label object=$dossier field=situation_part_familiale}}</strong></td>
    </tr>
    <tr>
      <th>{{mb_field object=$dossier field=spe_perte_parent typeEnum=checkbox}}</th>
      <td><span class="compact">{{mb_label object=$dossier field=spe_perte_parent}}</span></td>
      <th>{{mb_field object=$dossier field=spf_violences_conjugales typeEnum=checkbox}}</th>
      <td><span class="compact">{{mb_label object=$dossier field=spf_violences_conjugales}}</span></td>
    </tr>
    <tr>
      <th>{{mb_field object=$dossier field=spe_maltraitance typeEnum=checkbox}}</th>
      <td><span class="compact">{{mb_label object=$dossier field=spe_maltraitance}}</span></td>
      <th>{{mb_field object=$dossier field=spf_mere_isolee typeEnum=checkbox}}</th>
      <td><span class="compact">{{mb_label object=$dossier field=spf_mere_isolee}}</span></td>
    </tr>
    <tr>
      <th>{{mb_field object=$dossier field=spe_mere_placee_enfance typeEnum=checkbox}}</th>
      <td><span class="compact">{{mb_label object=$dossier field=spe_mere_placee_enfance}}</span></td>
      <th>{{mb_field object=$dossier field=spf_absence_entourage_fam typeEnum=checkbox}}</th>
      <td><span class="compact">{{mb_label object=$dossier field=spf_absence_entourage_fam}}</span></td>
    </tr>
    <tr>
      <th>{{mb_field object=$dossier field=situation_part_adolescence typeEnum=checkbox}}</th>
      <td><strong>{{mb_label object=$dossier field=situation_part_adolescence}}</strong></td>
      <th>{{mb_field object=$dossier field=stress_agression typeEnum=checkbox}}</th>
      <td><strong>{{mb_label object=$dossier field=stress_agression}}</strong></td>
    </tr>
    <tr>
      <th>{{mb_field object=$dossier field=spa_anorexie_boulimie typeEnum=checkbox}}</th>
      <td><span class="compact">{{mb_label object=$dossier field=spa_anorexie_boulimie}}</span></td>
      <th>{{mb_field object=$dossier field=sa_agression_physique typeEnum=checkbox}}</th>
      <td><span class="compact">{{mb_label object=$dossier field=sa_agression_physique}}</span></td>
    </tr>
    <tr>
      <th>{{mb_field object=$dossier field=spa_depression typeEnum=checkbox}}</th>
      <td><span class="compact">{{mb_label object=$dossier field=spa_depression}}</span></td>
      <th>{{mb_field object=$dossier field=sa_agression_sexuelle typeEnum=checkbox}}</th>
      <td><span class="compact">{{mb_label object=$dossier field=sa_agression_sexuelle}}</span></td>
    </tr>
    <tr>
      <td colspan="2"></td>
      <th>{{mb_field object=$dossier field=sa_harcelement_travail typeEnum=checkbox}}</th>
      <td><span class="compact">{{mb_label object=$dossier field=sa_harcelement_travail}}</span></td>
    </tr>
    <tr>
      <th colspan="2">{{mb_label object=$dossier field=rques_psychologie}}</th>
      <td colspan="2">
        {{if !$print}}
          {{mb_field object=$dossier field=rques_psychologie form=Psycho-mere-`$dossier->_guid`}}
        {{else}}
          {{mb_value object=$dossier field=rques_psychologie}}
        {{/if}}
      </td>
    </tr>
  </table>
</form>
