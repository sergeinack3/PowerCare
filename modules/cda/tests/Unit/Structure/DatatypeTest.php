<?php

/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Tests\Unit;

use Exception;
use Ox\Core\CMbDT;
use Ox\Interop\Cda\Datatypes\Base\CCDAEIVL_event;
use Ox\Interop\Cda\Datatypes\Base\CCDAINT;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVXB_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAMO;
use Ox\Interop\Cda\Datatypes\Base\CCDAPQ;
use Ox\Interop\Cda\Datatypes\Base\CCDAREAL;
use Ox\Interop\Cda\Datatypes\Base\CCDASXCM_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDATS;
use Ox\Interop\Cda\Datatypes\Datatype\CCDABXIT_CD;
use Ox\Interop\Cda\Datatypes\Datatype\CCDABXIT_IVL_PQ;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAEIVL_PPD_TS;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAEIVL_TS;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAGLIST_PQ;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAGLIST_TS;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAHXIT_CE;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAHXIT_PQ;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVL_INT;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVL_MO;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVL_PPD_PQ;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVL_PPD_TS;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVL_PQ;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVL_REAL;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVXB_INT;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVXB_MO;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVXB_PPD_PQ;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVXB_PPD_TS;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVXB_PQ;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVXB_REAL;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAlist_int;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAPIVL_PPD_TS;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAPIVL_TS;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAPPD_PQ;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAPPD_TS;
use Ox\Interop\Cda\Datatypes\Datatype\CCDASXCM_CD;
use Ox\Interop\Cda\Datatypes\Datatype\CCDASXCM_INT;
use Ox\Interop\Cda\Datatypes\Datatype\CCDASXCM_MO;
use Ox\Interop\Cda\Datatypes\Datatype\CCDASXCM_PPD_PQ;
use Ox\Interop\Cda\Datatypes\Datatype\CCDASXCM_PPD_TS;
use Ox\Interop\Cda\Datatypes\Datatype\CCDASXCM_PQ;
use Ox\Interop\Cda\Datatypes\Datatype\CCDASXCM_REAL;
use Ox\Interop\Cda\Datatypes\Datatype\CCDASXPR_TS;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAUVP_TS;
use Ox\Tests\OxUnitTestCase;

class DatatypeTest extends OxUnitTestCase
{
    public function testCCDABXITCD(): void
    {
        $CCDABXIT_CD = new CCDABXIT_CD();

        // Test avec une quantité incorrecte
        $CCDABXIT_CD->setQty("10.25");
        $this->assertFalse($CCDABXIT_CD->validate());

        // Test avec une quantité correcte
        $CCDABXIT_CD->setQty("10");
        $this->assertTrue($CCDABXIT_CD->validate());
    }

    public function testCCDABXITIVLPQ(): void
    {
        $CCDABXIT_IVL_PQ = new CCDABXIT_IVL_PQ();

        // Test avec une quantité incorrecte
        $CCDABXIT_IVL_PQ->setQty("10.25");
        $this->assertFalse($CCDABXIT_IVL_PQ->validate());

        // Test avec une quantité correcte
        $CCDABXIT_IVL_PQ->setQty("10");
        $this->assertTrue($CCDABXIT_IVL_PQ->validate());
    }

    public function testCCDAEIVLPPDTS(): void
    {
        $CCDAEIVL_PPD_TS = new CCDAEIVL_PPD_TS();

        // Test avec les valeurs null
        $this->assertTrue($CCDAEIVL_PPD_TS->validate());

        // Test avec un event incorrect
        $ivl = new CCDAEIVL_event();
        $ivl->setCode("TEST");
        $CCDAEIVL_PPD_TS->setEvent($ivl);
        $this->assertFalse($CCDAEIVL_PPD_TS->validate());

        // Test avec un event correct
        $ivl->setCode("AC");
        $CCDAEIVL_PPD_TS->setEvent($ivl);
        $this->assertTrue($CCDAEIVL_PPD_TS->validate());

        // Test avec un offset incorrect
        $ivl = new CCDAIVL_PPD_PQ();
        $pq  = new CCDAIVXB_PPD_PQ();
        $pq->setInclusive("TESTTEST");
        $ivl->setLow($pq);
        $CCDAEIVL_PPD_TS->setOffset($ivl);
        $this->assertFalse($CCDAEIVL_PPD_TS->validate());

        // Test avec un offset correct
        $pq->setInclusive("true");
        $ivl->setLow($pq);
        $CCDAEIVL_PPD_TS->setOffset($ivl);
        $this->assertTrue($CCDAEIVL_PPD_TS->validate());
    }

    public function testCCDAEIVLTS(): void
    {
        $CCDAEIVL_TS = new CCDAEIVL_TS();

        // Test avec les valeurs null
        $this->assertTrue($CCDAEIVL_TS->validate());

        // Test avec un event incorrect
        $ivl = new CCDAEIVL_event();
        $ivl->setCode("TEST");
        $CCDAEIVL_TS->setEvent($ivl);
        $this->assertFalse($CCDAEIVL_TS->validate());

        // Test avec un event correct
        $ivl->setCode("AC");
        $CCDAEIVL_TS->setEvent($ivl);
        $this->assertTrue($CCDAEIVL_TS->validate());

        // Test avec un offset incorrect
        $ivl = new CCDAIVL_PQ();
        $pq  = new CCDAIVXB_PQ();
        $pq->setInclusive("TESTTEST");
        $ivl->setLow($pq);
        $CCDAEIVL_TS->setOffset($ivl);
        $this->assertFalse($CCDAEIVL_TS->validate());

        // Test avec un offset correct
        $pq->setInclusive("true");
        $ivl->setLow($pq);
        $CCDAEIVL_TS->setOffset($ivl);
        $this->assertTrue($CCDAEIVL_TS->validate());
    }

