<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tests\Unit\Resolver\Identifiers;

use Ox\Interop\Eai\CDomain;
use Ox\Interop\Eai\Resolver\Identifiers\PIIdentifierResolver;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Tests\OxUnitTestCase;

class PIIdentifierResolverTest extends OxUnitTestCase
{
    private const DOMAIN_OID          = "1.1.2.3.4.500.1.40";
    private const DOMAIN_NAMESPACE_ID = "pi_identifier_domain_test";

    private static CDomain $domain;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$domain         = $domain = CDomain::getMasterDomainPatient();
        $domain->OID          = self::DOMAIN_OID;
        $domain->namespace_id = self::DOMAIN_NAMESPACE_ID;
    }

    private static function createDomain(): CDomain
    {
        return new CDomain();
    }

    public function testWithoutData(): void
    {
        $identifier_resolver = new PIIdentifierResolver();

        $this->assertNull($identifier_resolver->resolve(null, null, null));
    }

    public function testModeDowngrade(): void
    {
        $identifier          = "0001";
        $identifier_resolver = new PIIdentifierResolver();
        $identifier_resolver->setModeDowngrade();

        $this->assertEquals($identifier, $identifier_resolver->resolve($identifier, null, 'PI'));
    }

    public function testModeNamespace(): void
    {
        $identifier          = "0001";
        $identifier_resolver = (new PIIdentifierResolver())
            ->setModeNamespaceId()
            ->setDomain(self::$domain);

        $this->assertEquals($identifier, $identifier_resolver->resolve($identifier, self::DOMAIN_NAMESPACE_ID, 'PI'));
    }

    public function testModeNamespaceWithBadSystem(): void
    {
        $identifier          = "0001";
        $identifier_resolver = (new PIIdentifierResolver())
            ->setModeNamespaceId()
            ->setDomain(self::$domain);

        $this->assertNull($identifier_resolver->resolve($identifier, "bad system", 'PI'));
    }

    public function providerModeOID(): array
    {
        return [
            'correct oid'              => [self::DOMAIN_OID],
            'correct oid with urn:oid' => ["urn:oid:" . self::DOMAIN_OID],
        ];
    }


    /**
     * @param string $oid
     *
     * @dataProvider providerModeOID
     *
     * @return void
     */
    public function testModeOID(string $oid): void
    {
        $identifier          = "0001";
        $identifier_resolver = (new PIIdentifierResolver())
            ->setModeOID()
            ->setGroup(CGroups::loadCurrent())
            ->setDomain(self::$domain);

        $this->assertEquals($identifier, $identifier_resolver->resolve($identifier, $oid, 'PI'));
    }

    public function testModeOIDWithBadSystem(): void
    {
        $identifier          = "0001";
        $identifier_resolver = (new PIIdentifierResolver())
            ->setModeOID()
            ->setDomain(self::$domain);

        $this->assertNull($identifier_resolver->resolve($identifier, "1.1.2.5.63.55.2", 'PI'));
    }

    public function testWithBadControlType(): void
    {
        $identifier          = "0001";
        $identifier_resolver = (new PIIdentifierResolver())
            ->setModeNamespaceId()
            ->setDomain(self::$domain);

        $this->assertNull($identifier_resolver->resolve($identifier, self::DOMAIN_NAMESPACE_ID, 'bad type'));
    }

    public function testWithBadControlTypeButNoControl(): void
    {
        $identifier          = "0001";
        $identifier_resolver = (new PIIdentifierResolver())
            ->setModeNamespaceId()
            ->disableControlTypeIdentifier()
            ->setDomain(self::$domain);

        $this->assertEquals(
            $identifier,
            $identifier_resolver->resolve($identifier, self::DOMAIN_NAMESPACE_ID, 'bad type')
        );
    }

    public function testWithCustomControlType(): void
    {
        $identifier          = "0001";
        $custom_code_type    = 'personal identifier';
        $identifier_resolver = (new PIIdentifierResolver())
            ->setModeNamespaceId()
            ->setCustomTypeCodeExpected($custom_code_type)
            ->setDomain(self::$domain);

        $this->assertEquals(
            $identifier,
            $identifier_resolver->resolve($identifier, self::DOMAIN_NAMESPACE_ID, $custom_code_type)
        );
    }
}
