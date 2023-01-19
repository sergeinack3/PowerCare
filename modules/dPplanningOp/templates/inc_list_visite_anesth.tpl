{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <td class="text {{if $_operation->annulee}} cancelled{{/if}}" style="text-align: left;">
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_chir}}
  </td>
  <td class="text {{if $_operation->annulee}} cancelled{{/if}}" style="text-align: left;">
    <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_ref_sejour->_ref_patient->_guid}}')">
      {{$_operation->_ref_sejour->_ref_patient}}
    </span>
  </td>
  <td style="text-align: center;">
    {{assign var=constantes value=$_operation->_ref_sejour->_ref_patient->_ref_constantes_medicales}}
    {{if $constantes->poids}} {{$constantes->poids}}kg{{/if}}
    <br />
    {{if $constantes->taille}} {{$constantes->taille}}cm{{/if}}
  </td>
  <td class="text {{if $_operation->annulee}} cancelled {{/if}}" style="text-align: left;">
    <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}')">
      {{if $_operation->libelle}}
        {{$_operation->libelle}}
      {{else}}
        {{foreach from=$_operation->_ext_codes_ccam item=curr_code}}
          {{$curr_code->code}}
        {{/foreach}}
      {{/if}}
      ({{mb_label object=$_operation field=cote}} {{mb_value object=$_operation field=cote}})
    </span>
  </td>
  <td class="button {{if $_operation->annulee}}cancelled{{/if}}" style="text-align: center;">
    {{$_operation->time_operation|date_format:$conf.time}}
  </td>
  <td class="button {{if $_operation->annulee}}cancelled{{/if}}" style="text-align: center;">
    {{if $_operation->_ref_affectation->lit_id}}
      {{$_operation->_ref_affectation->_ref_lit}}
    {{else}}
      {{$_operation->_ref_affectation->_ref_service}}
    {{/if}}
  </td>
  {{assign var=dossier_anesth value=$_operation->_ref_consult_anesth}}
  {{assign var=consult_anesth value=$dossier_anesth->_ref_consultation}}

  <td class="{{if $_operation->annulee}}cancelled{{/if}}" style="text-align: center;">
    {{if $dossier_anesth->_id}}
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$consult_anesth->_ref_chir initials=border}}
      <a class="action" href="?m=cabinet&tab=edit_consultation&selConsult={{$consult_anesth->_id}}">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$dossier_anesth->_guid}}')">
        le {{mb_value object=$consult_anesth field="_date"}} ({{mb_value object=$consult_anesth field="_etat"}})
        </span>
      </a>
    {{else}}
      <div class="empty">Non effectuée</div>
    {{/if}}
  </td>

  <td style="text-align: center;">
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_anesth initials=border}}
  </td>

  <td class="{{if $_operation->annulee}}cancelled{{/if}}" style="text-align: center;">
    {{$_operation->_ref_type_anesth}}
  </td>

  <td class="{{if $_operation->annulee}}cancelled{{/if}}" style="text-align: center;">
    {{if $_operation->ASA}}
      <strong>{{$_operation->ASA[0]}}</strong>
    {{else}}
      -
    {{/if}}
  </td>

  {{assign var=hide_visite value='dPsalleOp COperation hide_visite_pre_anesth'|gconf}}
  <td class="{{if $_operation->annulee}}cancelled{{/if}} {{if !$_operation->date_visite_anesth || ($_operation->urgence && $hide_visite)}}empty{{/if}}" style="line-height: 20px;">
    {{if !$_operation->urgence || ($_operation->urgence && !$hide_visite)}}
      {{if $_operation->date_visite_anesth}}
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_anesth_visite initials=border}}
        le {{$_operation->date_visite_anesth|date_format:$conf.date}}
        {{if "dPsalleOp COperation use_time_vpa"|gconf && $_operation->time_visite_anesth}}
          à {{$_operation->time_visite_anesth|date_format:$conf.time}}
        {{/if}}
      {{else}}
        Non effectuée
      {{/if}}
    {{else}}
      Pas nécéssaire
    {{/if}}
  </td>
  <td>
    {{if !$_operation->urgence || ($_operation->urgence && !$hide_visite)}}
    <button type="button" class="edit notext me-secondary" onclick="editVisite({{$_operation->_id}});" style="float: right;">{{tr}}Edit{{/tr}}</button>
    {{/if}}
  </td>
  <td {{if $_operation->annulee}}class="cancelled"{{/if}}>
    <button type="button" class="injection me-tertiary" onclick="Operation.dossierBloc('{{$_operation->_id}}', function() { reloadLineVisiteAnesth('{{$_operation->_guid}}'); })">Bloc</button>
    <button type="button" class="soins me-tertiary" onclick="Operation.showDossierSoins('{{$_operation->sejour_id}}', 'suivi_clinique', function() { reloadLineVisiteAnesth('{{$_operation->_guid}}'); });">Soins
    </button>
    {{if $_operation->_ref_consult_anesth->_id}}
    <button type="button" class="print notext me-tertiary me-dark" onclick="printFicheAnesth('{{$_operation->_ref_consult_anesth->_id}}');">{{tr}}Print{{/tr}}</button>
    {{/if}}
  </td>
  <td>
    {{if $_operation->_count_lines_post_op}}
      {{mb_include module=system template=inc_bulb img_ampoule="ampoule_blue"
      title="`$_operation->_count_lines_post_op` ligne(s) de prescription post-opératoires" }}
    {{/if}}
  </td>
</tr>