    public function testCCDAGLISTPQ(): void
    {
        $CCDAGLIST_PQ = new CCDAGLIST_PQ();

        // Test avec les valeurs null
        $this->assertFalse($CCDAGLIST_PQ->validate());

        // Test avec une head correct, séquence incorrecte
        $hea = new CCDAPQ();
        $hea->setUnit("test");
        $CCDAGLIST_PQ->setHead($hea);
        $this->assertFalse($CCDAGLIST_PQ->validate());

        // Test avec un increment correct, séquence correcte
        $inc = new CCDAPQ();
        $inc->setUnit("test");
        $CCDAGLIST_PQ->setIncrement($inc);
        $this->assertTrue($CCDAGLIST_PQ->validate());

        // Test avec une period incorrecte
        $CCDAGLIST_PQ->setPeriod("10.25");
        $this->assertFalse($CCDAGLIST_PQ->validate());

        // Test avec une period correcte
        $CCDAGLIST_PQ->setPeriod("10");
        $this->assertTrue($CCDAGLIST_PQ->validate());

        // Test avec un denominator incorrect
        $CCDAGLIST_PQ->setDenominator("10.25");
        $this->assertFalse($CCDAGLIST_PQ->validate());

        // Test avec un denominator correct
        $CCDAGLIST_PQ->setDenominator("10");
        $this->assertTrue($CCDAGLIST_PQ->validate());
    }

    public function testCCDAGLISTTS(): void
    {
        $CCDAGLIST_TS = new CCDAGLIST_TS();

        // Test avec les valeurs null
        $head = new CCDATS();
        $head->setValue(CMbDT::dateTime());
        $CCDAGLIST_TS->setHead($head);
        $this->assertFalse($CCDAGLIST_TS->validate());

        // Test avec un increment correct, séquence correcte
        $inc = new CCDAPQ();
        $inc->setUnit("test");
        $CCDAGLIST_TS->setIncrement($inc);
        $this->assertTrue($CCDAGLIST_TS->validate());

        // Test avec une period incorrecte
        $CCDAGLIST_TS->setPeriod("10.25");
        $this->assertFalse($CCDAGLIST_TS->validate());

        // Test avec une period correcte
        $CCDAGLIST_TS->setPeriod("10");
        $this->assertTrue($CCDAGLIST_TS->validate());

        // Test avec un denominator incorrect
        $CCDAGLIST_TS->setDenominator("10.25");
        $this->assertFalse($CCDAGLIST_TS->validate());

        // Test avec un denominator correct
        $CCDAGLIST_TS->setDenominator("10");
        $this->assertTrue($CCDAGLIST_TS->validate());

        // Test avec une head correcte, séquence incorrecte
        $hea = new CCDATS();
        $this->expectException(Exception::class);
        $hea->setValue("75679245900741.869627871786625715081550660290154484483335306381809807748522068");
    }

    public function testCCDAHXITCE(): void
    {
        $CCDAHXIT_CE = new CCDAHXIT_CE();

        // Test avec un validTime incorrecte
        $ivl  = new CCDAIVL_TS();
        $ivbx = new CCDAIVXB_TS();
        $ivbx->setInclusive("TESTTESt");
        $ivl->setLow($ivbx);
        $CCDAHXIT_CE->setValidTime($ivl);
        $this->assertFalse($CCDAHXIT_CE->validate());

        // Test avec un validTime correcte
        $ivbx->setInclusive("true");
        $ivl->setLow($ivbx);
        $CCDAHXIT_CE->setValidTime($ivl);
        $this->assertTrue($CCDAHXIT_CE->validate());
    }

    public function testCCDAHXITPQ(): void
    {
        $CCDAHXIT_PQ = new CCDAHXIT_PQ();

        // Test avec un validTime incorrecte
        $ivl  = new CCDAIVL_TS();
        $ivbx = new CCDAIVXB_TS();
        $ivbx->setInclusive("TESTTEST");
        $ivl->setLow($ivbx);
        $CCDAHXIT_PQ->setValidTime($ivl);
        $this->assertFalse($CCDAHXIT_PQ->validate());

        // Test avec un validTime correcte
        $ivbx->setInclusive("true");
        $ivl->setLow($ivbx);
        $CCDAHXIT_PQ->setValidTime($ivl);
        $this->assertTrue($CCDAHXIT_PQ->validate());
    }

    public function testCCDAIVLINT(): void
    {
        $CCDAIVL_INT = new CCDAIVL_INT();

        // Test avec un low incorrect
        $xbts = new CCDAIVXB_INT();
        $xbts->setInclusive("TESTTEST");
        $CCDAIVL_INT->setLow($xbts);
        $this->assertFalse($CCDAIVL_INT->validate());

        // Test avec un low correct
        $xbts->setInclusive("true");
        $CCDAIVL_INT->setLow($xbts);
        $this->assertTrue($CCDAIVL_INT->validate());

        // Test avec un high incorrect
        $hi = new CCDAIVXB_INT();
        $hi->setInclusive("TESTTEST");
        $CCDAIVL_INT->setHigh($hi);
        $this->assertFalse($CCDAIVL_INT->validate());

        // Test avec un high correct
        $hi->setInclusive("true");
        $CCDAIVL_INT->setHigh($hi);
        $this->assertTrue($CCDAIVL_INT->validate());

        // Test avec un width incorrect
        $wid = new CCDAINT();
        $wid->setValue("10.25");
        $CCDAIVL_INT->setWidth($wid);
        $this->assertFalse($CCDAIVL_INT->validate());

        // Test avec un width correct
        $wid->setValue("10");
        $CCDAIVL_INT->high = null;
        $CCDAIVL_INT->setWidth($wid);
        $this->assertTrue($CCDAIVL_INT->validate());

        // Test avec un high incorrect
        $CCDAIVL_INT->setOrder(null);
        $CCDAIVL_INT->low    = null;
        $CCDAIVL_INT->width  = null;
        $CCDAIVL_INT->center = null;
        $hi                  = new CCDAIVXB_INT();
        $hi->setInclusive("TESTTEST");
        $CCDAIVL_INT->setHigh($hi);
        $this->assertFalse($CCDAIVL_INT->validate());

        // Test avec un high correct
        $hi->setInclusive("true");
        $CCDAIVL_INT->setHigh($hi);
        $this->assertTrue($CCDAIVL_INT->validate());

        // Test avec un width incorrect
        $CCDAIVL_INT->high = null;
        $CCDAIVL_INT->setOrder(null);
        $wid = new CCDAINT();
        $wid->setValue("10.25");
        $CCDAIVL_INT->setWidth($wid);
        $this->assertFalse($CCDAIVL_INT->validate());

        // Test avec un width correct
        $wid->setValue("10");
        $CCDAIVL_INT->setWidth($wid);
        $this->assertTrue($CCDAIVL_INT->validate());

        // Test avec un high incorrect
        $hi2 = new CCDAIVXB_INT();
        $hi2->setInclusive("TESTTEST");
        $CCDAIVL_INT->setHigh($hi2);
        $this->assertFalse($CCDAIVL_INT->validate());

        // Test avec un high correct
        $hi2->setInclusive("true");
        $CCDAIVL_INT->setHigh($hi2);
        $this->assertTrue($CCDAIVL_INT->validate());

        // Test avec un center incorrect
        $CCDAIVL_INT->setOrder(null);
        $CCDAIVL_INT->width = null;
        $CCDAIVL_INT->high  = null;
        $cen                = new CCDAINT();
        $cen->setValue("10.25");
        $CCDAIVL_INT->setCenter($cen);
        $this->assertFalse($CCDAIVL_INT->validate());

        // Test avec un center correct
        $cen->setValue("10");
        $CCDAIVL_INT->setCenter($cen);
        $this->assertTrue($CCDAIVL_INT->validate());

        // Test avec un width incorrect
        $cenW = new CCDAINT();
        $cenW->setValue("10.25");
        $CCDAIVL_INT->setCenter($cenW);
        $this->assertFalse($CCDAIVL_INT->validate());

        // Test avec un width correct
        $cenW->setValue("10");
        $CCDAIVL_INT->setCenter($cenW);
        $this->assertTrue($CCDAIVL_INT->validate());
    }

