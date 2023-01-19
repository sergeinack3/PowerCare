{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    $("token_url").select();
  });
</script>

<div class="small-info">
  Copiez puis collez cette adresse dans votre gestionnaire d'agenda.
</div>

<input type="text" name="token_url" id="token_url" value="{{$url}}" readonly
       style="width: 99%; box-sizing: border-box; -moz-box-sizing: border-box;" />