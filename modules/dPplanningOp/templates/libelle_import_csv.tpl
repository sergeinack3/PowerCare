{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Import de libellés</h2>

{{mb_include module=system template=inc_import_csv_info_intro}}
<li><strong>{{mb_label class=CLibelleOp field=statut}}</strong></li>
<li><strong>{{mb_label class=CLibelleOp field=nom}}</strong> ({{mb_label class=CFunctions field=text}})</li>
<li><strong>{{mb_label class=CLibelleOp field=date_debut}}</strong></li>
<li><strong>{{mb_label class=CLibelleOp field=date_fin}}</strong></li>
<li><strong>{{mb_label class=CLibelleOp field=services}}</strong></li>
<li><strong>{{mb_label class=CLibelleOp field=mots_cles}}</strong></li>
<li><strong>{{mb_label class=CLibelleOp field=numero}}</strong></li>
<li><strong>{{mb_label class=CLibelleOp field=version}}</strong></li>
{{mb_include module=system template=inc_import_csv_info_outro}}

<form method="post" action="?m={{$m}}&{{$actionType}}={{$action}}&dialog=1" name="import" enctype="multipart/form-data">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="{{$actionType}}" value="{{$action}}" />
  <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />
  <input type="file" name="import" />
  <input type="checkbox" name="dryrun" value="1" checked />
  <label for="dryrun">{{tr}}DryRun{{/tr}}</label>
  <button class="submit">{{tr}}Save{{/tr}}</button>
</form>

{{if $results|@count}}
  <table class="tbl">
    <tr>
      <th class="title" colspan="9">{{$results|@count}} libellés trouvés</th>
    </tr>
    <tr>
      <th>Etat</th>
      <th>{{mb_title class=CLibelleOp field=statut}}</th>
      <th>{{mb_title class=CLibelleOp field=nom}}</th>
      <th>{{mb_title class=CLibelleOp field=date_debut}}</th>
      <th>{{mb_title class=CLibelleOp field=date_fin}}</th>
      <th>{{mb_title class=CLibelleOp field=services}}</th>
      <th>{{mb_title class=CLibelleOp field=mots_cles}}</th>
      <th>{{mb_title class=CLibelleOp field=numero}}</th>
      <th>{{mb_title class=CLibelleOp field=version}}</th>
    </tr>
    {{foreach from=$results item=libelle}}
      <tr>
        {{if count($libelle.errors)}}
          <td class="text warning compact">
            {{foreach from=$libelle.errors item=_error}}
              <div>{{$_error}}</div>
            {{/foreach}}
          </td>
        {{else}}
          <td class="text ok">
            OK
          </td>
        {{/if}}

        <td class="text">{{$libelle.statut}}</td>
        <td class="text">{{$libelle.nom}}</td>
        <td class="text">{{$libelle.date_debut}}</td>
        <td class="text">{{$libelle.date_fin}}</td>
        <td class="text">{{$libelle.services}}</td>
        <td class="text">{{$libelle.mots_cles}}</td>
        <td class="text">{{$libelle.numero}}</td>
        <td class="text">{{$libelle.version}}</td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}