{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">
  {{mb_value object=$sejour field=_motif_complet}}
</span>

{{if $sejour->_jour_op}}
  {{assign var=nb_days_hide_op value="soins dossier_soins nb_days_hide_op"|gconf}}
  {{foreach from=$sejour->_jour_op item=_info_jour_op}}
    {{if $nb_days_hide_op == 0 || $nb_days_hide_op > $_info_jour_op.jour_op}}
      {{assign var=anesth value=$_info_jour_op.operation->_ref_anesth}}
      <br />
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_info_jour_op.operation_guid}}');">
        (J{{$_info_jour_op.jour_op}})
        {{$_info_jour_op.operation->libelle}}
      </span>
      {{if $anesth->_id}}
        (<span onmouseover="ObjectTooltip.createEx(this, '{{$anesth->_guid}}')">{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$anesth initials=border}}</span>)
      {{/if}}
    {{/if}}
  {{/foreach}}
{{/if}}