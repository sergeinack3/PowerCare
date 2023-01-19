{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=soins script=soins register=true}}

{{if ($sejour->_count_objectifs_soins !== null) && !$dossier_addictologie_id}}
  <script>
    if ($('objectif_soin')) {
      Control.Tabs.setTabCount('objectif_soin', {{$sejour->_count_objectifs_soins}});
      var count_retard = '{{$sejour->_count_objectifs_retard}}';
      if (count_retard != '0') {
        Control.Tabs.getTabAnchor('objectif_soin').addClassName('wrong');
      }
      else {
        Control.Tabs.getTabAnchor('objectif_soin').removeClassName('wrong');
      }
    }
  </script>
{{elseif $dossier_addictologie_id}}
  <script>
    Main.add(function(){
      Soins.dossier_addictologie_id = '{{$dossier_addictologie_id}}';
    });
  </script>
{{/if}}

<table class="tbl me-no-align me-no-box-shadow">
  <tr>
    <th colspan="10" class="title">
      <button style="float:right;margin-top:1px;" type="button" class="add notext me-primary" onclick="Soins.editObjectif('', '{{$sejour->_id}}');">{{tr}}Add{{/tr}}</button>
      <button style="float:right;margin-top:1px;" type="button" class="print notext me-tertiary" onclick="Soins.printObjectifsSoins('{{$sejour->_id}}');">{{tr}}Print{{/tr}}</button>
      {{tr}}CObjectifSoin{{/tr}} <small>({{$sejour->_count_objectifs_soins}})</small>
    </th>
  </tr>
  <tr>
    <th class="narrow">{{mb_label class=CObjectifSoin field=date}}</th>
    <th class="narrow">
      {{mb_label class=CObjectifSoin field=user_id}} / {{mb_label class=CMediusers field=function_id}}
    </th>
    <th>{{mb_label class=CObjectifSoin field=libelle}}</th>
    <th style="width: 25%;">{{mb_label class=CObjectifSoin field=moyens}}</th>
    <th class="narrow">{{mb_title class=CObjectifSoin field=delai}}</th>
    <th class="narrow">{{mb_label class=CObjectifSoin field=statut}}</th>
    <th>{{mb_label class=CObjectifSoin field=resultat}}</th>
    <th>{{tr}}CObjectifSoinReeval{{/tr}}</th>
    <th class="narrow">{{tr}}CObjectifSoinReeval-Closing{{/tr}}</th>
    <th class="narrow"></th>
  </tr>
  {{if $sejour->_count_objectifs_retard}}
    <tr>
      <td colspan="10">
        <div class="small-warning">
          {{$sejour->_count_objectifs_retard}} {{tr}}CObjectifSoinReeval-last_exceed_7_days{{/tr}}
        </div>
      </td>
    </tr>
  {{/if}}
  {{foreach from=$sejour->_ref_objectifs_soins item=_objectif}}
    <tr {{if $_objectif->statut == "atteint"}}style="opacity: 0.7;" class="hatching"{{/if}}>
      <td>{{mb_value object=$_objectif field=date}}</td>
      <td>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_objectif->_ref_user}}<br/>
        {{mb_include module=mediusers template=inc_vw_function function=$_objectif->_ref_user->_ref_function}}
      <br/>
      </td>
      <td class="text" style="color:{{if $_objectif->statut == "atteint"}}green{{elseif $_objectif->statut == "non_atteint"}}red{{/if}}">
        {{mb_value object=$_objectif field=libelle}}

        <br/>
        {{if $_objectif->objectif_soin_categorie_id}}
          <span class="compact">{{mb_value object=$_objectif->_ref_categorie}}</span>
        {{/if}}

        {{if $_objectif->_ref_cibles|@count}}
          <ul>
          {{foreach from=$_objectif->_ref_cibles item=_cible}}
            <li>
              <a href="#" onclick="Soins.addTransmission('{{$sejour->_id}}', '{{$app->user_id}}', null, '{{$_cible->object_id}}', '{{$_cible->object_class}}');">
              {{$_cible->_ref_object->_view}}
              </a>
            </li>
          {{/foreach}}
          <ul>
        {{/if}}
      </td>
      <td>{{mb_value object=$_objectif field=moyens}}</td>
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
      <td>
        <button type="button" class="edit notext" onclick="Soins.editObjectif('{{$_objectif->_id}}', '{{$_objectif->sejour_id}}');">{{tr}}Modify{{/tr}}</button>
      </td>
    </tr>
  {{foreachelse}}
  <tr>
    <td colspan="10" class="empty">
      {{tr}}CObjectifSoin.none{{/tr}}
    </td>
  </tr>
  {{/foreach}}
</table>
