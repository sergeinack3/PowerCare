{{*
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="text">
  {{$msgSystem|smarty:nodefaults}}

  {{if $demandeSynchro}}
    <button class="tick" onclick="synchrovalid('{{$object->repas_id}}');">Oui</button>
    <button class="cancel" onclick="synchroRefused('{{$object->affectation_id}}','{{$object->typerepas_id}}');">Non</button>
  {{/if}}

  {{if $callBack}}
    <script type='text/javascript'>
      {{$callBack}}({{$idValue}});
    </script>
  {{/if}}
</div>