    public function testCCDAIVLMO(): void
    {
        $CCDAIVL_MO = new CCDAIVL_MO();

        // Test avec un operator incorrect
        $CCDAIVL_MO->setOperator("TESTTEST");
        $this->assertFalse($CCDAIVL_MO->validate());

        // Test avec un operator correct
        $CCDAIVL_MO->setOperator("I");
        $this->assertTrue($CCDAIVL_MO->validate());

        // Test avec un low incorrect
        $xbts = new CCDAIVXB_MO();
        $xbts->setInclusive("TESTTEST");
        $CCDAIVL_MO->setLow($xbts);
        $this->assertFalse($CCDAIVL_MO->validate());

        // Test avec un low correcte
        $xbts->setInclusive("true");
        $CCDAIVL_MO->setLow($xbts);
        $this->assertTrue($CCDAIVL_MO->validate());

        // Test avec un high incorrect
        $hi = new CCDAIVXB_MO();
        $hi->setInclusive("TESTTEST");
        $CCDAIVL_MO->setHigh($hi);
        $this->assertFalse($CCDAIVL_MO->validate());

        // Test avec un high correcte
        $hi->setInclusive("true");
        $CCDAIVL_MO->setHigh($hi);
        $this->assertTrue($CCDAIVL_MO->validate());

        // Test avec un width incorrect, séquence incorrecte
        $wid = new CCDAMO();
        $wid->setValue("test");
        $CCDAIVL_MO->setWidth($wid);
        $this->assertFalse($CCDAIVL_MO->validate());

        // Test avec un width correcte, séquence incorrecte
        $wid->setValue("10.25");
        $CCDAIVL_MO->high = null;
        $CCDAIVL_MO->setWidth($wid);
        $this->assertTrue($CCDAIVL_MO->validate());

        // Test avec un high incorrect
        $CCDAIVL_MO->setOrder(null);
        $CCDAIVL_MO->low    = null;
        $CCDAIVL_MO->width  = null;
        $CCDAIVL_MO->center = null;
        $hi                 = new CCDAIVXB_MO();
        $hi->setInclusive("TESTTEST");
        $CCDAIVL_MO->setHigh($hi);
        $this->assertFalse($CCDAIVL_MO->validate());

        // Test avec un high correcte
        $hi->setInclusive("true");
        $CCDAIVL_MO->setHigh($hi);
        $this->assertTrue($CCDAIVL_MO->validate());

        // Test avec un width incorrect
        $CCDAIVL_MO->high = null;
        $CCDAIVL_MO->setOrder(null);
        $wid = new CCDAMO();
        $wid->setValue("test");
        $CCDAIVL_MO->setWidth($wid);
        $this->assertFalse($CCDAIVL_MO->validate());

        // Test avec un width correcte
        $wid->setValue("10.25");
        $CCDAIVL_MO->setWidth($wid);
        $this->assertTrue($CCDAIVL_MO->validate());

        // Test avec un high incorrect
        $hi2 = new CCDAIVXB_MO();
        $hi2->setInclusive("TESTTEST");
        $CCDAIVL_MO->setHigh($hi2);
        $this->assertFalse($CCDAIVL_MO->validate());

        // Test avec un high correcte
        $hi2->setInclusive("true");
        $CCDAIVL_MO->setHigh($hi2);
        $this->assertTrue($CCDAIVL_MO->validate());

        // Test avec un center incorrect
        $CCDAIVL_MO->setOrder(null);
        $CCDAIVL_MO->width = null;
        $CCDAIVL_MO->high  = null;
        $cen               = new CCDAMO();
        $cen->setValue("test");
        $CCDAIVL_MO->setCenter($cen);
        $this->assertFalse($CCDAIVL_MO->validate());

        // Test avec un center correct
        $cen->setValue("10.25");
        $CCDAIVL_MO->setCenter($cen);
        $this->assertTrue($CCDAIVL_MO->validate());

        // Test avec un width incorrect
        $cenW = new CCDAMO();
        $cenW->setValue("test");
        $CCDAIVL_MO->setCenter($cenW);
        $this->assertFalse($CCDAIVL_MO->validate());

        // Test avec un width correct
        $cenW->setValue("10.25");
        $CCDAIVL_MO->setCenter($cenW);
        $this->assertTrue($CCDAIVL_MO->validate());
    }

