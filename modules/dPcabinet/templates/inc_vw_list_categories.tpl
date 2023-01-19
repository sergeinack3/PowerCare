{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table id="vw_categorie_table_liste_categories" class="tbl">
  <tr>
    <th colspan="10">{{tr}}CConsultationCategorie.list{{/tr}}</th>
  </tr>
  <tr>
    <th>{{mb_title class=CConsultationCategorie field=nom_categorie}}</th>
    <th class="narrow">{{mb_title class=CConsultationCategorie field=nom_icone}}</th>
    <th class="narrow">{{mb_title class=CConsultationCategorie field=duree}}</th>
    <th class="narrow">{{mb_title class=CConsultationCategorie field=seance}}</th>
    <th class="narrow">{{mb_title class=CConsultationCategorie field=max_seances}}</th>
    <th class="narrow">{{mb_title class=CConsultationCategorie field=anticipation}}</th>
    <th class="narrow">{{mb_title class=CConsultationCategorie field=couleur}}</th>
  </tr>
  {{if $droit}}
    {{foreach from=$categories item=_categorie}}
      <tr>
        <td class="clickable" onclick="CategorieFunction.edit('{{$_categorie->_id}}');">
          {{if $selPraticien && $_categorie->function_id}}
            ( {{$_categorie->_ref_function->_view}} )
          {{/if}}
          {{$_categorie->nom_categorie|spancate}}
          <br />
          <span class="compact">{{$_categorie->commentaire|spancate}}</span>
        </td>
        <td>
          {{mb_include module=cabinet template=inc_icone_categorie_consult categorie=$_categorie}}
        </td>
        <td>x{{$_categorie->duree}}</td>
        <td>{{mb_value object=$_categorie field=seance}}</td>
        <td>{{mb_value object=$_categorie field=max_seances}}</td>
        <td>{{mb_value object=$_categorie field=anticipation}}</td>
        <td>{{mb_value object=$_categorie field=couleur}}</td>
      </tr>
      {{foreachelse}}
      <tr>
        <td class="empty" colspan="10">{{tr}}CConsultationCategorie.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  {{else}}
    <tr>
      <td class="empty" colspan="10">
        {{if $selPraticien}}
          {{tr}}CConsultationCategorie.no_acces_cabinet{{/tr}}
        {{else}}
          {{tr}}CConsultationCategorie.no_acces_cabinet{{/tr}}
        {{/if}}
      </td>
    </tr>
  {{/if}}
</table>