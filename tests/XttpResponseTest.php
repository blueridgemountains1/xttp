<?php

use GuzzleHttp\Psr7\Response;
use JohnathanSmith\Xttp\XttpResponse;
use PHPUnit\Framework\TestCase;

/**
 * @group response
 */
class XttpResponseTest extends TestCase
{
    /**
     * @test
     */
    public function withOptions_will_replace_options()
    {
        $bodyArray = ['Johnathan' => 'Smith'];
        $bodyString = json_encode($bodyArray);
        $headerKey = 'X-Foo';
        $headerVal = 'Bar';

        $response = new Response(200, [$headerKey => $headerVal], $bodyString);

        $r = new XttpResponse(
           $response
        );

        $this->assertEquals($bodyString, $r->body());
        $this->assertEquals($headerVal, $r->header($headerKey));
        $this->assertEquals([$headerKey => $headerVal], $r->headers());

        $this->assertEquals($bodyArray, $r->json());

        $this->assertEquals(200, $r->status());
        $this->assertEquals(true, $r->isSuccess());
        $this->assertEquals(true, $r->isOk());
        $this->assertEquals(false, $r->isRedirect());
        $this->assertEquals(false, $r->isClientError());
        $this->assertEquals(false, $r->isServerError());
    }
}
