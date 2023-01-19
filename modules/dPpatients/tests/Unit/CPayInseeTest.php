<?php

namespace Ox\Mediboard\Patients\Tests\Unit;

use Ox\Mediboard\Patients\CPaysInsee;
use Ox\Tests\OxUnitTestCase;

class CPayInseeTest extends OxUnitTestCase
{
    /** @var int */
    public static $code_num = 004;

    /** @var string */
    public static $code_alpha2 = "AF";

    /** @var string */
    public static $code_alpha3 = "AFG";

    /** @var string */
    public static $nom_fr = "Afghanistan";

    /** @var string */
    public static $pays = "Portugal";
    
    public static $num_pays = "99139";

    public function test__construct(): void
    {
        $pays_insee = new CPaysInsee();
        $this->assertInstanceOf(CPaysInsee::class, $pays_insee);
    }

    public function testGetAlpha2(): void
    {
        $nom_pays = CPaysInsee::getAlpha2(self::$code_num);
        $this->assertEquals(self::$code_alpha2, $nom_pays);
    }

    public function testGetAlpha3(): void
    {
        $nom_pays = CPaysInsee::getAlpha3(self::$code_num);
        $this->assertEquals(self::$code_alpha3, $nom_pays);
    }

    public function testGetPaysByNumerique(): void
    {
        $pays = CPaysInsee::getPaysByNumerique(self::$code_num);
        $this->assertEquals(self::$code_alpha3, $pays->alpha_3);
    }

    public function testGetPaysByAlpha(): void
    {
        $pays = CPaysInsee::getPaysByAlpha(self::$code_alpha3);
        $this->assertEquals($pays->numerique, self::$code_num);
    }

    public function testGetNomFR(): void
    {
        $nom_fr = CPaysInsee::getNomFR(self::$code_num);
        $this->assertEquals(self::$nom_fr, $nom_fr);
    }

    public function testMatch(): void
    {
        $pays = (new CPaysInsee())->match(self::$num_pays, 1);
        $this->assertEquals(self::$pays, $pays[CPaysInsee::NUMERIC_PORTUGAL]->nom_fr);
    }
}