    public function testCCDAIVLPPDPQ(): void
    {
        $CCDAIVL_PPD_PQ = new CCDAIVL_PPD_PQ();

        // Test avec un low incorrect
        $xbts = new CCDAIVXB_PPD_PQ();
        $xbts->setValue("TESTTEST");
        $CCDAIVL_PPD_PQ->setLow($xbts);
        $this->assertFalse($CCDAIVL_PPD_PQ->validate());

        // Test avec un low correct
        $xbts->setValue("75679245900741.869627871786625715081550660290154484483335306381809807748522068");
        $xbts->setInclusive("true");
        $CCDAIVL_PPD_PQ->setLow($xbts);
        $this->assertTrue($CCDAIVL_PPD_PQ->validate());

        // Test avec un high incorrect
        $hi = new CCDAIVXB_PPD_PQ();
        $hi->setValue("TESTTEST");
        $CCDAIVL_PPD_PQ->setHigh($hi);
        $this->assertFalse($CCDAIVL_PPD_PQ->validate());

        // Test avec un high correct
        $hi->setValue("75679245900741.869627871786625715081550660290154484483335306381809807748522068");
        $CCDAIVL_PPD_PQ->setHigh($hi);
        $this->assertTrue($CCDAIVL_PPD_PQ->validate());

        // Test avec un width incorrect, séquence incorrecte
        $pq = new CCDAPPD_PQ();
        $pq->setValue("test");
        $CCDAIVL_PPD_PQ->setWidth($pq);
        $this->assertFalse($CCDAIVL_PPD_PQ->validate());

        // Test avec un width correcte, séquence incorrecte
        $pq->setValue("10.25");
        $CCDAIVL_PPD_PQ->high = null;
        $CCDAIVL_PPD_PQ->setWidth($pq);
        $this->assertTrue($CCDAIVL_PPD_PQ->validate());

        // Test avec un high incorrect
        $CCDAIVL_PPD_PQ->setOrder(null);
        $CCDAIVL_PPD_PQ->low    = null;
        $CCDAIVL_PPD_PQ->width  = null;
        $CCDAIVL_PPD_PQ->center = null;
        $hi                     = new CCDAIVXB_PPD_PQ();
        $hi->setValue("TESTTEST");
        $CCDAIVL_PPD_PQ->setHigh($hi);
        $this->assertFalse($CCDAIVL_PPD_PQ->validate());

        // Test avec un high correcte
        $hi->setValue("75679245900741.869627871786625715081550660290154484483335306381809807748522068");
        $CCDAIVL_PPD_PQ->setHigh($hi);
        $this->assertTrue($CCDAIVL_PPD_PQ->validate());

        // Test avec un width incorrect
        $CCDAIVL_PPD_PQ->high = null;
        $CCDAIVL_PPD_PQ->setOrder(null);
        $pq = new CCDAPPD_PQ();
        $pq->setValue("test");
        $CCDAIVL_PPD_PQ->setWidth($pq);
        $this->assertFalse($CCDAIVL_PPD_PQ->validate());

        // Test avec un width correct
        $pq->setValue("10.25");
        $CCDAIVL_PPD_PQ->setWidth($pq);
        $this->assertTrue($CCDAIVL_PPD_PQ->validate());

        // Test avec un high incorrect
        $hi = new CCDAIVXB_PPD_PQ();
        $hi->setValue("TESTTEST");
        $CCDAIVL_PPD_PQ->setHigh($hi);
        $this->assertFalse($CCDAIVL_PPD_PQ->validate());

        // Test avec un high correct
        $hi->setValue("75679245900741.869627871786625715081550660290154484483335306381809807748522068");
        $CCDAIVL_PPD_PQ->setHigh($hi);
        $this->assertTrue($CCDAIVL_PPD_PQ->validate());

        // Test avec un center incorrect
        $CCDAIVL_PPD_PQ->setOrder(null);
        $CCDAIVL_PPD_PQ->width = null;
        $CCDAIVL_PPD_PQ->high  = null;
        $pq                    = new CCDAPPD_PQ();
        $pq->setDistributionType("TESTTEST");
        $CCDAIVL_PPD_PQ->setCenter($pq);
        $this->assertFalse($CCDAIVL_PPD_PQ->validate());

        // Test avec un center correcte
        $pq->setDistributionType("F");
        $CCDAIVL_PPD_PQ->setCenter($pq);
        $this->assertTrue($CCDAIVL_PPD_PQ->validate());

        // Test avec un width incorrecte
        $pq = new CCDAPPD_PQ();
        $pq->setValue("test");
        $CCDAIVL_PPD_PQ->setWidth($pq);
        $this->assertFalse($CCDAIVL_PPD_PQ->validate());

        // Test avec un width correct
        $pq->setValue("10.25");
        $CCDAIVL_PPD_PQ->setWidth($pq);
        $this->assertTrue($CCDAIVL_PPD_PQ->validate());
    }

