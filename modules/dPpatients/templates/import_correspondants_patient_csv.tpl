{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Import de correspondants patients</h2>

{{mb_include module=system template=inc_import_csv_info_intro}}
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=relation}} (assurance, autre, confiance, employeur, inconnu, prevenir ou
  representant_th)
</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=relation_autre}}</li>
<li class="me-small-fields"><strong>{{mb_label class=CCorrespondantPatient field=nom}} *</strong></li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=surnom}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=nom_jeune_fille}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=prenom}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=naissance}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=sex}} (u, f ou m)</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=adresse}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=cp}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=ville}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=tel}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=mob}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=fax}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=urssaf}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=parente}} (ami, ascendant, autre, beau_fils, colateral, collegue, compagnon, conjoint,
  directeur, divers, employeur, employe, enfant, enfant_adoptif, entraineur, epoux, frere,<br /> grand_parent, mere, pere,
  petits_enfants, proche, proprietaire, soeur ou tuteur)
</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=parente_autre}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=email}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=remarques}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=ean}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=ean_base}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=type_pec}} (TG, TP ou TS)</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=date_debut}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=date_fin}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=num_assure}}</li>
<li class="me-small-fields">{{mb_label class=CCorrespondantPatient field=employeur}}</li>
{{mb_include module=system template=inc_import_csv_info_outro}}

<form method="post" action="?m={{$m}}&amp;{{$actionType}}={{$action}}&amp;dialog=1&amp;" name="import" enctype="multipart/form-data">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="{{$actionType}}" value="{{$action}}" />
  <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />
  <input type="file" name="import" />
  <input type="hidden" name="dryrun" value="1" />
  <input type="hidden" name="force_update" value="0" />
  <span style="display: inline-block">
    <label>
      <input type="checkbox" name="dryrun_view" value="1" checked="checked" onchange="$V(this.form.dryrun, this.checked ? 1 : 0);" />
      {{tr}}DryRun{{/tr}}
    </label><br />
    <label title="{{tr}}CCorrespondantPatient.force_update.desc{{/tr}}">
      <input type="checkbox" name="force_update_view" value="0" onchange="$V(this.form.force_update, this.checked ? '1' : '0');" />
      {{tr}}CCorrespondantPatient.force_update{{/tr}}
    </label>
  </span>
  <button type="submit" class="submit">{{tr}}Save{{/tr}}</button>
</form>

{{if $results|@count}}
  <table class="tbl">
    <tr>
      <th class="title" colspan="22">{{$results|@count}} correspondants patient trouvés</th>
    </tr>
    <tr>
      <th class="narrow">Etat</th>
      <th>{{mb_title class=CCorrespondantPatient field=relation}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=relation_autre}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=nom}}</strong></th>
      <th>{{mb_title class=CCorrespondantPatient field=surnom}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=nom_jeune_fille}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=prenom}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=naissance}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=sex}} (u, f ou m)</th>
      <th>{{mb_title class=CCorrespondantPatient field=adresse}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=cp}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=ville}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=tel}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=mob}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=fax}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=urssaf}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=parente}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=parente_autre}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=email}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=remarques}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=ean}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=ean_base}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=type_pec}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=date_debut}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=date_fin}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=num_assure}}</th>
      <th>{{mb_title class=CCorrespondantPatient field=employeur}}</th>
    </tr>
    {{foreach from=$results item=_code}}
      <tr>
        {{if $_code.error == "0"}}
          <td class="text ok">Correspondant {{if $dryrun}}importable{{else}}importé{{/if}}</td>
        {{else}}
          <td class="text warning compact">
            <div>{{$_code.error}}</div>
          </td>
        {{/if}}
        <td>{{$_code.relation}}</td>
        <td>{{$_code.relation_autre}}</td>
        <td>{{$_code.nom}}</strong></td>
        <td>{{$_code.surnom}}</td>
        <td>{{$_code.nom_jeune_fille}}</td>
        <td>{{$_code.prenom}}</td>
        <td>{{$_code.naissance}}</td>
        <td>{{$_code.sex}} (u, f ou m)</td>
        <td>{{$_code.adresse}}</td>
        <td>{{$_code.cp}}</td>
        <td>{{$_code.ville}}</td>
        <td>{{$_code.tel}}</td>
        <td>{{$_code.mob}}</td>
        <td>{{$_code.fax}}</td>
        <td>{{$_code.urssaf}}</td>
        <td>{{$_code.parente}}</td>
        <td>{{$_code.parente_autre}}</td>
        <td>{{$_code.email}}</td>
        <td>{{$_code.remarques}}</td>
        <td>{{$_code.ean}}</td>
        <td>{{$_code.ean_base}}</td>
        <td>{{$_code.type_pec}}</td>
        <td>{{$_code.date_debut}}</td>
        <td>{{$_code.date_fin}}</td>
        <td>{{$_code.num_assure}}</td>
        <td>{{$_code.employeur}}</td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}