{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  {{if $nbAlertes}}
    <tr>
      <th colspan="10" class="title">{{$nbAlertes}} alerte(s) sur des interventions</th>
    </tr>
    <tr>
      <th>Date</th>
      <th>Chirurgien</th>
      <th>Patient</th>
      <th>Salle</th>
      <th>Intervention</th>
    </tr>
    {{foreach from=$blocs item=bloc}}
      {{foreach from=$bloc->_alertes_intervs item=_alerte}}
        {{assign var="_operation" value=$_alerte->_ref_object}}
        {{mb_include module=bloc template=inc_line_alerte is_alerte=1}}
      {{/foreach}}
    {{/foreach}}
  {{/if}}

  {{if $nbNonValide}}
    <tr>
      <th>Date</th>
      <th>Chirurgien</th>
      <th>Patient</th>
      <th>Salle</th>
      <th>Intervention</th>
    </tr>
    <tr>
      <th colspan="10" class="title">{{$nbNonValide}} intervention(s) non validées</th>
    </tr>
    {{foreach from=$blocs item=bloc key=key_bloc}}
      {{foreach from=$listNonValidees.$key_bloc item=_operation}}
        {{mb_include module=bloc template=inc_line_alerte is_alerte=0}}
      {{/foreach}}
    {{/foreach}}
  {{/if}}
  {{if $nbHorsPlage}}
    <tr>
      <th colspan="10" class="title">{{$nbHorsPlage}} intervention(s) hors plage</th>
    </tr>
    <tr>
      <th>Date</th>
      <th>Chirurgien</th>
      <th>Patient</th>
      <th>Salle</th>
      <th>Intervention</th>
    </tr>
    {{foreach from=$blocs item=bloc key=key_bloc}}
      {{foreach from=$listHorsPlage.$key_bloc item=_operation}}
        {{mb_include module=bloc template=inc_line_alerte is_alerte=0 edit_mode=$edit_mode}}
      {{/foreach}}
    {{/foreach}}
  {{/if}}
</table>