{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    {{if !$type || $module_rpps}}
      Control.Tabs.create('medecin-search-tabs');
    {{/if}}

    {{if $module_rpps && $praticiens}}
      Control.Tabs.setTabCount('result-search-professionnal_health', {{$total.rpps}});
    {{/if}}

    {{if $medecins}}
      Control.Tabs.setTabCount('result-search-medecin-exact', {{$total.exact}});
    {{/if}}

    {{if $medecins_close}}
      Control.Tabs.setTabCount('result-search-medecin-close', {{$total.close}});
    {{/if}}
  });
</script>

{{assign var=module_rpps value="rpps"|module_active}}

{{if !$type}}
  <ul class="control_tabs" id="medecin-search-tabs">
      {{if $module_rpps}}
        <li>
          <a href="#result-search-professionnal_health">
              {{tr}}mediusers-Health directory{{/tr}}
          </a>
        </li>
      {{/if}}

      {{if $function_id || $group_id}}
        <li><a href="#result-search-medecin-exact">{{tr}}mediusers-Search medecin result exact{{/tr}}</a></li>
        <li><a href="#result-search-medecin-close">{{tr}}mediusers-Search medecin result close{{/tr}}</a></li>
      {{/if}}
  </ul>
{{/if}}

{{if $module_rpps}}
  <div id="result-search-professionnal_health">
      {{mb_include template=inc_list_medecin_personne_exercice total=$total_personne_exercice page=$page praticiens=$praticiens}}
  </div>
{{/if}}

{{if $function_id || $group_id}}
    {{if !$type || $medecins}}
      <div id="result-search-medecin-exact" {{if !$type}}style="display: none;"{{/if}}>
          {{mb_include template=inc_list_medecin total=$total.exact page=$page medecins=$medecins type='exact'}}
      </div>
    {{/if}}

    {{if !$type || $medecins_close}}
      <div id="result-search-medecin-close" {{if !$type}}style="display: none;"{{/if}}>
          {{mb_include template=inc_list_medecin total=$total.close page=$page medecins=$medecins_close type='close'}}
      </div>
    {{/if}}
{{/if}}
