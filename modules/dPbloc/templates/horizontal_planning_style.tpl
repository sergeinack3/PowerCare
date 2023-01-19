{{*
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  table.horizontal_planning,
  table.salles_header {
    box-sizing: border-box;
    border: none;
    border-collapse: collapse;
    border-spacing: 0px;
    margin: 0;
  }

  table.salles_header {
    display: inline-block;
  }

  div#planning_header {
    display: inline-block;
    vertical-align: top;
  }

  div#timeline_container {
    overflow-x: auto;
    display: inline-block;
    position: relative;
  }

  table.horizontal_planning th,
  table.salles_header th {
    box-sizing: border-box;
    text-align: center;
    vertical-align: middle;
    font-weight: bold;
  }

  table.salles_header th.placeholder {
    width: 100px;
    height: 40px;
    border-bottom: slategrey solid 1px;
  }

  table.salles_header th.salle {
    position: relative;
    width: 100px;
    height: 85px;
    border-bottom: slategrey solid 1px;
  }

  table.salles_header th.salle span.action {
    display: none;
  }

  table.salles_header th.salle.onhover span.action {
    display: inline;
    position: absolute;
    top: 5px;
    left: 5px;
  }

  table.horizontal_planning th.hour {
    box-sizing: border-box;
    height: 20px;
    border-right: slategrey solid 1px;
    text-align: left;
  }

  table.horizontal_planning th.hour:nth-child(1) {
    border-left: slategrey solid 1px;
  }

  table.horizontal_planning td.hour_first_half {
    box-sizing: border-box;
    height: 85px;
    border-top: slategrey solid 1px;
    border-left: slategrey solid 1px;
  }

  table.horizontal_planning td.hour_second_half {
    box-sizing: border-box;
    height: 80px;
    border-top: slategrey solid 1px;
    border-left: darkgrey dashed 1px;
  }

  div.current_time {
    box-sizing : border-box;
    width: 1px;
    position: absolute;
    border-left: firebrick solid 1px;
    height: 100%;
    z-index: 60;
  }

  div.operation {
    box-sizing: border-box;
    position: absolute;
    border: #454f5f solid 1px;
    height: 75px;
    overflow-x: hidden;
    overflow-y: hidden;
    padding: 2px;
    border-radius: 5px;
    z-index: 50;
    white-space: nowrap;
    text-overflow: clip;
  }

  span.induction_time {
    position: absolute;
    height: 75px;
    top: 0px;
    background-color: #1e69cd;
    z-index: -1;
  }

  div.operation.has_preop {
    border-top-left-radius: 0px;
    border-bottom-left-radius: 0px;
  }

  div.operation.has_postop {
    border-top-right-radius: 0px;
    border-bottom-right-radius: 0px;
  }

  div.operation.not_started {
    background-color: #dddddd;
  }

  div.operation.ended {
    background: repeating-linear-gradient(
      45deg,
      #cccccc,
      #cccccc 10px,
      #ffffff 10px,
      #ffffff 20px
    );
  }

  div.operation.late_ended {
    background: repeating-linear-gradient(
      45deg,
      #a81613,
      #a81613 10px,
      #ffffff 10px,
      #ffffff 20px
    );
  }

  div.operation.pending {
    background-color: #cd5c1f;
  }

  div.operation.late {
    background-color: #a81613;
  }

  div.operation.moved {
    border: #60140c dashed 1px;
  }

  div.operation:hover {
    overflow: visible;
  }

  div.operation div.operation_actions {
    position: absolute;
    top: -24px;
    height: 24px;
    background: #fff;
    border: 1px solid #AAA;
    border-radius: 3px;
    display: none;
  }

  div.operation:hover div.operation_actions {
    display: block;
  }

  .touchscreen span.texticon {
    width: 30px;
    height: 15px;
    text-align: center;
    vertical-align: middle;
  }

  div.operation.onhover {
    overflow-x: visible;
    z-index: 100;
    box-shadow: 1px 1px 5px black, -1px -1px 5px black;
  }

  .touchscreen div.operation.onhover {
    height: 95px;
  }

  /* Use of the clip-path property to hide the left or right (or both) shadows depending on the preop/postop */
  div.operation.has_preop.onhover {
    clip-path: inset(-29px -5px -5px 0px)
  }

  div.operation.has_postop.onhover {
    clip-path: inset(-29px 0px -5px -5px)
  }

  div.operation.has_preop.has_postop.onhover {
    clip-path: inset(-29px 0px -5px 0px)
  }

  div.operation.undersized.onhover {
    width: 450px!important;
  }

  div.preop {
    box-sizing: border-box;
    position: absolute;
    border-radius: 5px 0 0 5px;
    box-sizing: border-box;
    background-color: #74cd67;
    border-top: slategrey solid 1px;
    border-bottom: slategrey solid 1px;
    border-left: slategrey solid 1px;
    height: 75px;
    z-index: 0;
  }

  div.preop.onhover {
    box-shadow: 1px 1px 5px black, -1px -1px 5px black;
    z-index: 100;
  }

  .touchscreen div.preop.onhover {
    height: 95px;
  }

  div.postop {
    box-sizing: border-box;
    position: absolute;
    border-top: slategrey solid 1px;
    border-bottom: slategrey solid 1px;
    border-right: slategrey solid 1px;
    border-radius: 0 5px 5px 0;
    background-color: #74cd67;
    height: 75px;
    margin: 0px;
    z-index: 0;
  }

  div.postop.onhover {
    box-shadow: 1px 1px 5px black, 1px -1px 5px black;
    z-index: 100;
  }

  .touchscreen div.postop.onhover {
    height: 95px;
  }

  div.blocage {
    text-align: center;
    box-sizing: border-box;
    position: absolute;
    border: #2c3341 solid 1px;
    border-radius: 5px;
    height: 75px;
    z-index: 30;
    background: repeating-linear-gradient(
      45deg,
      #871119,
      #871119 10px,
      #626669 10px,
      #626669 20px
    );
    padding: 5px;
  }

  div.blocage span.view {
    border: #2c3341 solid 1px;
    border-radius: 5px;
    padding: 3px;
    background-color: #a9bdc3;
    cursor: pointer;
    display: none;
  }

  div.blocage:hover span.view {
    display: inline;
  }
</style>