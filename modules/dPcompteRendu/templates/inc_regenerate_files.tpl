{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  {{if $keep_going}}
    var input = $("start_regenerate");
    input.value = parseInt(input.value) + {{$count}};
    if ($("auto_regenerate").checked) {
      regenerateFiles();
    }
  {{/if}}
</script>

{{foreach from=$messages key=cr_id item=_message}}
  <div class="info">
    {{$cr_id}} : {{$_message}}
  </div>
{{/foreach}}