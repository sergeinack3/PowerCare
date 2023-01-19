{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Thumb.nb_thumbs = {{$_nb_pages}};
  Thumb.file_id = {{$file_id}};

  // Activation des boutons des imprimantes dans la modale d'impression serveur
  // Suppression du loading, et message de génération pdf terminée.
  {{if $print}}
    $$(".printer").each(function(button) {
      button.disabled = "";
    });
    var divState = $("state");
    if (divState) {
      divState.removeClassName("loading");
      divState.innerHTML = "{{tr}}CCompteRendu.generated_pdf{{/tr}}";
    }
  {{/if}}
</script>

{{if $app->user_prefs.pdf_and_thumbs}}
  {{$_nb_pages}} page{{if $_nb_pages > 1}}s{{/if}}
  {{foreach from=1|range:$_nb_pages item=index}}
    <p style="margin-bottom: 10px;">
      <!--  Firefox refuse le min-width et min-height pour une image avec un src vide.  -->
      <img id="thumb_{{$index}}" class="thumb_empty thumbnail"
        src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP4zwAAAgEBAKEeXHUAAAAASUVORK5CYII="
        style="margin-bottom: 0; min-width: 250px; max-width: 250px; min-height: 300px; max-height: 300px; color: #fff; cursor: pointer;"
        onclick="return false;" />
      <br/>
      {{$index}}
    </p>
  {{/foreach}}
{{/if}}
