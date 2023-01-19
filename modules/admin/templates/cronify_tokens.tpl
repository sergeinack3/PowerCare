{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    textarea = $('cron-lines');
    textarea.focus();
    textarea.select();
  })
</script>
<textarea id="cron-lines" rows="12" cols="80" style="font-family: monospace;">
{{foreach from=$tokens item=_token}}
# {{$_token->_view}}
    * * * * * sh {{$conf.root_dir}}/shell/token_request.sh {{$_token->hash}}
{{/foreach}}
</textarea>