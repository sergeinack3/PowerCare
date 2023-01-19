{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=_show_numdoss_modal value=0}}
{{mb_default var=hide_empty          value=0}}

{{assign var=show_modal_identifiant value=$conf.dPplanningOp.CSejour.show_modal_identifiant}}
{{assign var=nda                    value=$nda_obj->_NDA_view}}
{{assign var=_doss_id               value=$nda_obj->_id}}

{{if !$nda || $hide_empty}}
  {{mb_return}}
{{/if}}

{{if $show_modal_identifiant && $_doss_id && $_show_numdoss_modal}}
<a href="#1" onclick="new Url('sante400', 'ajax_show_id400').addParam('id400', '{{$nda}}').addParam('object_id', '{{$_doss_id}}').requestModal(400);">
{{/if}}
  [{{$nda|default:"-"}}]
{{if $show_modal_identifiant && $_doss_id && $_show_numdoss_modal}}
</a>
{{/if}}