    public function testCCDAIVLPPDTS(): void
    {
        $CCDAIVL_PPD_TS = new CCDAIVL_PPD_TS();

        // Test avec un low correct
        $xbts = new CCDAIVXB_PPD_TS();
        $xbts->setValue(CMbDT::dateTime());
        $xbts->setInclusive("true");
        $CCDAIVL_PPD_TS->setLow($xbts);
        $this->assertTrue($CCDAIVL_PPD_TS->validate());

        // Test avec un high correct
        $hi = new CCDAIVXB_PPD_TS();
        $hi->setValue(CMbDT::dateTime());
        $CCDAIVL_PPD_TS->setHigh($hi);
        $this->assertTrue($CCDAIVL_PPD_TS->validate());

        // Test avec un width incorrecte, séquence incorrect
        $pq = new CCDAPPD_PQ();
        $pq->setValue("test");
        $CCDAIVL_PPD_TS->setWidth($pq);
        $this->assertFalse($CCDAIVL_PPD_TS->validate());

        // Test avec un width correcte, séquence incorrect
        $CCDAIVL_PPD_TS->high = null;
        $pq->setValue("10.25");
        $CCDAIVL_PPD_TS->setWidth($pq);
        $this->assertTrue($CCDAIVL_PPD_TS->validate());

        // Test avec un high correct
        $CCDAIVL_PPD_TS->setOrder(null);
        $CCDAIVL_PPD_TS->low    = null;
        $CCDAIVL_PPD_TS->width  = null;
        $CCDAIVL_PPD_TS->center = null;
        $hi                     = new CCDAIVXB_PPD_TS();
        $hi->setValue(CMbDT::dateTime());
        $CCDAIVL_PPD_TS->setHigh($hi);
        $this->assertTrue($CCDAIVL_PPD_TS->validate());

        // Test avec un width incorrect
        $CCDAIVL_PPD_TS->high = null;
        $CCDAIVL_PPD_TS->setOrder(null);
        $pq = new CCDAPPD_PQ();
        $pq->setValue("test");
        $CCDAIVL_PPD_TS->setWidth($pq);
        $this->assertFalse($CCDAIVL_PPD_TS->validate());

        // Test avec un width correct
        $pq->setValue("10.25");
        $CCDAIVL_PPD_TS->setWidth($pq);
        $this->assertTrue($CCDAIVL_PPD_TS->validate());

        // Test avec un high correct
        $hi = new CCDAIVXB_PPD_TS();
        $hi->setValue(CMbDT::dateTime());
        $CCDAIVL_PPD_TS->setHigh($hi);
        $this->assertTrue($CCDAIVL_PPD_TS->validate());

        // Test avec un center correct
        $CCDAIVL_PPD_TS->setOrder(null);
        $CCDAIVL_PPD_TS->width = null;
        $CCDAIVL_PPD_TS->high  = null;
        $ts                    = new CCDAPPD_TS();
        $ts->setValue(CMbDT::dateTime());
        $CCDAIVL_PPD_TS->setCenter($ts);
        $this->assertTrue($CCDAIVL_PPD_TS->validate());

        // Test avec un width incorrect
        $pq = new CCDAPPD_PQ();
        $pq->setValue("test");
        $CCDAIVL_PPD_TS->setWidth($pq);
        $this->assertFalse($CCDAIVL_PPD_TS->validate());

        // Test avec un width correct
        $pq->setValue("10.25");
        $CCDAIVL_PPD_TS->setWidth($pq);
        $this->assertTrue($CCDAIVL_PPD_TS->validate());
    }

    public function testCCDAIVLPQ(): void
    {
        $CCDAIVL_PQ = new CCDAIVL_PQ();

        // Test avec un low incorrect
        $xbts = new CCDAIVXB_PQ();
        $xbts->setInclusive("TESTTEST");
        $CCDAIVL_PQ->setLow($xbts);
        $this->assertFalse($CCDAIVL_PQ->validate());

        // Test avec un low correct
        $xbts->setInclusive("true");
        $CCDAIVL_PQ->setLow($xbts);
        $this->assertTrue($CCDAIVL_PQ->validate());

        // Test avec un high incorrect
        $hi = new CCDAIVXB_PQ();
        $hi->setInclusive("TESTTEST");
        $CCDAIVL_PQ->setHigh($hi);
        $this->assertFalse($CCDAIVL_PQ->validate());

        // Test avec un high correct
        $hi->setInclusive("true");
        $CCDAIVL_PQ->setHigh($hi);
        $this->assertTrue($CCDAIVL_PQ->validate());

        // Test avec un width incorrect, séquence incorrecte
        $pq = new CCDAPQ();
        $pq->setUnit(" ");
        $CCDAIVL_PQ->setWidth($pq);
        $this->assertFalse($CCDAIVL_PQ->validate());

        // Test avec un width correct, séquence incorrecte
        $CCDAIVL_PQ->high = null;
        $pq->setUnit("10.25");
        $CCDAIVL_PQ->setWidth($pq);
        $this->assertTrue($CCDAIVL_PQ->validate());

        // Test avec un high incorrect
        $CCDAIVL_PQ->setOrder(null);
        $CCDAIVL_PQ->low    = null;
        $CCDAIVL_PQ->width  = null;
        $CCDAIVL_PQ->center = null;
        $hi                 = new CCDAIVXB_PQ();
        $hi->setInclusive("TESTTEST");
        $CCDAIVL_PQ->setHigh($hi);
        $this->assertFalse($CCDAIVL_PQ->validate());

        // Test avec un high correcte
        $hi->setInclusive("true");
        $CCDAIVL_PQ->setHigh($hi);
        $this->assertTrue($CCDAIVL_PQ->validate());

        // Test avec un width incorrect
        $CCDAIVL_PQ->high = null;
        $CCDAIVL_PQ->setOrder(null);
        $pq = new CCDAPQ();
        $pq->setUnit(" ");
        $CCDAIVL_PQ->setWidth($pq);
        $this->assertFalse($CCDAIVL_PQ->validate());

        // Test avec un width correct
        $pq->setUnit("test");
        $CCDAIVL_PQ->setWidth($pq);
        $this->assertTrue($CCDAIVL_PQ->validate());

        // Test avec un high incorrect
        $hi = new CCDAIVXB_PQ();
        $hi->setInclusive("TESTTEST");
        $CCDAIVL_PQ->setHigh($hi);
        $this->assertFalse($CCDAIVL_PQ->validate());

        // Test avec un high correct
        $hi->setInclusive("true");
        $CCDAIVL_PQ->setHigh($hi);
        $this->assertTrue($CCDAIVL_PQ->validate());

        // Test avec un center incorrect
        $CCDAIVL_PQ->setOrder(null);
        $CCDAIVL_PQ->width = null;
        $CCDAIVL_PQ->high  = null;
        $pq                = new CCDAPQ();
        $pq->setUnit(" ");
        $CCDAIVL_PQ->setCenter($pq);
        $this->assertFalse($CCDAIVL_PQ->validate());

        // Test avec un center correct
        $pq->setUnit("test");
        $CCDAIVL_PQ->setCenter($pq);
        $this->assertTrue($CCDAIVL_PQ->validate());

        // Test avec un width incorrect
        $pq = new CCDAPQ();
        $pq->setUnit(" ");
        $CCDAIVL_PQ->setWidth($pq);
        $this->assertFalse($CCDAIVL_PQ->validate());

        // Test avec un width correct
        $pq->setUnit("test");
        $CCDAIVL_PQ->setWidth($pq);
        $this->assertTrue($CCDAIVL_PQ->validate());
    }

