<?php namespace OhMyBrew\ShopifyApp\Test;

use OhMyBrew\ShopifyApp\ShopifyApp;
use OhMyBrew\ShopifyApp\Models\Shop;

class ShopifyAppControllerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->shopifyApp = new ShopifyApp($this->app);
    }

    public function testShopWithoutSession()
    {
        // No session, no API instance, thus no shop
        $this->assertNull($this->shopifyApp->shop());
    }

    public function testShopWithSession()
    {
        session(['shopify_domain' => 'example.myshopify.com']);

        // First run should store the shop object to shop var
        $run1 = $this->shopifyApp->shop();

        // Second run should retrive shop var
        $run2 = $this->shopifyApp->shop();

        $this->assertEquals($run1, $run2);
    }

    public function testCreatesNewShopWithSessionIfItDoesNotExist()
    {
        session(['shopify_domain' => 'example-nonexistant.myshopify.com']);
        $this->assertEquals(null, Shop::where('shopify_domain', 'example-nonexistant.myshopify.com')->first());

        $this->shopifyApp->shop();

        $this->assertNotNull(Shop::where('shopify_domain', 'example-nonexistant.myshopify.com')->first());
    }

    public function testReturnsApiInstance()
    {
        $this->assertEquals(\OhMyBrew\BasicShopifyAPI::class, get_class($this->shopifyApp->api()));
    }

    public function testShopSanitize()
    {
        $domains = ['my-shop', 'my-shop.myshopify.com', 'https://my-shop.myshopify.com', 'http://my-shop.myshopify.com'];
        $domains_2 = ['my-shop', 'my-shop.myshopify.io', 'https://my-shop.myshopify.io', 'http://my-shop.myshopify.io'];

        // Test for standard myshopify.com
        foreach ($domains as $domain) {
            $this->assertEquals('my-shop.myshopify.com', $this->shopifyApp->sanitizeShopDomain($domain));
        }

        // Test if someone changed the domain
        config(['shopify-app.myshopify_domain' => 'myshopify.io']);
        foreach ($domains_2 as $domain) {
            $this->assertEquals('my-shop.myshopify.io', $this->shopifyApp->sanitizeShopDomain($domain));
        }
    }
}