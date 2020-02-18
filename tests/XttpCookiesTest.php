<?php

namespace JohnathanSmith\Xttp\Tests;

use GuzzleHttp\Cookie\CookieJar;
use JohnathanSmith\Xttp\XttpCookies;
use PHPUnit\Framework\TestCase;

/**
 * @group xttp-cookies
 * @group cookies
 */
class XttpCookiesTest extends TestCase
{
    /**
     * @var \JohnathanSmith\Xttp\XttpCookies
     */
    private $cookies;

    public function setUp(): void
    {
        parent::setUp();
        $this->cookies = new XttpCookies(new CookieJar());
    }

    /** @test */
    public function can_set_and_add_cookies()
    {
        $this->cookies->set(['Johnathan' => 'Smith', 'Sarah' => 'Smith']);

        $this->cookies->add(['Kristabelle' => 'Levi', 'Johnathan' => 'Ray']);

        $names = $this->cookies->getNames();
        $values = $this->cookies->getValues();

        $this->assertEquals(['Kristabelle', 'Johnathan', 'Sarah'], $names);
        $this->assertEquals(['Levi', 'Ray', 'Smith'], $values);
    }
}
