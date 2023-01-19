{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=codes_affectation ajax=$ajax}}

<script>
  Main.add(function () {
    CodesAffectation.changePage(0);
  });
</script>

<table class="tbl main">
  <thead>
  <tr>
    <th>{{tr}}CFunctions{{/tr}}</th>
    <th>{{tr}}CCodeAffectation{{/tr}}</th>
    <th class="narrow"></th>
  </tr>
  </thead>
  <tbody id="codes"></tbody>
</table>

