{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<html>
  <head>
    <link href='{{$css_path}}' rel='stylesheet'/>
  </head>
  <body>
    <table class="print me-no-box-shadow">
      <tr>
        <th class="title" colspan="4">
            {{$patient}}
        </th>
      </tr>
      <tr>
        <th class="quarterPane">{{mb_label object=$patient field=sexe}}</th>
        <td class="quarterPane">{{mb_value object=$patient field=sexe}}</td>
        <th class="quarterPane">{{mb_label object=$patient field=naissance}}</th>
        <td class="quarterPane">{{mb_value object=$patient field=naissance}}</td>
      </tr>
        {{if !$sejour_only}}
          <tr>
            <th class="title" colspan="4">{{tr}}COperation{{/tr}}</th>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$operation field=chir_id}}</th>
            <td class="quarterPane">{{$praticien}}</td>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$operation field=libelle}}</th>
            <td class="quarterPane">{{mb_value object=$operation field=libelle}}</td>
          </tr>
          <tr>
            <th class="quarterPane">{{tr}}CActe{{/tr}}</th>
            <td class="quarterPane"></td>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$operation field=cote}}</th>
            <td class="quarterPane">{{mb_value object=$operation field=cote}}</td>
            <th class="quarterPane">{{mb_label object=$operation field=temp_operation}}</th>
            <td class="quarterPane">{{mb_value object=$operation field=temp_operation}}</td>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$operation field=urgence}}</th>
            <td class="quarterPane">{{$operation->urgence}}</td>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$operation field=materiel}}</th>
            <td class="quarterPane">{{mb_value object=$operation field=materiel}}</td>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$operation field=rques}}</th>
            <td class="quarterPane">{{mb_value object=$operation field=rques}}</td>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$operation field=presence_preop}}</th>
            <td class="quarterPane">{{mb_value object=$operation field=presence_preop}}</td>
            <th class="quarterPane">{{mb_label object=$operation field=presence_postop}}</th>
            <td class="quarterPane">{{mb_value object=$operation field=presence_postop}}</td>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$operation field=duree_bio_nettoyage}}</th>
            <td class="quarterPane">{{mb_value object=$operation field=duree_bio_nettoyage}}</td>
            <th class="quarterPane">{{mb_label object=$operation field=duree_uscpo}}</th>
            <td class="quarterPane">{{mb_value object=$operation field=duree_uscpo}}</td>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$operation field=exam_extempo}}</th>
            <td class="quarterPane">{{$operation->exam_extempo}}</td>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$operation field=examen}}</th>
            <td class="quarterPane">{{mb_value object=$operation field=examen}}</td>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$operation field=exam_per_op}}</th>
            <td class="quarterPane">{{mb_value object=$operation field=exam_per_op}}</td>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$operation field=conventionne}}</th>
            <td class="quarterPane">{{$operation->conventionne}}</td>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$operation field=depassement}}</th>
            <td class="quarterPane">{{mb_value object=$operation field=depassement}}</td>
            <th class="quarterPane">{{mb_label object=$operation field=forfait}}</th>
            <td class="quarterPane">{{mb_value object=$operation field=forfait}}</td>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$operation field=reglement_dh_chir}}</th>
            <td class="quarterPane">{{mb_value object=$operation field=reglement_dh_chir}}</td>
            <th class="quarterPane">{{mb_label object=$operation field=reglement_dh_anesth}}</th>
            <td class="quarterPane">{{mb_value object=$operation field=reglement_dh_anesth}}</td>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$operation field=anesth_id}}</th>
            <td class="quarterPane">{{$anesth}}</td>
            <th class="quarterPane">{{mb_label object=$operation field=type_anesth}}</th>
            <td class="quarterPane">{{mb_value object=$operation field=type_anesth}}</td>
          </tr>
        {{/if}}
      <tr>
        <th class="title" colspan="4">{{tr}}CSejour{{/tr}}</th>
      </tr>
      <tr>
        <th class="quarterPane">{{mb_label object=$sejour field=entree}}</th>
        <td class="quarterPane">{{mb_value object=$sejour field=entree}}</td>
        <th class="quarterPane">{{mb_label object=$sejour field=sortie}}</th>
        <td class="quarterPane">{{mb_value object=$sejour field=sortie}}</td>
      </tr>
      <tr>
        <th class="quarterPane">{{mb_label object=$sejour field=type}}</th>
        <td class="quarterPane">{{mb_value object=$sejour field=type}}</td>
      </tr>
      <tr>
        <th class="quarterPane">{{mb_label object=$sejour field=libelle}}</th>
        <td class="quarterPane">{{mb_value object=$sejour field=libelle}}</td>
      </tr>
      <tr>
        <th class="quarterPane">{{mb_label object=$sejour field=praticien_id}}</th>
        <td class="quarterPane">{{$praticien}}</td>
        <th class="quarterPane">{{mb_label object=$sejour field=service_id}}</th>
        <td class="quarterPane">{{mb_value object=$service field=nom}}</td>
      </tr>
      <tr>
        <th class="quarterPane">{{mb_label object=$sejour field=rques}}</th>
        <td class="quarterPane">{{mb_value object=$sejour field=rques}}</td>
        <th class="quarterPane">{{mb_label object=$sejour field=ATNC}}</th>
        <td class="quarterPane">{{$sejour->ATNC}}</td>
      </tr>
      <tr>
        <th class="quarterPane">{{mb_label object=$sejour field=facturable}}</th>
        <td class="quarterPane">{{$sejour->facturable}}</td>
      </tr>
      <tr>
        <th class="quarterPane">{{mb_label object=$sejour field=ald}}</th>
        <td class="quarterPane">{{$sejour->ald}}</td>
        <th class="quarterPane">{{mb_label object=$sejour field=presence_confidentielle}}</th>
        <td class="quarterPane">{{$sejour->presence_confidentielle}}</td>
      </tr>
      <tr>
        <th class="quarterPane">{{mb_label object=$sejour field=aide_organisee}}</th>
        <td class="quarterPane">{{mb_value object=$sejour field=aide_organisee}}</td>
      </tr>
      <tr>
        <th class="quarterPane">{{mb_label object=$sejour field=handicap}}</th>
        <td class="quarterPane">{{$sejour->handicap}}</td>
      </tr>
      <tr>
        <th class="quarterPane">{{mb_label object=$sejour field=frais_sejour}}</th>
        <td class="quarterPane">{{mb_value object=$sejour field=frais_sejour}}</td>
        <th class="quarterPane">{{mb_label object=$sejour field=reglement_frais_sejour}}</th>
        <td class="quarterPane">{{mb_value object=$sejour field=reglement_frais_sejour}}</td>
      </tr>
      <tr>
        <th class="quarterPane">{{mb_label object=$sejour field=isolement}}</th>
        <td class="quarterPane">{{$sejour->isolement}}</td>
        <th class="quarterPane">{{mb_label object=$sejour field=nuit_convenance}}</th>
        <td class="quarterPane">{{$sejour->nuit_convenance}}</td>
      </tr>
      <tr>
        <th class="quarterPane">{{mb_label object=$sejour field=hospit_de_jour}}</th>
        <td class="quarterPane">{{$sejour->hospit_de_jour}}</td>
        <th class="quarterPane">{{mb_label object=$sejour field=consult_accomp}}</th>
        <td class="quarterPane">{{$sejour->consult_accomp}}</td>
      </tr>
      <tr>
        <th class="quarterPane">{{mb_label object=$sejour field=convalescence}}</th>
        <td class="quarterPane">{{mb_value object=$sejour field=convalescence}}</td>
      </tr>
    </table>
  </body>
</html>
