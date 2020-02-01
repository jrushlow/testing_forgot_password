<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\BundleUnitTests\Model;

use App\Tests\Fixtures\PasswordResetRequestTraitFixture;
use PHPUnit\Framework\TestCase;

class PasswordResetRequestTraitFixtureTest extends TestCase
{
    /** @var \DateTimeImmutable */
    protected $expiresAt;

    /** @var string */
    protected $selector;

    /** @var string */
    protected $hashedToken;

    protected function setUp()
    {
        $this->expiresAt = $this->createMock(\DateTimeImmutable::class);
        $this->selector = 'selector';
        $this->hashedToken = 'hashed';
    }

    protected function getFixture(): PasswordResetRequestTraitFixture
    {
        return new PasswordResetRequestTraitFixture(
            $this->expiresAt,
            $this->selector,
            $this->hashedToken
        );
    }

    public function propertyDataProvider(): \Generator
    {
        yield ['selector'];
        yield ['hashedToken'];
        yield ['requestedAt'];
        yield ['expiresAt'];
    }

    /**
     * @test
     * @dataProvider propertyDataProvider
     */
    public function hasProperty(string $propertyName): void
    {
        self::assertClassHasAttribute(
            $propertyName,
            PasswordResetRequestTraitFixture::class,
            sprintf('PasswordResetRequestTrait::class does not have %s property defined.', $propertyName)
        );
    }

    public function methodDataProvider(): \Generator
    {
        yield ['getRequestedAt'];
        yield ['isExpired'];
        yield ['getExpiresAt'];
        yield ['getHashedToken'];
    }

    /**
     * @test
     * @dataProvider methodDataProvider
     */
    public function hasMethod(string $methodName): void
    {
        self::assertTrue(
            method_exists(PasswordResetRequestTraitFixture::class, $methodName),
            sprintf('PasswordResetRequestTrait::class does not have %s method defined.', $methodName)
        );
    }

    /** @test */
    public function getRequestAtReturnsImmutableDateTime(): void
    {
        $trait = $this->getFixture();

        self::assertInstanceOf(\DateTimeImmutable::class, $trait->getRequestedAt());
    }

    /** @test */
    public function isExpiredReturnsFalseWithTimeInFuture(): void
    {
        $this->expiresAt
            ->expects($this->once())
            ->method('getTimestamp')
            ->willReturn(time() + (360))
        ;

        $trait = $this->getFixture();
        self::assertFalse($trait->isExpired());
    }

    /** @test */
    public function isExpiredReturnsTrueWithTimeInPast(): void
    {
        $this->expiresAt
            ->expects($this->once())
            ->method('getTimestamp')
            ->willReturn(time() - (360))
        ;

        $trait = $this->getFixture();
        self::assertTrue($trait->isExpired());
    }

    /** @test */
    public function getExpiresAtReturnsDateTimeInterface(): void
    {
        $trait = $this->getFixture();

        self::assertInstanceOf(\DateTimeInterface::class, $trait->getExpiresAt());
    }

    /** @test */
    public function getHashedTokenReturnsToken(): void
    {
        $trait = $this->getFixture();
        self::assertSame($this->hashedToken, $trait->getHashedToken());
    }
}
