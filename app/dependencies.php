<?php

use Pimple\Container;
use Enrico\ProductCollection;
use PHPHtmlParser\Dom;

// Create the DI container
$container = new Container();

// Settings
$container['settings'] = [

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
			// 	'element' => '.productText',
			// 	'type' => 'string',
			// 	'index' => 2
			// ],
			// 'item_code' => [
			// 	'element' => '.itemCode',
			// 	'type' => 'numeric',
			// 	'index' => 0
			// ],

		],

		//	the element of the product list page that contains the url for the individual product detail page
		'product_detail_url' => [
			'element' => '.product .productInner .productInfoWrapper .productInfo h3 a'
		],

		// a temporary file used to dump the HTML content of a page to calculate its size
		'temporary_html_file' => '/test.html',
	]
];

// monolog logger
$container['logger'] = function ($c) {
	$settings = $c['settings'];
	$logger = new Monolog\Logger($settings['logger']['name']);
	$logger->pushProcessor(new Monolog\Processor\UidProcessor());
	$logger->pushHandler(new Monolog\Handler\StreamHandler($settings['logger']['path'], Monolog\Logger::DEBUG));
	return $logger;
};

// HTML parser
$container['HTMLparser'] = function ($c) {
	return new Dom;
};

// Product collection manager
$container['productCollection'] = function ($c) {
	return new ProductCollection($c['logger']);
};