    public function testCCDAIVLREAL(): void
    {
        $CCDAIVL_REAL = new CCDAIVL_REAL();

        // Test avec un low incorrecte
        $xbts = new CCDAIVXB_REAL();
        $xbts->setInclusive("TESTTEST");
        $CCDAIVL_REAL->setLow($xbts);
        $this->assertFalse($CCDAIVL_REAL->validate());

        // Test avec un low correct
        $xbts->setInclusive("true");
        $CCDAIVL_REAL->setLow($xbts);
        $this->assertTrue($CCDAIVL_REAL->validate());

        // Test avec un high incorrect
        $hi = new CCDAIVXB_REAL();
        $hi->setInclusive("TESTTEST");
        $CCDAIVL_REAL->setHigh($hi);
        $this->assertFalse($CCDAIVL_REAL->validate());

        // Test avec un high correct
        $hi->setInclusive("true");
        $CCDAIVL_REAL->setHigh($hi);
        $this->assertTrue($CCDAIVL_REAL->validate());

        // Test avec un width incorrect, séquence incorrecte
        $wid = new CCDAREAL();
        $wid->setValue("test");
        $CCDAIVL_REAL->setWidth($wid);
        $this->assertFalse($CCDAIVL_REAL->validate());

        // Test avec un width correct, séquence incorrecte
        $CCDAIVL_REAL->high = null;
        $wid->setValue("10.25");
        $CCDAIVL_REAL->setWidth($wid);
        $this->assertTrue($CCDAIVL_REAL->validate());

        // Test avec un high incorrect
        $CCDAIVL_REAL->setOrder(null);
        $CCDAIVL_REAL->low    = null;
        $CCDAIVL_REAL->width  = null;
        $CCDAIVL_REAL->center = null;
        $hi                   = new CCDAIVXB_REAL();
        $hi->setInclusive("TESTTEST");
        $CCDAIVL_REAL->setHigh($hi);
        $this->assertFalse($CCDAIVL_REAL->validate());

        // Test avec un high correct
        $hi->setInclusive("true");
        $CCDAIVL_REAL->setHigh($hi);
        $this->assertTrue($CCDAIVL_REAL->validate());

        // Test avec un width incorrect
        $CCDAIVL_REAL->high = null;
        $CCDAIVL_REAL->setOrder(null);
        $wid = new CCDAREAL();
        $wid->setValue("test");
        $CCDAIVL_REAL->setWidth($wid);
        $this->assertFalse($CCDAIVL_REAL->validate());

        // Test avec un width correct
        $wid->setValue("10.25");
        $CCDAIVL_REAL->setWidth($wid);
        $this->assertTrue($CCDAIVL_REAL->validate());

        // Test avec un high incorrect
        $hi2 = new CCDAIVXB_REAL();
        $hi2->setInclusive("TESTTEST");
        $CCDAIVL_REAL->setHigh($hi2);
        $this->assertFalse($CCDAIVL_REAL->validate());

        // Test avec un high correct
        $hi2->setInclusive("true");
        $CCDAIVL_REAL->setHigh($hi2);
        $this->assertTrue($CCDAIVL_REAL->validate());

        // Test avec un center incorrect
        $CCDAIVL_REAL->setOrder(null);
        $CCDAIVL_REAL->width = null;
        $CCDAIVL_REAL->high  = null;
        $cen                 = new CCDAREAL();
        $cen->setValue("test");
        $CCDAIVL_REAL->setCenter($cen);
        $this->assertFalse($CCDAIVL_REAL->validate());

        // Test avec un center correct
        $cen->setValue("10.25");
        $CCDAIVL_REAL->setCenter($cen);
        $this->assertTrue($CCDAIVL_REAL->validate());

        // Test avec un width incorrect
        $cenW = new CCDAREAL();
        $cenW->setValue("test");
        $CCDAIVL_REAL->setCenter($cenW);
        $this->assertFalse($CCDAIVL_REAL->validate());

        // Test avec un width correct
        $cenW->setValue("10.25");
        $CCDAIVL_REAL->setCenter($cenW);
        $this->assertTrue($CCDAIVL_REAL->validate());
    }

    public function testCCDAIVXBINT(): void
    {
        $CCDAIVL_REAL = new CCDAIVXB_INT();

        // Test avec un inclusive incorrect
        $CCDAIVL_REAL->setInclusive("TESTTEST");
        $this->assertFalse($CCDAIVL_REAL->validate());

        // Test avec un inclusive correct
        $CCDAIVL_REAL->setInclusive("true");
        $this->assertTrue($CCDAIVL_REAL->validate());
    }

    public function testCCDAIVXBMO(): void
    {
        $CCDAIVXB_MO = new CCDAIVXB_MO();

        // Test avec un inclusive incorrect
        $CCDAIVXB_MO->setInclusive("TESTTEST");
        $this->assertFalse($CCDAIVXB_MO->validate());

        // Test avec un inclusive correct
        $CCDAIVXB_MO->setInclusive("true");
        $this->assertTrue($CCDAIVXB_MO->validate());
    }

    public function testCCDAIVXBPPDPQ(): void
    {
        $CCDAIVXB_PPD_PQ = new CCDAIVXB_PPD_PQ();

        // Test avec un inclusive incorrect
        $CCDAIVXB_PPD_PQ->setInclusive("TESTTEST");
        $this->assertFalse($CCDAIVXB_PPD_PQ->validate());

        // Test avec un inclusive correct
        $CCDAIVXB_PPD_PQ->setInclusive("true");
        $this->assertTrue($CCDAIVXB_PPD_PQ->validate());
    }

