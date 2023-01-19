{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$fiches_anesth item=_fiche_anesth name=fiches_anesths}}
  {{$_fiche_anesth|smarty:nodefaults}}

  {{if !$smarty.foreach.fiches_anesths.last}}
    <hr style="border: 0; page-break-after: always;" />
  {{/if}}
{{/foreach}}