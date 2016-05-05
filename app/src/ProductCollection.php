<?php

namespace Enrico;

class ProductCollection
{
    private $logger;
    private $items;
    private $total = 0;

    /**
     * Construct 
     *
     * @param Monolog\Logger $logger
     */
    public function __construct($logger)
    {
        $this->logger = $logger;
        $this->items = [];
        $this->logger->info("ProductCollection has been initialized");
    }

    /**
     * Add item to the collection 
     *
     * @param Array $item Associative array with the product details
     */
    public function add_item($item)
    {   
    	$this->logger->info("ProductCollection: add_item function has started");

		array_push($this->items, $item);
        $this->total += $item['unit_price'];
        $this->logger->info("ProductCollection: a new item has been added to the collection (".json_encode($item).")");
    }

    /**
     * Get the list of items from the collection 
     *
     * @return Array List of items in the collection
     */
    public function get_items(){
    	return $this->items;
    }

    /**
     * Get the total price of the items in the collection 
     *
     * @return string Total price
     */
    public function get_total(){
    	return $this->total;
    }

}