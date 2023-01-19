{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul class="MessagingLinkAutocomplete">
    {{foreach from=$matches item=match}}
        <li data-id="{{$match->_id}}" class="MessagingLinkAutocomplete-content">
            <div class="MessagingLinkAutocomplete-title">
                {{$match->_view}}
            </div>
            <div class="MessagingLinkAutocomplete-infos">
                <span class="me-padding-bottom-5">
                    {{$match->naissance|date_format:$conf.date}}
                </span>
                <span>
                    {{if $match->adresse}}
                        {{$match->adresse}},
                    {{/if}}
                    {{$match->cp}}
                    {{$match->ville}}
                </span>
            </div>
        </li>
    {{/foreach}}
</ul>