    public function testCCDAIVXBPPDTS(): void
    {
        $CCDAIVXB_PPD_TS = new CCDAIVXB_PPD_TS();

        // Test avec un inclusive incorrect
        $CCDAIVXB_PPD_TS->setInclusive("TESTTEST");
        $this->assertFalse($CCDAIVXB_PPD_TS->validate());

        // Test avec un inclusive correct
        $CCDAIVXB_PPD_TS->setInclusive("true");
        $this->assertTrue($CCDAIVXB_PPD_TS->validate());
    }

    public function testCCDAIVXBPQ(): void
    {
        $CCDAIVXB_PQ = new CCDAIVXB_PQ();

        // Test avec un inclusive incorrect
        $CCDAIVXB_PQ->setInclusive("TESTTEST");
        $this->assertFalse($CCDAIVXB_PQ->validate());

        // Test avec un inclusive correct
        $CCDAIVXB_PQ->setInclusive("true");
        $this->assertTrue($CCDAIVXB_PQ->validate());
    }

    public function testCCDAIVXBREAL(): void
    {
        $CCDAIVXB_REAL = new CCDAIVXB_REAL();

        // Test avec un inclusive incorrect
        $CCDAIVXB_REAL->setInclusive("TESTTEST");
        $this->assertFalse($CCDAIVXB_REAL->validate());

        // Test avec un inclusive correct
        $CCDAIVXB_REAL->setInclusive("true");
        $this->assertTrue($CCDAIVXB_REAL->validate());
    }

    public function testCCDAlistint(): void
    {
        $CCDAlist_int = new CCDAlist_int();

        // Test avec un int incorrecte
        $CCDAlist_int->addData("10.25");
        $this->assertFalse($CCDAlist_int->validate());

        // Test avec un int correcte
        $CCDAlist_int->resetListData();
        $CCDAlist_int->addData("10");
        $this->assertTrue($CCDAlist_int->validate());

        // Test avec deux int correcte
        $CCDAlist_int->addData("11");
        $this->assertTrue($CCDAlist_int->validate());
    }

    public function testCCDAPIVLPPDTS(): void
    {
        $CCDAPIVL_PPD_TS = new CCDAPIVL_PPD_TS();

        // Test avec les valeurs null
        $this->assertTrue($CCDAPIVL_PPD_TS->validate());

        // Test avec un alignment incorrecte
        $CCDAPIVL_PPD_TS->setAlignment(" ");
        $this->assertFalse($CCDAPIVL_PPD_TS->validate());

        // Test avec un alignment correcte
        $CCDAPIVL_PPD_TS->setAlignment("CD");
        $this->assertTrue($CCDAPIVL_PPD_TS->validate());

        // Test avec un institutionSpecified incorrecte
        $CCDAPIVL_PPD_TS->setInstitutionSpecified("CD");
        $this->assertFalse($CCDAPIVL_PPD_TS->validate());

        // Test avec un institutionSpecified correcte
        $CCDAPIVL_PPD_TS->setInstitutionSpecified("true");
        $this->assertTrue($CCDAPIVL_PPD_TS->validate());

        // Test avec une phase correct
        $pq = new CCDAPPD_PQ();
        $pq->setDistributionType("TESTTEST");
        $CCDAPIVL_PPD_TS->setPeriod($pq);
        $this->assertFalse($CCDAPIVL_PPD_TS->validate());

        // Test avec une period correcte
        $pq->setDistributionType("F");
        $CCDAPIVL_PPD_TS->setPeriod($pq);
        $this->assertTrue($CCDAPIVL_PPD_TS->validate());
    }

    public function testCCDAPIVLTS(): void
    {
        $CCDAPIVL_TS = new CCDAPIVL_TS();

        // Test avec les valeurs null, Document valide
        $this->assertTrue($CCDAPIVL_TS->validate());

        // Test avec un alignment incorrecte
        $CCDAPIVL_TS->setAlignment(" ");
        $this->assertFalse($CCDAPIVL_TS->validate());

        // Test avec un alignment correcte
        $CCDAPIVL_TS->setAlignment("CD");
        $this->assertTrue($CCDAPIVL_TS->validate());

        // Test avec un institutionSpecified incorrecte
        $CCDAPIVL_TS->setInstitutionSpecified("CD");
        $this->assertFalse($CCDAPIVL_TS->validate());

        // Test avec un institutionSpecified correcte
        $CCDAPIVL_TS->setInstitutionSpecified("true");
        $this->assertTrue($CCDAPIVL_TS->validate());

        // Test avec une phase incorrecte
        $ivl  = new CCDAIVL_TS();
        $xbts = new CCDAIVXB_TS();
        $xbts->setValue(CMbDT::dateTime());
        $ivl->setLow($xbts);
        $CCDAPIVL_TS->setPhase($ivl);
        $this->assertTrue($CCDAPIVL_TS->validate());

        // Test avec une period correcte
        $pq = new CCDAPQ();
        $pq->setUnit("TEST");
        $CCDAPIVL_TS->setPeriod($pq);
        $this->assertTrue($CCDAPIVL_TS->validate());

        // Test avec une period incorrecte
        $pq->setUnit(" ");
        $CCDAPIVL_TS->setPeriod($pq);
        $this->assertFalse($CCDAPIVL_TS->validate());
    }

    public function testCCDAPPDPQ(): void
    {
        $CCDAPPD_PQ = new CCDAPPD_PQ();

        // Test avec un distributionType incorrecte
        $CCDAPPD_PQ->setDistributionType("TESTTEST");
        $this->assertFalse($CCDAPPD_PQ->validate());

        // Test avec un distributionType correcte
        $CCDAPPD_PQ->setDistributionType("F");
        $this->assertTrue($CCDAPPD_PQ->validate());

        // Test avec un standardDeviation incorrecte
        $pq = new CCDAPQ();
        $pq->setValue("test");
        $CCDAPPD_PQ->setStandardDeviation($pq);
        $this->assertFalse($CCDAPPD_PQ->validate());

        // Test avec un standardDeviation correcte
        $pq->setValue("10.25");
        $CCDAPPD_PQ->setStandardDeviation($pq);
        $this->assertTrue($CCDAPPD_PQ->validate());
    }

