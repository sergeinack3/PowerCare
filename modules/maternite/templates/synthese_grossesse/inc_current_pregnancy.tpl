{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="me-w100" style="font-size: 100%;">
  <tr>
    <th class="category" colspan="4">{{tr}}CGrossesse-Current pregnancy{{/tr}}</th>
  </tr>
  <tr>
    <th>{{mb_label object=$grossesse field=_semaine_grossesse}}</th>
    <td>
      <strong>
          {{$grossesse->_semaine_grossesse}} {{tr}}CGrossesse-_semaine_grossesse-court{{/tr}}
        + {{$grossesse->_reste_semaine_grossesse}} {{tr}}Day-court{{/tr}}
      </strong>
    </td>
    <th>{{mb_label object=$grossesse field=terme_prevu}}</th>
    <td>
      <strong>{{'Ox\Core\CMbDT::format'|static_call:"":$grossesse->terme_prevu|date_format:"%d %B %Y"}}</strong>
    </td>
  </tr>
  <tr>
    <th>{{mb_label class=CConsultationPostNatEnfant field=poids}}</th>
    <td>
        {{mb_ternary var=last_poids test=$last_constantes.0->poids value=$last_constantes.0->poids other=$constantes_maman->poids}}
        {{$last_poids}} {{tr}}common-kg{{/tr}}
        {{if $last_constantes.0->poids}}
          ({{tr var1=$last_constantes.1.poids|date_format:$conf.date}}common-the %s{{/tr}})
          (
          <strong>{{if $difference_poids > 0}}+ {{/if}}{{$difference_poids}} {{tr}}common-kg{{/tr}}</strong>
          )
        {{/if}}
    </td>
  </tr>
  <tr>
    <th>{{tr}}CGrossesse.determination_date_grossesse.ddr{{/tr}}</th>
    <td>{{mb_value object=$grossesse field=date_dernieres_regles}}</td>
    <th>{{tr}}CGrossesse-multiple-desc{{/tr}}</th>
    <td>{{mb_value object=$grossesse field=multiple}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$grossesse field=date_debut_grossesse}}</th>
    <td>{{mb_value object=$grossesse field=date_debut_grossesse}}</td>
      {{if $grossesse->multiple}}
        <th>{{mb_label object=$grossesse field=nb_foetus}}</th>
        <td>{{mb_value object=$grossesse field=nb_foetus}}</td>
      {{/if}}
  </tr>
  <tr>
    <th>{{mb_label object=$grossesse field=nb_grossesses_ant}}</th>
    <td>{{mb_value object=$grossesse field=nb_grossesses_ant}}</td>
      {{if $grossesse->multiple}}
        <th>{{tr}}CSurvEchoGrossesse-Chorionicity{{/tr}}</th>
        <td>
            {{foreach from=$surv_echographies item=_surv_echographie name=list_echo}}
                {{if $smarty.foreach.list_echo.first}}
                    {{if $_surv_echographie->mcba}}
                        {{mb_label object=$_surv_echographie field=mcba}}
                    {{elseif $_surv_echographie->mcma}}
                        {{mb_label object=$_surv_echographie field=mcma}}
                    {{elseif $_surv_echographie->bcba}}
                        {{mb_label object=$_surv_echographie field=bcba}}
                    {{/if}}
                {{/if}}
                {{foreachelse}}
                {{tr}}CDossierPerinat.type_surveillance.{{/tr}}
            {{/foreach}}
        </td>
      {{/if}}
  </tr>
  <tr>
    <th>{{mb_label object=$dossier_perinatal field=ant_obst_nb_gr_acc}}</th>
    <td>{{mb_value object=$dossier_perinatal field=ant_obst_nb_gr_acc}} {{tr var1=$dossier_perinatal->ant_obst_nb_gr_cesar}}CGrossesse-including %s caesarean{{/tr}}</td>
  </tr>
  {{if $grossesse->date_debut_grossesse}}
    <tr>
      <td>
        <br/>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$grossesse field=estimate_first_ultrasound_date}}</th>
      <td>{{mb_value object=$grossesse field=estimate_first_ultrasound_date}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$grossesse field=estimate_second_ultrasound_date}}</th>
      <td>{{mb_value object=$grossesse field=estimate_second_ultrasound_date}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$grossesse field=estimate_third_ultrasound_date}}</th>
      <td>{{mb_value object=$grossesse field=estimate_third_ultrasound_date}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$grossesse field=estimate_sick_leave_date}}</th>
      <td>{{mb_value object=$grossesse field=estimate_sick_leave_date}}</td>
    </tr>
  {{/if}}
</table>
