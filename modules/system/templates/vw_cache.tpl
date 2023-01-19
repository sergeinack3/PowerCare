{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=cache_viewer}}

<script>
  Main.add(function () {
    Control.Tabs.create('cache-tabs', true);
  });
</script>

<style>
  .tab-bargraph {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    height: 4px;
    background-size: 100% 100%;
    opacity: 0.6;
    background-color: #eee;
    /*border-top: 1px solid #ddd;*/
  }
  .tab-text {
    position: relative;
    z-index: 2;
    white-space: nowrap;
    margin-top: 3px;
  }
  .tab-text small {
    font-weight:normal;
  }

  #cache-tabs a {
    width: 80px;
  }

  a.active .tab-bargraph {
    opacity: 1.0;
  }

  a.trigger-detail {
    background: url(style/mediboard_ext/images/buttons/tree-folding.png) no-repeat right -18px;
    padding-right: 12px;
  }
  a.trigger-detail:hover {
    background-position-y: -2px;
    text-decoration: none;
  }
  a.trigger-detail.unfold {
    background-position-y: -50px;
  }
  a.trigger-detail.unfold:hover {
    background-position-y: -34px;
  }
</style>

<ul class="control_tabs" id="cache-tabs">
  <li>
    <a href="#tab-shm" style="position: relative;">
      {{mb_include module=system template=inc_cache_bargraph info=$shm_global_info}}
      <div class="tab-text">
        SHM<br/><small>{{$shm_global_info.total|decabinary}} ({{$shm_global_info.total_rate|round:1}}%)</small>
      </div>
    </a>
  </li>
  <li>
    <a href="#tab-dshm">
      <div class="tab-text">
        DSHM<br/><small>{{$dshm_global_info.entries}} / {{$dshm_global_info.instance_count}}</small>
      </div>
    </a>
  </li>
  <li>
    <a href="#tab-opcache" style="position: relative;">
      {{mb_include module=system template=inc_cache_bargraph info=$opcode_global_info}}
      <div class="tab-text">
        Opcode cache<br/><small>{{$opcode_global_info.total|decabinary}} ({{$opcode_global_info.total_rate|round:1}}%)</small>
      </div>
    </a>
  </li>
  <li>
    <a href="#tab-jscss">
      <div class="tab-text">
        JS/CSS<br/><small>{{$assets_global_info.js_total|decabinary}} / {{$assets_global_info.css_total|decabinary}}</small>
      </div>
    </a>
  </li>
</ul>

<div style="display: none;" id="tab-shm">
  {{mb_include module=system template=inc_cache_info info=$shm_global_info type="shm"}}
</div>

<div style="display: none;" id="tab-dshm">
  {{mb_include module=system template=inc_cache_info info=$dshm_global_info type="dshm"}}
</div>

<div style="display: none;" id="tab-opcache">
  {{mb_include module=system template=inc_cache_info info=$opcode_global_info type="opcode"}}
</div>

<div style="display: none;" id="tab-jscss">
  {{mb_include module=system template=inc_cache_assets_info info=$assets_global_info type="assets"}}
</div>
