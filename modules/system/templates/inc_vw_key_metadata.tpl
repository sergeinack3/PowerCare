{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<td>{{mb_value object=$metadata field=name}}</td>
<td>{{mb_value object=$metadata field=alg}}</td>
<td>{{mb_value object=$metadata field=mode}}</td>

<td>
    {{if $metadata->hasBeenPersisted()}}
        {{mb_value object=$metadata field=creation_date}}
    {{else}}
      <button type="button" class="fas fa-random me-primary" onclick="KeyMetadata.generate('{{$metadata->_id}}');">
        {{tr}}KeyBuilder-action-Generate key{{/tr}}
      </button>

      <button type="button" class="fas fa-upload me-secondary" disabled>
          {{tr}}KeyBuilder-action-Input key{{/tr}}
      </button>
    {{/if}}
</td>
