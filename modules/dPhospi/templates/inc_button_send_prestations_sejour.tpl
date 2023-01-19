{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=systeme_prestations_tiers value="dPhospi prestations systeme_prestations_tiers"|gconf}}

{{if $systeme_prestations_tiers == "Aucun" || !$systeme_prestations_tiers|module_active}}
  {{mb_return}}
{{/if}}

{{if $systeme_prestations_tiers == "softway" && "softway presta send_presta_immediately"|gconf}}
  {{mb_return}}
{{/if}}

{{mb_script module=hospi script=prestation ajax=1}}

{{mb_default var=type value=""}}
{{mb_default var=notext value=notext}}

<button type="button" class="prestation me-margin-2 {{$notext}}" onclick="Prestation.sendPresta('{{$_sejour->_id}}')" title="{{tr}}CPrestationJournaliere-button_send_presta{{/tr}}">
  {{tr}}CPrestationJournaliere-button_send_presta{{/tr}}
</button>
