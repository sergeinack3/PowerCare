{{*
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  highlightMessage = function(form) {
    return Url.update(form, "highlighted");
  };
  
  {{if $message}}
    Main.add(function(){
      highlightMessage(getForm("hpr-input-form"));
    });
  {{/if}}
</script>

<div>
  <form name="hpr-input-form" action="?m=hprim21&a=ajax_display_hprim_message" onsubmit="return highlightMessage(this)" method="post" class="prepared">
    <pre style="padding: 0; max-height: none;"><textarea name="message" rows="12" style="width: 100%; border: none; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; margin: 0; resize: vertical;">{{$message}}</textarea></pre>
    <button class="change me-primary">Valider</button>
  </form>
</div>

<div id="highlighted" class="me-margin-top-8"></div>
