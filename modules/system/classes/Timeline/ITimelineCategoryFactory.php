<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Timeline;

/**
 * ITimelineCategoryFactory interface
 */
interface ITimelineCategoryFactory {
  /**
   * Create a category
   *
   * @param string $category - class name
   *
   * @return ITimelineCategory
   */
  public function makeCategory(string $category): ITimelineCategory;
}