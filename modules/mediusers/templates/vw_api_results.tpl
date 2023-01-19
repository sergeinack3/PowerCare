{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Requête</h2>
<strong style="font-size: 12px;">{{$url}}</strong>

{{if $post}}
  <h3>POST</h3>
  <code>{{$post|@print_r}}</code>
{{/if}}

<h2>Résultat</h2>
<code>{{$response|smarty:nodefaults}}</code>
