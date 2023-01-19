{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="hover_chir">
  {{if $plage->chir_id}}
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$plage->_ref_chir}}
  {{else}}
    {{mb_include module=mediusers template=inc_vw_function function=$plage->_ref_spec}}
  {{/if}}
</div>
<div class="plageop_header">
  {{mb_include module=system template=inc_object_notes object=$plage float='right'}}
  {{if $plage->_unordered_operations|@count}}
    <span style="float: right;">
      {{$plage->_unordered_operations|@count}} opération(s) non planifiée(s)
    </span>
  {{/if}}

  {{if $plage->chir_id}}
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$plage->_ref_chir}}
  {{else}}
    {{mb_include module=mediusers template=inc_vw_function function=$plage->_ref_spec}}
  {{/if}}

  <br/>
  <img src='images/icons/anesth.png'/>
  {{if $plage->_ref_anesth->_id}}
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$plage->_ref_anesth}}
  {{else}}
    <span class="empty" style="color: #5f5f5f;">Aucun anesthésiste sélectionné</span>
  {{/if}}
</div>