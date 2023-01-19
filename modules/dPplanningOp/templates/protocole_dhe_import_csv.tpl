{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Import de protocoles de DHE Mediboard.</h2>

{{mb_include module=system template=inc_import_csv_info_intro}}
<li>
  <strong>{{tr}}CProtocole-Export-Titre-Nom fonction{{/tr}}</strong>
  ({{mb_label class=CFunctions field=text}}) - {{tr}}CProtocole.import_function_legend{{/tr}}
</li>
<li>
  <strong>{{tr}}CProtocole-Export-Titre-Nom praticien{{/tr}}</strong>
  ({{mb_label class=CMediusers field=_user_last_name }}) - {{tr}}CProtocole.import_chir_id_legend{{/tr}}
</li>
<li>
  <strong>{{tr}}CProtocole-Export-Titre-Prenom praticien{{/tr}}</strong>
  ({{mb_label class=CMediusers field=_user_first_name}}) - {{tr}}CProtocole.import_chir_id_legend{{/tr}}
</li>
<li>
  <strong>{{tr}}CProtocole-Export-Titre-Libelle intervention{{/tr}}</strong>
  (mise à jour du protocole ayant exactement le même libellé) - {{tr}}CProtocole.import_libelle_legend{{/tr}}
</li>
<li>
  <strong>{{tr}}CProtocole-Export-Titre-Libelle sejour{{/tr}}</strong>
  (mise à jour du protocole de séjour ayant exactement le même libellé) - {{tr}}CProtocole.import_libelle_sejour_legend{{/tr}}
</li>
<li><strong>{{tr}}CProtocole-Export-Titre-Duree intervention{{/tr}}</strong> (<code>HH:MM</code>) - {{tr}}CProtocole.import_libelle_obligatoire{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Actes ccam{{/tr}} (séparés par des barres verticales <code>|</code>)</li>
<li>{{tr}}CProtocole-Export-Titre-Diagnostic{{/tr}}</li>
<li>
  {{tr}}CProtocole-Export-Titre-Type hospitalisation{{/tr}}
  (parmi <code>comp</code>, <code>ambu</code>, <code>exte</code>, <code>seances</code>, <code>ssr</code> ou <code>psy</code>)
  - {{tr}}CProtocole.import_type_legend{{/tr}}
</li>
<li><strong>{{tr}}CProtocole-Export-Titre-Duree hospitalisation{{/tr}}</strong> - {{tr}}CProtocole.import_libelle_obligatoire{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Duree uscpo{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Duree preop{{/tr}} (<code>HH:MM</code>)</li>
<li>{{tr}}CProtocole-Export-Titre-Presence preop{{/tr}} (<code>HH:MM</code>)</li>
<li>{{tr}}CProtocole-Export-Titre-Presence postop{{/tr}} (<code>HH:MM</code>)</li>
<li>{{tr}}CProtocole-Export-Titre-UF hebergement{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-UF medicale{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-UF de soins{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Facturable{{/tr}} ({{tr}}CProtocole-Export-Titre-Facturable-desc{{/tr}})</li>
<li>{{tr}}CProtocole-Export-Titre-RRAC{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Medical{{/tr}} (par défaut : <code>0</code> pour un protocole d'intervention, <code>1</code> pour un protocole de séjour uniquement)</li>
<li>{{tr}}CProtocole-Export-Titre-Extempo{{/tr}}</li>
<li>
  {{tr}}CProtocole-Export-Titre-Cote{{/tr}}
  (parmi <code>comp</code>, <code>droit</code>, <code>gauche</code>, <code>haut</code>, <code>bas</code>, <code>bilatéral</code>, <code>total</code> ou <code>inconnu</code>)
</li>
<li>{{tr}}CProtocole-Export-Titre-Bilan preop{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Materiel{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Examens perop{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Depassement honoraires{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Forfait clinique{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Fournitures{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Remarques intervention{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Convalescence{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Remarques sejour{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Septique{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Duree heure hospitalisation{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Pathologie{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Type pec{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Hospitalisation de jour{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Service{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Heure entree{{/tr}}</li>
<li>{{tr}}CProtocole-Export-Titre-Mode traitement{{/tr}}</li>
{{if $conf.dPbloc.CPlageOp.systeme_materiel === "expert"}}
  <li>{{tr}}CBesoinRessource{{/tr}}</li>
{{/if}}
{{if "appFineClient"|module_active}}
  <li>{{tr}}CAppFineClientOrderPack-msg-Order|pl{{/tr}} ({{tr}}AppFine{{/tr}})</li>
{{/if}}
<li>{{mb_label class=CProtocole field=circuit_ambu}}</li>
<li>{{tr}}CProtocole-Import-Titre-Actif{{/tr}} {{tr}}CProtocole-Import-Titre-Actif-desc{{/tr}}</li>
{{mb_include module=system template=inc_import_csv_info_outro}}

<form name="import" method="post" action="?m={{$m}}&{{$actionType}}={{$action}}&dialog=1" enctype="multipart/form-data">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="csv_result" value="0" />
  <input type="hidden" name="suppressHeaders" value="0" />
  <input type="hidden" name="{{$actionType}}" value="{{$action}}" />
  <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />
  <input type="file" name="import" />
  <input type="checkbox" name="dryrun" value="1" checked
         onclick="this.form.dlbutton[this.checked ? 'show' : 'hide']()"/>
  <label for="dryrun">Essai à blanc</label>
  <button class="submit">{{tr}}Save{{/tr}}</button>
  <button class="download" name="dlbutton" type="button" onclick="
      $V(this.form.csv_result, 1);
      $V(this.form.suppressHeaders, 1);
      this.form.submit();
      $V(this.form.csv_result, 0);
      $V(this.form.suppressHeaders, 0);
      setTimeout(
        function() {
          this.form.reset();
          $('waitingMsgMask').hide();
          $('waitingMsgText').setStyle({top: '-1500px'});
        }.bind(this),
        3000
      );
">
    {{tr}}CProtocole-Export-Action-Enregistrer et exporter{{/tr}}
  </button>
</form>

{{if $results|@count}}
  <div style="display: flex">
    <div class="warning">{{tr}}errors{{/tr}} : {{$counts.error}}</div>
    <div class="info">{{tr}}common-Creation{{/tr}} : {{$counts.created}}</div>
    <div class="info">{{tr}}common-Update{{/tr}} : {{$counts.updated}}</div>
  </div>
  <table class="tbl">
    <tr>
      <th class="title" colspan={{math equation=x+y x=43 y=$idex_names|@count}}>{{$results|@count}} protocoles trouvés</th>
    </tr>
    <tr>
      <th>Etat</th>
      <th>{{mb_title class=CProtocole field=function_id}}</th>
      <th>{{mb_title class=CProtocole field=chir_id}} <br />{{mb_title class=CMediusers field=_user_last_name }}</th>
      <th>{{mb_title class=CProtocole field=chir_id}} <br />{{mb_title class=CMediusers field=_user_first_name}}</th>
      <th>{{mb_title class=CProtocole field=libelle}}</th>
      <th>{{mb_title class=CProtocole field=libelle_sejour}}</th>
      <th>{{mb_title class=CProtocole field=temp_operation}}</th>
      <th>{{mb_title class=CProtocole field=codes_ccam}}</th>
      <th>{{mb_title class=CProtocole field=DP}}</th>
      <th>{{mb_title class=CProtocole field=type}}</th>
      <th>{{mb_title class=CProtocole field=duree_hospi}}</th>
      <th>{{mb_title class=CProtocole field=duree_uscpo}}</th>
      <th>{{mb_title class=CProtocole field=duree_preop}}</th>
      <th>{{mb_title class=CProtocole field=presence_preop}}</th>
      <th>{{mb_title class=CProtocole field=presence_postop}}</th>
      <th>{{mb_title class=CProtocole field=uf_hebergement_id}}</th>
      <th>{{mb_title class=CProtocole field=uf_medicale_id}}</th>
      <th>{{mb_title class=CProtocole field=uf_soins_id}}</th>
      <th>{{mb_title class=CProtocole field=facturable}}</th>
      <th>{{mb_title class=CProtocole field=RRAC}}</th>
      <th>{{mb_title class=CProtocole field=for_sejour}}</th>
      <th>{{mb_title class=CProtocole field=exam_extempo}}</th>
      <th>{{mb_title class=CProtocole field=cote}}</th>
      <th>{{mb_title class=CProtocole field=examen}}</th>
      <th>{{mb_title class=CProtocole field=materiel}}</th>
      <th>{{mb_title class=CProtocole field=exam_per_op}}</th>
      <th>{{mb_title class=CProtocole field=depassement}}</th>
      <th>{{mb_title class=CProtocole field=forfait}}</th>
      <th>{{mb_title class=CProtocole field=fournitures}}</th>
      <th>{{mb_title class=CProtocole field=rques_operation}}</th>
      <th>{{mb_title class=CProtocole field=convalescence}}</th>
      <th>{{mb_title class=CProtocole field=rques_sejour}}</th>
      <th>{{mb_title class=CProtocole field=septique}}</th>
      <th>{{mb_title class=CProtocole field=duree_heure_hospi}}</th>
      <th>{{mb_title class=CProtocole field=pathologie}}</th>
      <th>{{mb_title class=CProtocole field=type_pec}}</th>
      <th>{{mb_title class=CProtocole field=hospit_de_jour}}</th>
      <th>{{mb_title class=CProtocole field=service_id}}</th>
      <th>{{mb_title class=CProtocole field=time_entree_prevue}}</th>
      <th>{{mb_title class=CProtocole field=charge_id}}</th>
      {{if $conf.dPbloc.CPlageOp.systeme_materiel === "expert"}}
        <th>{{tr}}CBesoinRessource{{/tr}}</th>
      {{/if}}
      {{if "appFineClient"|module_active}}
        <th>{{tr}}CAppFineClientOrderPack-msg-Order|pl{{/tr}} ({{tr}}AppFine{{/tr}})</th>
      {{/if}}
      <th>{{mb_title class=CProtocole field=circuit_ambu}}</th>
      {{foreach from=$idex_names item=_idex_name}}
        <th>{{tr}}CIdSante400{{/tr}} - {{$_idex_name}}</th>
      {{/foreach}}
        <th>{{mb_title class=CProtocole field=actif}}</th>
    </tr>
    {{foreach from=$results item=_protocole}}
      <tr>
        {{if count($_protocole.errors)}}
          <td class="text warning compact">
            {{foreach from=$_protocole.errors item=_error}}
              <div>{{$_error}}</div>
            {{/foreach}}
          </td>
        {{else}}
          <td class="text ok">
            OK
          </td>
        {{/if}}

        <td class="text">{{$_protocole.function_name}}</td>
        <td class="text">{{$_protocole.praticien_lastname}}</td>
        <td class="text">{{$_protocole.praticien_firstname}}</td>
        <td class="text">{{$_protocole.motif}}</td>
        <td class="text">{{$_protocole.libelle_sejour}}</td>
        <td class="text">{{$_protocole.temp_operation}}</td>
        <td class="text">{{$_protocole.codes_ccam}}</td>
        <td class="text">{{$_protocole.DP}}</td>
        <td class="text">{{$_protocole.type_hospi}}</td>
        <td class="text">{{$_protocole.duree_hospi}}</td>
        <td class="text">{{$_protocole.duree_uscpo}}</td>
        <td class="text">{{$_protocole.duree_preop}}</td>
        <td class="text">{{$_protocole.presence_preop}}</td>
        <td class="text">{{$_protocole.presence_postop}}</td>
        <td class="text">{{$_protocole.uf_hebergement}}</td>
        <td class="text">{{$_protocole.uf_medicale}}</td>
        <td class="text">{{$_protocole.uf_soins}}</td>
        <td class="text">{{$_protocole.facturable}}</td>
        <td class="text">{{$_protocole.RRAC}}</td>
        <td class="text">{{$_protocole.for_sejour}}</td>
        <td class="text">{{$_protocole.Exam_extempo_prevu}}</td>
        <td class="text">{{$_protocole.cote}}</td>
        <td class="text">{{$_protocole.bilan_preop}}</td>
        <td class="text">{{$_protocole.materiel_a_prevoir}}</td>
        <td class="text">{{$_protocole.examens_perop}}</td>
        <td class="text">{{$_protocole.depassement_honoraires}}</td>
        <td class="text">{{$_protocole.forfait_clinique}}</td>
        <td class="text">{{$_protocole.fournitures}}</td>
        <td class="text">{{$_protocole.rques_interv}}</td>
        <td class="text">{{$_protocole.convalesence}}</td>
        <td class="text">{{$_protocole.rques_sejour}}</td>
        <td class="text">{{$_protocole.septique}}</td>
        <td class="text">{{$_protocole.duree_heure_hospi}}</td>
        <td class="text">{{$_protocole.pathologie}}</td>
        <td class="text">{{$_protocole.type_pec}}</td>
        <td class="text">{{$_protocole.hospit_de_jour}}</td>
        <td class="text">{{$_protocole.service}}</td>
        <td class="text">{{$_protocole.time_entree_prevue}}</td>
        <td class="text">{{$_protocole.charge_price_indicator}}</td>
        {{if $conf.dPbloc.CPlageOp.systeme_materiel === "expert"}}
          <td class="text">{{$_protocole._ref_besoins}}</td>
        {{/if}}
        {{if "appFineClient"|module_active}}
          <td>{{$_protocole._ref_packs_appFine}}</td>
        {{/if}}
        <td class="text">{{$_protocole.circuit_ambu}}</td>
        {{foreach from=$idex_names item=_idex_name}}
          <td class="text">{{$_protocole.$_idex_name}}</td>
        {{/foreach}}
        <td class="text">{{$_protocole.actif}}</td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}

