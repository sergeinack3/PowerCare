{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPccam script=CCodageCCAM ajax=true}}
<div id="affichage_ccam_informations_generales" style="display: none">
  <table class="tbl">
    <th style="width: 20%;">Dernière mise<br />à jour</th>
    <td class="text">{{$date_versions[0]}}</td>
    </tr>
    <tr>
      <th>Place dans la CCAM</th>
      <td class="text">{{$code_complet->place}}</td>
    </tr>
    <tr>
      <th>Code de regroupement</th>
      {{foreach name=first from=$code_complet->_ref_code_ccam->_ref_activites[1]->_ref_classif item=_classif}}
        {{if $smarty.foreach.first.first}}
          <td>{{$_classif->_regroupement}} ({{$_classif->code_regroupement}})</td>
        {{/if}}
      {{/foreach}}
    </tr>
    {{foreach from=$code_complet->remarques item=_remarque}}
      <tr>
        <th>Remarques</th>
        <td>{{$_remarque}}</td>
      </tr>
    {{/foreach}}
  </table>
  <table class="tbl">
    {{foreach from=$code_complet->activites item=_activite key=_key}}
      <tr>
        <th class="category" style="width: 50%;">Activité {{$_key}}</th>
      </tr>
      {{foreach from=$_activite->phases item=_phase}}
        <tr>
          <td style="text-align: center;">Phase {{$_phase->phase}} ({{$_phase->libelle}})</td>
        </tr>
        <tr>
          <td style="text-align: center;">Prix en euros : {{$_phase->tarif}}</td>
        </tr>

      {{/foreach}}
      <tr>
        <th class="section">Modificateurs</th>
      </tr>
      {{foreach from=$_activite->modificateurs item=_modificateur}}
        <tr>
          <td class="text">
            <ul>
              <li>
                {{$_modificateur->code}} : {{$_modificateur->libelle}}
              </li>
            </ul>
          </td>
        </tr>
      {{/foreach}}
    {{/foreach}}
  </table>
</div>

<div id="affichage_ccam_prise_en_charge" style="display: none">
  <table class="tbl">
    {{foreach from=$code_complet->_ref_code_ccam->_ref_notes item=_note}}
      <tr>
        <th>Note :</th>
        <td>{{$_note->texte}}</td>
      </tr>
    {{/foreach}}
    {{if $code_complet->remboursement !== null}}
      <tr>
        <th>Remboursement</th>
        <td>{{tr}}CDatedCodeCCAM.remboursement.{{$code_complet->remboursement}}{{/tr}}</td>
      </tr>
    {{/if}}
    {{if $code_complet->forfait !== null}}
      <tr>
        <th>Forfait spécifique</th>
        <td>
          <span class="circled" title="{{tr}}CDatedCodeCCAM.forfait.{{$code_complet->forfait}}-desc{{/tr}}" style="color: firebrick; border-color: firebrick; cursor: help;">
            {{tr}}CDatedCodeCCAM.forfait.{{$code_complet->forfait}}{{/tr}}
          </span>
        </td>
      </tr>
    {{/if}}
    <tr>
      {{if $code_complet->procedure.code}}
    <tr>
      <th>Procédure associée</th>
      <td>

        {{$code_complet->procedure.code}} : {{$code_complet->procedure.texte}}
      </td>
    </tr>
    {{/if}}
    <th>Exonération du ticket modérateur :</th>
    {{foreach name=infotarif from=$code_complet->_ref_code_ccam->_ref_infotarif item=_ref_infotarif}}
      {{if $smarty.foreach.infotarif.first}}
        {{foreach name=first from=$_ref_infotarif->code_exo item=_code_exo}}
          {{if $smarty.foreach.first.first}}
            <td>{{$_code_exo.libelle}}</td>
          {{/if}}
        {{/foreach}}
      {{/if}}
    {{/foreach}}
    </tr>
  </table>
</div>

<div id="affichage_ccam_associations" style="display: none">
  <table class="tbl">
    <tr>
      <th class="category" colspan="2">Type d'acte : {{$code_complet->_ref_code_ccam->_type_acte}}</th>
    </tr>
    {{foreach name=first from=$code_complet->activites item=_activite key=_key}}
      {{if $_activite->assos|@count > 0}}
        {{assign var=nbAssociations value=2}}
        <tr>
          <th class="section" colspan="2">Activité {{$_key}}</th>
        </tr>
        {{foreach from=$_activite->assos item=_asso key=_key_asso}}
          {{if $nbAssociations is div by 2}}
            <tr>
          {{/if}}
          <td class="text" class="section" style="width: 50%;">
            <strong>
              <a onclick="CCodageCCAM.refreshModal('{{$_key_asso}}');" href="#">
                {{$_key_asso}}
              </a>
            </strong>
            {{$_asso.texte}}
          </td>
          {{assign var=nbAssociations value=$nbAssociations+1}}
          {{if $nbAssociations is div by 2}}
            </tr>
          {{/if}}
        {{/foreach}}
      {{else}}
        <tr>
          <td colspan="2"><p style="text-align:center;">Pas d'associations pour l'activité {{$_key}}</p></td>
        </tr>
      {{/if}}
    {{/foreach}}
  </table>
</div>

<div id="affichage_ccam_actes_voisins" style="display: none">
  <table class="tbl">
    {{foreach from=$acte_voisins item=_acte key=_key}}
      {{if $_key is div by 2}}
        <tr>
      {{/if}}
      <td class="text" style="width:50%;">
        <strong>
          <a onclick="CCodageCCAM.refreshModal('{{$_acte->code}}');" href="#">
            {{$_acte->code}}
          </a>
        </strong>
        {{$_acte->libelleLong}}
      </td>
      {{if ($_key+1) is div by 2 or ($_key+1) == $acte_voisins|@count}}
        </tr>
      {{/if}}
    {{/foreach}}
  </table>
</div>
