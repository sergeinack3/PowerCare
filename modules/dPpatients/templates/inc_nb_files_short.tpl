{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=nb_files value=0}}
{{mb_default var=nb_docs value=0}}


(<span style="{{if $nb_files}}color:black; font-weight: bold;{{/if}}">{{$nb_files}}</span>, <span
  style="{{if $nb_docs}}color:black; font-weight: bold;{{/if}}">{{$nb_docs}}</span>)