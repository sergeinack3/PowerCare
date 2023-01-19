{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $dossier->niveau_alerte_cesar || $dossier->rques_conduite_a_tenir || $dossier->conduite_a_tenir_acc}}
  <div class="me-float-right me-w33
              {{if $dossier->niveau_alerte_cesar == 1}}
                small-info" style="background-color: lightgreen"
                  {{elseif $dossier->niveau_alerte_cesar == 2}}
                    small-warning"
                  {{elseif $dossier->niveau_alerte_cesar == 3}}
                    small-error"
                  {{else}}
                    small-info"
                  {{/if}}
  >
    {{if $dossier->conduite_a_tenir_acc}}
        {{mb_value object=$dossier field=conduite_a_tenir_acc}}
      <br/>
    {{/if}}
    {{if $dossier->niveau_alerte_cesar}}
        {{mb_value object=$dossier field=niveau_alerte_cesar}}
    {{/if}}
    {{if $dossier->rques_conduite_a_tenir}}
        {{if !$dossier->niveau_alerte_cesar && !$dossier->conduite_a_tenir_acc}}{{tr}}CDossierPerinat-rques_conduite_a_tenir{{/tr}} : {{else}}<br />{{/if}}<span title="{{$dossier->rques_conduite_a_tenir}}">{{$dossier->rques_conduite_a_tenir|truncate:60:"..."}}</span>
    {{/if}}
  </div>
{{/if}}
