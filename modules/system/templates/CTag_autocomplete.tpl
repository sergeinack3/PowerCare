{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span style="background: #{{$match->color}}; margin: -2px; margin-right: 1px; padding: 2px;">&nbsp;</span>
<span class="view" style="padding-left: {{$match->_deepness}}em; {{if !$match->parent_id}}font-weight: bold;{{/if}}">{{$match->_view|smarty:nodefaults}}</span>