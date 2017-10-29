<?php
// define server for memcached
$servers = array(
    array('127.0.0.1', 11211, 33)
);

// Connection creation
$memcache = new Memcached();
$cacheAvailable = $memcache->addServers($servers);

if ($cacheAvailable) {
	echo "Cache available!";
	$all_products = $memcache->get('product_for_sale');
	echo '<pre>';
	print_r($all_products);
	echo '</pre>';
} else {
	echo "Not cached!";
}

