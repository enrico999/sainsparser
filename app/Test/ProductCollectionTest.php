<?php

namespace Test;

use Enrico\ProductCollection;

class ProductCollectionTest extends \PHPUnit_Framework_TestCase
{
	private $logger;

	protected function setUp()
    {
    	$this->logger = $this->getMockBuilder('\Monolog\Logger')->setConstructorArgs(array('app'))->getMock();
    }

    public function testCollectionIsEmptyWhenCreated(){

    	$pc = new ProductCollection($this->logger);
    	$this->assertEmpty($pc->get_items());
    	$this->assertEquals(0, count($pc->get_items()));
    }

    public function testAddingNewItemIntoCollectionIncreasesCollectionSize(){

    	$product = [
    		"title" => "Sainsbury's Apricot Ripe & Ready x5",
    		"unit_price" => 1.50,
    		"size" => "20.85KB",
    		"description" => "Apricots"
    	];

    	$pc = new ProductCollection($this->logger);
    	$pc->add_item($product);

    	$this->assertNotEmpty($pc->get_items());
    	$this->assertEquals(1, count($pc->get_items()));
    }

    public function testCollectionTotalReturnsExpectedResult(){

    	$expected_total = 3;

    	$product1 = [
    		"title" => "Sainsbury's Apricot Ripe & Ready x5",
    		"unit_price" => 1.50,
    		"size" => "20.85KB",
    		"description" => "Apricots"
    	];

    	$product2 = [
    		"title" => "Sainsbury's Apricot Ripe & Ready x5",
    		"unit_price" => 1.50,
    		"size" => "20.85KB",
    		"description" => "Apricots"
    	];

    	$pc = new ProductCollection($this->logger);
    	$pc->add_item($product1);
    	$pc->add_item($product2);

    	$this->assertEquals($expected_total, $pc->get_total());
    }

}