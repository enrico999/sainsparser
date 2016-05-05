<?php

namespace Test;

use Enrico\SainsParser;
use Pimple\Container;
use Enrico\ProductCollection;
use PHPHtmlParser\Dom;

class SainsParserTest extends \PHPUnit_Framework_TestCase
{
    private $container;

    protected function setUp()
    {
        $this->container = new Container();
        $this->container['settings'] = [

            // monolog settings
            'logger' => [
                'name' => 'app',
                'path' => __DIR__ . '/log/app.log',
            ],

            // parser settings
            'parser_settings' => [

                // the elements we want to extract from the product's detail page
                // extending this list will return more details for each product
                'product_detail' => [
                    'title' => [
                        'element' => '.productTitleDescriptionContainer h1',
                        'type' => 'string',
                        'index' => 0
                    ],
                    'unit_price' => [
                        'element' => '.pricePerUnit',
                        'type' => 'numeric',
                        'index' => 0
                    ],
                    'description' => [
                        'element' => '.productText',
                        'type' => 'string',
                        'index' => 0
                    ],
                    // 'item_size' => [
                    //  'element' => '.productText',
                    //  'type' => 'string',
                    //  'index' => 2
                    // ],
                    // 'item_code' => [
                    //  'element' => '.itemCode',
                    //  'type' => 'numeric',
                    //  'index' => 0
                    // ],

                ],

                //  the element of the product list page that contains the url for the individual product detail page
                'product_detail_url' => [
                    'element' => '.product .productInner .productInfoWrapper .productInfo h3 a'
                ],

                // a temporary file used to dump the HTML content of a page to calculate its size
                'temporary_html_file' => '/test.html',
            ]
        ];

        // monolog logger
        $this->container['logger'] = function ($c) {
            $settings = $c['settings'];
            $logger = new \Monolog\Logger($settings['logger']['name']);
            $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
            $logger->pushHandler(new\ Monolog\Handler\StreamHandler($settings['logger']['path'], \Monolog\Logger::DEBUG));
            return $logger;
        };

        // HTML parser
        $this->container['HTMLparser'] = function ($c) {
            return new Dom;
        };

        // Product collection manager
        $this->container['productCollection'] = function ($c) {
            return new ProductCollection($c['logger']);
        };
    }

    public function testEmptyUrlRaisesException() {

        $expected_message = "Please provide a valid URL";

        try {
            $sp = new SainsParser($this->container['HTMLparser'], $this->container['productCollection'], $this->container['logger'], $this->container['settings']['parser_settings']);

            $sp->set_product_list_url();
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), $expected_message);
            return;
        }
    }

    public function testInvalidUrlRaisesException() {

        $expected_message = "Please provide a valid URL";

        try {
            $sp = new SainsParser($this->container['HTMLparser'], $this->container['productCollection'], $this->container['logger'], $this->container['settings']['parser_settings']);

            $sp->set_product_list_url("www.this_is_an_invalid_url");
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), $expected_message);
            return;
        }
    }

    public function testRunFunctionRaisesExceptionIfNoUrlIsSupplied() {

        $expected_message = "Please provide a valid product_list_url URL before running the script";

        try {
            $sp = new SainsParser($this->container['HTMLparser'], $this->container['productCollection'], $this->container['logger'], $this->container['settings']['parser_settings']);

            $sp->run();
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), $expected_message);
            return;
        }
    }

    public function testGetSummaryIsNotEmpty(){

        $product_list_url = "http://hiring-tests.s3-website-eu-west-1.amazonaws.com/2015_Developer_Scrape/5_products.html";
        
        $sp = new SainsParser($this->container['HTMLparser'], $this->container['productCollection'], $this->container['logger'], $this->container['settings']['parser_settings']);
        $sp->set_product_list_url($product_list_url);
        $sp->run();
        $this->assertNotEmpty($sp->get_summary());
    }

    public function testValidUrlWithoutSainsburysProductsGeneratesEmptyResults(){

        $product_list_url = "http://hiring-tests.s3-website-eu-west-1.amazonaws.com/2015_Developer_Scrape/this_url_is_valid_but_doesnt_contain_sainsburys_products.html";
        
        $sp = new SainsParser($this->container['HTMLparser'], $this->container['productCollection'], $this->container['logger'], $this->container['settings']['parser_settings']);
        $sp->set_product_list_url($product_list_url);
        $sp->run();
        $summary_json = $sp->get_summary();
        $summary = json_decode($summary_json, 1);
        $this->assertNotEmpty($summary);
        $this->assertEmpty($summary['results']);
    }

    public function testValidUrlWithSainsburysProductsGeneratesNotEmptyResults(){

        $product_list_url = "http://hiring-tests.s3-website-eu-west-1.amazonaws.com/2015_Developer_Scrape/5_products.html";
        
        $sp = new SainsParser($this->container['HTMLparser'], $this->container['productCollection'], $this->container['logger'], $this->container['settings']['parser_settings']);
        $sp->set_product_list_url($product_list_url);
        $sp->run();
        $summary_json = $sp->get_summary();
        $summary = json_decode($summary_json, 1);
        $this->assertNotEmpty($summary);
        $this->assertNotEmpty($summary['results']);
    }

    

}