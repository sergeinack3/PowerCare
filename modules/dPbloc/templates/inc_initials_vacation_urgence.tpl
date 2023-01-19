{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if (!$plage->chir_id && !$plage->spec_id) || !"dPbloc CPlageOp original_owner"|gconf}}
  {{mb_return}}
{{/if}}

{{* Si le statut est libre / annulé ou si l'utilisateur est différent du propriétaire, les initiales sont barrées *}}
<span
  {{if (in_array($plage->status, array("free", "deleted")))              ||
       ($plage->chir_id && $plage->original_function_id)                 ||
       ($plage->spec_id && $plage->original_owner_id)                    ||
       ($plage->chir_id && $plage->chir_id != $plage->original_owner_id) ||
       ($plage->spec_id && $plage->spec_id != $plage->original_function_id)}}
         style="text-decoration: line-through;"
  {{/if}}>
  {{if $plage->chir_id}}
    {{if $plage->original_function_id}}
      {{mb_include module=mediusers template=inc_vw_function function=$plage->_ref_original_spec initials=border}}
    {{elseif $plage->original_owner_id && $plage->original_owner_id != $plage->chir_id}}
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$plage->_ref_original_chir initials=border}}
    {{else}}
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$plage->_ref_chir initials=border}}
    {{/if}}
  {{else}}
    {{if $plage->original_owner_id}}
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$plage->_ref_original_chir initials=border}}
    {{elseif $plage->original_function_id && $plage->original_function_id != $plage->spec_id}}
      {{mb_include module=mediusers template=inc_vw_function function=$plage->_ref_original_spec initials=border}}
    {{else}}
      {{mb_include module=mediusers template=inc_vw_function function=$plage->_ref_spec initials=border}}
    {{/if}}
  {{/if}}
</span>
