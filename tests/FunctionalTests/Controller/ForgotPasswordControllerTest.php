<?php

declare(strict_types=1);

namespace App\Tests\FunctionalTests\Controller;

use App\Controller\ForgotPasswordController;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class ForgotPasswordControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function showsResetRequestForm(): void
    {
        $client = static::createClient();
        $client->request('GET', '/forgot-password/request');

        self::assertResponseIsSuccessful();
    }

    /**
     * @test
     */
    public function onSubmitRedirectToEmailNotification(): void
    {
        $client = static::createClient();
        $client->request('GET', '/forgot-password/request');

        $client->submitForm('Send e-mail', [
            'password_request_form[email]' => 'jr@rushlow.dev'
        ]);

        self::assertResponseRedirects('/forgot-password/check-email');
    }

    /**
     * @test
     */
    public function errorDisplayedWhenThrottleLimitReached(): void
    {
        $client = static::createClient();
        $client->request('GET', '/forgot-password/request');

        $client->submitForm('Send e-mail', [
            'password_request_form[email]' => 'jr@rushlow.dev'
        ]);


        $client->followRedirects();
        $client->request('GET', '/forgot-password/request');

        $crawler = $client->submitForm('Send e-mail', [
            'password_request_form[email]' => 'jr@rushlow.dev'
        ]);

        self::assertCount(1, $crawler->filter('.flash-error'));
    }

    /**
     * @test
     */
    public function successfulRequestSendsEmail(): void
    {
        $client = static::createClient();
        $client->request('GET', '/forgot-password/request');

        $client->submitForm('Send e-mail', [
            'password_request_form[email]' => 'jr@rushlow.dev'
        ]);

        self::assertEmailCount(1);
    }

    /**
     * @test
     */
    public function emailContainsValidResetToken(): void
    {
        $client = static::createClient();
        $client->request('GET', '/forgot-password/request');

        $client->submitForm('Send e-mail', [
            'password_request_form[email]' => 'jr@rushlow.dev'
        ]);

        $email = self::getMailerMessage();
        $context = $email->getContext();
        $token = $context['resetToken']->getToken();

        $client->followRedirects();
        $client->request('GET', '/forgot-password/reset/'. $token);

        self::assertPageTitleContains('Reset your password');
    }
}
