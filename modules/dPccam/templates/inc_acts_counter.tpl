{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function() {
    {{if $count == 0}}
      $('count_{{$type}}_{{$subject_guid}}').up().addClassName('empty');
    {{else}}
      $('count_{{$type}}_{{$subject_guid}}').up().removeClassName('empty');
    {{/if}}
  });
</script>

({{$count}})