{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{assign var=patient value=$sejour->_ref_patient}}
<script>
  Main.add(function(){
    window.print();
  });
</script>


{{if $header_content}}
  {{$header_content|smarty:nodefaults}}
{{else}}
  <div class="form">
    <tr>
      <th>{{tr}}CPatient{{/tr}}</th>
      <td>
        <span>
          {{$patient}}
        </span>
      </td>
    </tr>
    {{if $dossier_addictologie->_id}}
    <tr>
      <th>{{mb_label object=$dossier_addictologie field=sejour_id}}</th>
      <td>{{mb_value object=$dossier_addictologie field=sejour_id}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$dossier_addictologie field=referent_user_id}}</th>
      <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$dossier_addictologie->_ref_referent_user}}</td>
    </tr>
    {{/if}}
  </div>
{{/if}}

<table class="tbl">
  <tr>
    <th colspan="9" class="title">
      {{tr}}CObjectifSoin{{/tr}} <small>({{$sejour->_count_objectifs_soins}})</small>
    </th>
  </tr>
  {{foreach from=$objectifs_by_categorie key=_categorie item=_objectifs}}
    <tbody>
      <tr>
        <th colspan="9" class="title">
          {{if $_categorie}}
            {{$_categorie}}
          {{else}}
            {{tr}}CObjectifSoinCategorie.none{{/tr}}
          {{/if}}
        </th>
      </tr>
      <tr>
        <th>{{mb_label class=CObjectifSoin field=libelle}}</th>
        <th>{{mb_label class=CObjectifSoin field=moyens}}</th>
        <th>{{mb_label class=CObjectifSoin field=priorite}}</th>
        <th class="narrow">{{mb_title class=CObjectifSoin field=delai}}</th>
        <th class="narrow">{{mb_label class=CObjectifSoin field=statut}}</th>
        <th>{{mb_label class=CObjectifSoin field=resultat}}</th>
        <th>{{tr}}CObjectifSoinReeval{{/tr}}</th>
        <th class="narrow">{{tr}}CObjectifSoinReeval-Closing{{/tr}}</th>
        <th>{{mb_label class=CObjectifSoin field=commentaire}}</th>
      </tr>
      {{foreach from=$_objectifs item=_objectif}}
        <tr {{if $_objectif->statut == "atteint"}}style="opacity: 0.7;" class="hatching"{{/if}}>
          <td class="text" style="color:{{if $_objectif->statut == "atteint"}}green{{elseif $_objectif->statut == "non_atteint"}}red{{/if}}">
            {{mb_value object=$_objectif field=libelle}}
          </td>
          <td>{{mb_value object=$_objectif field=moyens}}</td>
          <td>{{mb_value object=$_objectif field=priorite}}</td>
          <td>{{mb_value object=$_objectif field=delai}}</td>
          <td>{{mb_value object=$_objectif field=statut}}</td>
          <td>
            {{mb_value object=$_objectif field=resultat}}
          </td>
          <td class="text">
            <ul>
              {{foreach from=$_objectif->_ref_reevaluations item=_reeval}}
                <li>
                  {{mb_value object=$_reeval field=date}}: {{$_reeval->commentaire}}
                </li>
                {{foreachelse}}
                <li class="empty">{{tr}}CObjectifSoinReeval.none{{/tr}}</li>
              {{/foreach}}
            </ul>
          </td>
          <td>
            {{if $_objectif->cloture_user_id}}
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_objectif->_ref_cloture_user}}
              <br />{{mb_value object=$_objectif field=cloture_date}}
            {{/if}}
          </td>
          <td>{{mb_value object=$_objectif field=commentaire}}</td>
        </tr>
      {{/foreach}}
    </tbody>
  {{/foreach}}
</table>

{{if $footer_content}}
{{$footer_content|smarty:nodefaults}}
{{/if}}