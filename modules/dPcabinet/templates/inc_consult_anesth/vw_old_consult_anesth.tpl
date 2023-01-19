{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=use_old_anesth_conclu value="dPcabinet CConsultation use_old_anesth_conclu"|gconf}}
<table class="main tbl" id="old_consult">
  <tr>
    <th class="title" colspan="10">Liste des consultations d'anesthésie pour {{$patient->_view}}</th>
  </tr>
  <tr>
    <th class="category"></th>
    <th class="category"></th>
    <th class="category">{{mb_label class=CConsultAnesth field="mallampati"}}</th>
    <th class="category">{{mb_label class=CConsultAnesth field="bouche"}}</th>
    <th class="category">{{mb_label class=CConsultAnesth field="distThyro"}}</th>
    <th class="category">{{mb_label class=CConsultAnesth field="mob_cervicale"}}</th>
    {{if $use_old_anesth_conclu}}
      <th class="category">{{mb_label class=CConsultAnesth field="etatBucco"}}</th>
      <th class="category">{{mb_label class=CConsultAnesth field="conclusion"}}</th>
    {{/if}}
    {{if $moebius_active && $app->user_prefs.ViewConsultMoebius}}
      <th class="category">{{mb_label class=CRisqueIntubation field=risque_ventil}}</th>
      <th class="category">{{mb_label class=CConsultAnesth field=risque_intub}}</th>
      <th class="category">{{mb_label class=CRisqueIntubation field=risque_dentaire}}</th>
    {{/if}}
    <th></th>
  </tr>
  {{foreach from=$dossiers_anesth item=_dossier_anesth}}
    <tr>
      {{assign var=consultation value=$_dossier_anesth->_ref_consultation}}
      {{if $moebius_active}}
        {{assign var=risque_intubation value=$_dossier_anesth->_risques_anesth.risque_intubation}}
      {{/if}}
      <td>{{mb_value object=$consultation field="_date"}}</td>
      <td>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$consultation->_ref_praticien}}
      </td>
      <td>{{mb_value object=$_dossier_anesth field="mallampati"}}</td>
      <td>{{mb_value object=$_dossier_anesth field="bouche"}}</td>
      <td>{{mb_value object=$_dossier_anesth field="distThyro"}}</td>
      <td>{{mb_value object=$_dossier_anesth field="mob_cervicale"}}</td>
      {{if $use_old_anesth_conclu}}
        <td>{{mb_value object=$_dossier_anesth field="etatBucco"}}</td>
        <td>{{mb_value object=$_dossier_anesth field="conclusion"}}</td>
      {{/if}}
      {{if $moebius_active && $app->user_prefs.ViewConsultMoebius}}
        <td>{{mb_value object=$risque_intubation field=risque_ventil}}</td>
        <td>{{mb_value object=$_dossier_anesth field=risque_intub}}</td>
        <td>{{mb_value object=$risque_intubation field=risque_dentaire}}</td>
      {{/if}}
      <td class="button">
        <button class="tick" type="submit"
          {{if !$_dossier_anesth->mallampati && !$_dossier_anesth->bouche && !$_dossier_anesth->distThyro && !$_dossier_anesth->tourCou && !$_dossier_anesth->mob_cervicale
                && (!$use_old_anesth_conclu || (!$_dossier_anesth->etatBucco && !$_dossier_anesth->conclusion))}}
            disabled
          {{/if}}
          onclick="assignDataOldConsultAnesth('{{$_dossier_anesth->mallampati}}', '{{$_dossier_anesth->bouche}}', '{{$_dossier_anesth->distThyro}}', '{{$_dossier_anesth->tourCou}}',
            '{{$_dossier_anesth->mob_cervicale}}', '{{$_dossier_anesth->etatBucco}}', '{{$_dossier_anesth->conclusion|smarty:nodefaults|JSAttribute}}', '{{$use_old_anesth_conclu}}'
            {{if $moebius_active}},'{{$risque_intubation->risque_ventil}}', '{{$_dossier_anesth->risque_intub}}', '{{$risque_intubation->risque_dentaire}}'{{/if}});">
          {{tr}}common-action-Get{{/tr}}
        </button>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="10">{{tr}}CConsultAnesth.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
