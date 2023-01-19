{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$sejour->_veille}}
  {{assign var=rpu value=$sejour->_ref_rpu}}
  {{mb_include template=inc_icone_attente}}
  {{if $sejour->sortie_reelle}}
    <br />
    {{if $sejour->mode_sortie != "normal"}}
      ({{mb_value object=$sejour field=mode_sortie}}
    {{else}}
      (sortie
    {{/if}}
    à {{$sejour->sortie_reelle|date_format:$conf.time}})
  {{else}}
  {{mb_value object=$rpu field=_attente}}
  {{/if}}

{{else}}
  Admis la veille
{{/if}}
