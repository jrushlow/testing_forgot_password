<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Entity;

use App\Entity\PasswordResetRequest;
use PHPUnit\Framework\TestCase;
use SymfonyCasts\Bundle\ResetPassword\Model\PasswordResetRequestInterface;

class PasswordResetRequestTest extends TestCase
{
    /** @test */
    public function implementsPasswordResetRequestInterface(): void
    {
        $expected = PasswordResetRequestInterface::class;

        $interfaces = class_implements(PasswordResetRequest::class);

        self::assertArrayHasKey(
            $expected,
            $interfaces,
            sprintf('PasswordResetRequest::class does not implement %s', $expected)
        );
    }

    public function propertyDataProvider(): \Generator
    {
        yield ['id'];
        yield ['user'];
    }

    /**
     * @test
     * @dataProvider propertyDataProvider
     */
    public function hasProperties(string $propertyName): void
    {
        self::assertClassHasAttribute(
            $propertyName,
            PasswordResetRequest::class,
            sprintf('PasswordResetRequest::class does not have %s property.', $propertyName)
        );
    }

    public function methodDataProvider(): \Generator
    {
        yield['getUser'];
    }

    /**
     * @test
     * @dataProvider methodDataProvider
     */
    public function hasMethod(string $methodName): void
    {
        self::assertTrue(
            method_exists(PasswordResetRequest::class, $methodName),
            sprintf('PasswordResetRequest::class does not have %s method defined', $methodName)
        );
    }
}
