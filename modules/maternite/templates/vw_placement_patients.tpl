{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$services|@count}}
  <div class="small-info">
    {{tr}}CService.none_obstetrique{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{mb_script module=maternite  script=naissance}}
{{mb_script module=maternite  script=placement}}
{{mb_script module=cabinet    script=edit_consultation}}
{{mb_script module=planningOp script=sejour}}
{{mb_script module=patients   script=patient}}
{{mb_script module=hospi      script=affectation  ajax=1}}

<script>
  Main.add(function () {
    Placement.tabs_placement = Control.Tabs.create("tabs_placement", true, {
      afterChange:
        function (container) {
          Placement.refreshPlacement(container.id);
        }
    });

    Placement.refreshNonPlaces();
  });
</script>

<form name="changeDate" method="get">
  <input type="hidden" name="date" value="{{$dnow}}" />
</form>

<form name="save_room_op" method="post">
  <input type="hidden" name="m" value="dPplanningOp" />
  <input type="hidden" name="dosql" value="do_planning_aed" />
  <input type="hidden" name="salle_id" value="" />
  <input type="hidden" name="operation_id" value="" />
  <input type="hidden" name="callback" value="Placement.mapAffectation" />
</form>

<table class="main">
  <tr>
    <td>
      <div>
        <table id="patients_non_places" style="min-width: 150px;"></table>
      </div>
    </td>
    <td>
      <ul id="tabs_placement" class="control_tabs">
        {{* Services obstétricaux *}}
        {{foreach from=$services item=_service}}
          <li>
            <a href="#{{$_service->_guid}}">
              {{$_service}}
            </a>
          </li>
        {{/foreach}}

        {{* Blocs opératoire obstétricaux *}}
        {{foreach from=$blocs item=_bloc}}
          <li>
            <a href="#{{$_bloc->_guid}}">
              {{$_bloc}}
            </a>
          </li>
        {{/foreach}}
        <li style="text-align: center; font-size: 1.2em;" class="me-justify-content-center">
          <strong>
            {{$dnow|date_format:$conf.longdate}}
          </strong>
        </li>
        <li class="me-tabs-buttons">
          <button type="button" class="consultation_create me-primary"
                  onclick="Placement.pecPatiente();">
            {{tr}}CConsultation-prendre_en_charge{{/tr}}
          </button>

          {{if "dPurgences"|module_active}}
            <button type="button" class="consultation_create me-secondary"
                    onclick="Placement.pecPatienteUrgences();">
              {{tr}}CConsultation-prendre_en_charge_urgences{{/tr}}
            </button>
          {{/if}}
      </ul>

      {{foreach from=$services item=_service}}
        <div id="{{$_service->_guid}}" class="vue_topologique tab-container" style="display: none;"></div>
      {{/foreach}}

      {{foreach from=$blocs item=_bloc}}
        <div id="{{$_bloc->_guid}}" class="vue_topologique tab-container" style="display: none;"></div>
      {{/foreach}}
    </td>
  </tr>
</table>
