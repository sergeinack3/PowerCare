{{*
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $exchange->_observations}}
<div class="big-{{if ($exchange->statut_acquittement == 'erreur') || 
                     ($exchange->statut_acquittement == 'err')}}error
                {{elseif ($exchange->statut_acquittement == 'avertissement') || 
                         ($exchange->statut_acquittement == 'avt')
                }}warning
                {{else}}info{{/if}}">
  {{foreach from=$exchange->_observations item=observation}}
    <strong>Code :</strong> {{$observation.code}} <br />
    <strong>Libelle :</strong> {{$observation.libelle}} <br />
    <strong>Commentaire :</strong> {{$observation.commentaire}} <br />
  {{/foreach}}
</div>
{{/if}}