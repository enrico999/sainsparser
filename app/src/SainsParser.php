<?php

namespace Enrico;

use \Exception;

class SainsParser {

	private $product_list_url;
	private $HTMLparser;
	private $productCollection;
	private $parser_settings;
	private $logger;

    /**
     * Construct 
     *
     * @param Monolog\Logger $logger
     * @param PHPHtmlParser\Dom $HTMLparser
     * @param Enrico\ProductCollection $productCollection
     * @param Array $parser_settings
     */
	public function __construct($HTMLparser, $productCollection, $logger, $parser_settings){
		$this->HTMLparser = $HTMLparser;
		$this->parser_settings = $parser_settings;
		$this->productCollection = $productCollection;
		$this->logger = $logger;

		$this->logger->info("SainsParser has been initialized");
	}

	/**
     * Set the product list url for the parser 
     *
     * @param String $url URL of the product list page 
     * @throws Exception when an invalid product list url is supplied
     */
	public function set_product_list_url($url = null){
		$this->logger->info("SainsParser: set_product_list_url function has started");

		if($url === null || filter_var($url, FILTER_VALIDATE_URL) === false){
			$this->logger->info("An invalid product_list_url URL was supplied");
			throw new Exception("Please provide a valid URL");
		}

		$this->product_list_url = $url;
		$this->logger->info("SainsParser: product_list_url has been set to '$url'");
	}

	/**
     * Run the parser
     *
     * @throws Exception when an invalid product list url is supplied
     */
	public function run(){

		$this->logger->info("SainsParser: Run function has started");

		if(empty($this->product_list_url)){
			$this->logger->info("An invalid product_list_url URL was supplied");
			throw new Exception("Please provide a valid product_list_url URL before running the script");
		}

		// Load the parser with the url
		$list_parser = $this->HTMLparser->loadFromUrl($this->product_list_url);
		
		// $detail_url_list will contain a list of DOM elements that contain a link to the product's detail page
		$detail_url_list = $list_parser->find($this->parser_settings['product_detail_url']['element']);

		// Loop through each product detail url and parse the page
		foreach ($detail_url_list as $detail_url_item)
		{
			$product = [];

			// Extract the href attribute from the DOM element
			// It will contain the url to the product details
			$detail_url = trim($detail_url_item->getAttribute('href'));

			if(empty($detail_url) || filter_var($detail_url, FILTER_VALIDATE_URL) === false){ continue; }

			// Load the parser with the found url
			$detail_parser = $this->HTMLparser->loadFromUrl($detail_url);

			// Calculate the size of the HTML page and assign it to $product
			// A temporary file will be created to dump the HTML and calculate the file size. It will then be removed.
			$temporary_filename = __DIR__.$this->parser_settings['temporary_html_file'];
			$filesize = file_put_contents($temporary_filename, $detail_parser);
			unlink($temporary_filename);

			// Save the page filesize in product
			$product['size'] = round($filesize / 1024, 2)."KB";

			// Based on the settings provided in $this->parser_settings['product_detail'], parse the page and extract the items needed
			foreach($this->parser_settings['product_detail'] as $key => $parser_item_settings){

				$index = $parser_item_settings['index'];
				switch ($parser_item_settings['type']) {

				    case "string":
				        $product[$key] = trim(strip_tags($detail_parser->find($parser_item_settings['element'])[$index]));
				        break;

				    case "numeric":
				    	$raw_input = trim(strip_tags($detail_parser->find($parser_item_settings['element'])[$index]->firstChild()));
				        $product[$key] = preg_replace("/[^0-9,.]/", "", $raw_input);
				        break;

				    default:
				        break;
				}
			}

			$this->productCollection->add_item($product);

		}
	}

	/**
     * Get the summary details of the collection 
     *
     * @return Array Summary of the collection
     */
	public function get_summary(){
		$summary = [
			'results' => $this->productCollection->get_items(),
			'total' => $this->productCollection->get_total()
		];

		return json_encode($summary);
	}

}