    public function testCCDAPPDTS(): void
    {
        $CCDAPPD_TS = new CCDAPPD_TS();

        // Test avec un distributionType incorrect
        $CCDAPPD_TS->setDistributionType("TESTTEST");
        $this->assertFalse($CCDAPPD_TS->validate());

        // Test avec un distributionType correct
        $CCDAPPD_TS->setDistributionType("F");
        $this->assertTrue($CCDAPPD_TS->validate());

        // Test avec un standardDeviation incorrect
        $pq = new CCDAPQ();
        $pq->setValue("test");
        $CCDAPPD_TS->setStandardDeviation($pq);
        $this->assertFalse($CCDAPPD_TS->validate());

        // Test avec un standardDeviation correct
        $pq->setValue("10.25");
        $CCDAPPD_TS->setStandardDeviation($pq);
        $this->assertTrue($CCDAPPD_TS->validate());
    }

    public function testCCDASXCMCD(): void
    {
        $CCDASXCM_CD = new CCDASXCM_CD();

        // Test avec un operator incorrect
        $CCDASXCM_CD->setOperator("TESTTEST");
        $this->assertFalse($CCDASXCM_CD->validate());

        // Test avec un operator correct
        $CCDASXCM_CD->setOperator("I");
        $this->assertTrue($CCDASXCM_CD->validate());
    }

    public function testCCDASXCMINT(): void
    {
        $CCDASXCM_INT = new CCDASXCM_INT();

        // Test avec un operator incorrect
        $CCDASXCM_INT->setOperator("TESTTEST");
        $this->assertFalse($CCDASXCM_INT->validate());

        // Test avec un operator correct
        $CCDASXCM_INT->setOperator("I");
        $this->assertTrue($CCDASXCM_INT->validate());
    }

    public function testCCDASXCMMO(): void
    {
        $CCDASXCM_MO = new CCDASXCM_MO();

        // Test avec un operator incorrect
        $CCDASXCM_MO->setOperator("TESTTEST");
        $this->assertFalse($CCDASXCM_MO->validate());

        // Test avec un operator correct
        $CCDASXCM_MO->setOperator("I");
        $this->assertTrue($CCDASXCM_MO->validate());
    }

    public function testCCDASXCMPPDPQ(): void
    {
        $CCDASXCM_PPD_PQ = new CCDASXCM_PPD_PQ();

        // Test avec un operator incorrect
        $CCDASXCM_PPD_PQ->setOperator("TESTTEST");
        $this->assertFalse($CCDASXCM_PPD_PQ->validate());

        // Test avec un operator correct
        $CCDASXCM_PPD_PQ->setOperator("I");
        $this->assertTrue($CCDASXCM_PPD_PQ->validate());
    }

    public function testCCDASXCMPPDTS(): void
    {
        $CCDASXCM_PPD_TS = new CCDASXCM_PPD_TS();

        // Test avec un operator incorrect
        $CCDASXCM_PPD_TS->setOperator("TESTTEST");
        $this->assertFalse($CCDASXCM_PPD_TS->validate());

        // Test avec un operator correct
        $CCDASXCM_PPD_TS->setOperator("I");
        $this->assertTrue($CCDASXCM_PPD_TS->validate());
    }

    public function testCCDASXCMPQ(): void
    {
        $CCDASXCM_PQ = new CCDASXCM_PQ();

        // Test avec un operator incorrect
        $CCDASXCM_PQ->setOperator("TESTTEST");
        $this->assertFalse($CCDASXCM_PQ->validate());

        // Test avec un operator correct
        $CCDASXCM_PQ->setOperator("I");
        $this->assertTrue($CCDASXCM_PQ->validate());
    }

    public function testCCDASXCMREAL(): void
    {
        $CCDASXCM_REAL = new CCDASXCM_REAL();

        // Test avec un operator incorrect
        $CCDASXCM_REAL->setOperator("TESTTEST");
        $this->assertFalse($CCDASXCM_REAL->validate());

        // Test avec un operator correct
        $CCDASXCM_REAL->setOperator("I");
        $this->assertTrue($CCDASXCM_REAL->validate());
    }

    public function testCCDASXPRTS(): void
    {
        $CCDASXPR_TS = new CCDASXPR_TS();

        // Test avec une comp incorrecte
        $sx = new CCDASXCM_TS();
        $sx->setOperator("TESTTEST");
        $CCDASXPR_TS->addData($sx);
        $this->assertFalse($CCDASXPR_TS->validate());

        // Test avec une comp incorrecte, minimum non atteint
        $sx->setOperator("E");
        $CCDASXPR_TS->resetListData();
        $CCDASXPR_TS->addData($sx);
        $this->assertFalse($CCDASXPR_TS->validate());

        // Test avec une comp incorrecte et une incorrecte, minimum atteint
        $sx2 = new CCDASXCM_TS();
        $sx2->setOperator("TESTTEST");
        $CCDASXPR_TS->addData($sx2);
        $this->assertFalse($CCDASXPR_TS->validate());

        // Test avec deux comp correcte, minimum atteint
        $sx2->setOperator("P");
        $CCDASXPR_TS->resetListData();
        $CCDASXPR_TS->addData($sx);
        $CCDASXPR_TS->addData($sx2);
        $this->assertTrue($CCDASXPR_TS->validate());
    }

    public function testCCDAUVPTS(): void
    {
        $CCDAUVP_TS = new CCDAUVP_TS();

        // Test avec une probability incorrecte
        $CCDAUVP_TS->setProbability("2.0");
        $this->assertFalse($CCDAUVP_TS->validate());

        // Test avec un probability correcte
        $CCDAUVP_TS->setProbability("0.80");
        $this->assertTrue($CCDAUVP_TS->validate());
    }
}
