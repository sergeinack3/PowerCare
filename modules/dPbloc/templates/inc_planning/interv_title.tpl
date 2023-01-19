{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=_extra value=$_extra|intval}}
{{assign var=_duree value=$_duree|intval}}
{{assign var=_by_prat value=$_by_prat|intval}}
{{assign var=_materiel value=$_materiel|intval}}
{{assign var=_examens_perop value=$_examens_perop|intval}}
{{assign var=colspan_interv value=$_extra+$_duree+$_by_prat+$_examens_perop+4}}
{{if !$_compact}}
    {{assign var=colspan_interv value=$colspan_interv+$_materiel+$_examens_perop}}
{{/if}}
<th class="title" colspan="{{$colspan_interv}}">{{tr}}COperation{{/tr}}</th>
