{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=plateau_technique ajax=1}}
<script>
  Main.add(function() {
    PlateauTechnique.current_m = '{{$m}}';
  });
</script>
<table class="main">
  <tr>
    <td id="list_plateaux" style="width: 50%;">
      {{mb_include module=ssr template=inc_list_plateaux}}
    </td>
    <td id="form_plateau" style="width: 50%;">
      {{mb_include module=ssr template=inc_form_plateau}}
    </td>
  </tr>
</table>
