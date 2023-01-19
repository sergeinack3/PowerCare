<?php

/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ftp;

interface CustomRequestAnalyserInterface
{
    /**
     * Get Request analyser
     *
     * @return RequestAnalyserInterface
     */
    public function getRequestAnalyser(): RequestAnalyserInterface;
}
