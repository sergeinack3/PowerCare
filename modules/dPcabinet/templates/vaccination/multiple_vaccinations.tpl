{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=vaccination ajax=$ajax}}

<script>
  Main.add(function () {
    Vaccination.refreshMultipleVaccinations(
      {{$types|@json_encode}},
      {{$patient->_id}},
      {{if $repeat}}{{$repeat}}{{else}}null{{/if}}{{$repeat}},
      {{if $recall_age}}{{$recall_age}}{{else}}null{{/if}}
    );
  });
</script>

<style>
  .flex-vaccinations {
    display: flex;
  }

  #other_injections, #new_edit_vaccine {
    flex: 1;
    margin: 10px;
  }
</style>

<div class="flex-vaccinations">
  {{* Injections list *}}
  <div id="other_injections">

  </div>
</div>
