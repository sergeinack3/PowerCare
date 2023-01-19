{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  App.readonly = true;

  Main.add(function() {
    //$$('.not-printable').invoke('hide');

    setTimeout(function () {window.print()}, 4000);
  });
</script>

<button type="button" class="not-printable print" onclick="window.print();" style="float: right;">
  {{tr}}Print{{/tr}}
</button>

{{foreach from=$parts_to_print item=_part}}
  <div>
    {{$_part|smarty:nodefaults}}
  </div>
{{/foreach}}
