<?php

use Enrico\SainsParser;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/app/dependencies.php';

try {

	$product_list_url = "http://hiring-tests.s3-website-eu-west-1.amazonaws.com/2015_Developer_Scrape/5_products.html";

	$app = new SainsParser($container['HTMLparser'], $container['productCollection'], $container['logger'], $container['settings']['parser_settings']);
	$app->set_product_list_url($product_list_url);
	echo "running...\n";
	$app->run();

	echo $app->get_summary()."\n";

} catch(\Exception $e){
	print_r($e->getMessage()."\n");
}

