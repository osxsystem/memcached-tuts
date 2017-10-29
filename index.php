<?php

define('SERVER_NAME', '127.0.0.1');
define('USERNAME', 'root');
define('PASSWORD', 'root');
define('PORT', '3306');
define('DB_NAME', 'memchached_tut01');

try {
    $conn = new PDO('mysql:host='.SERVER_NAME.';port='.PORT.';dbname='. DB_NAME , USERNAME, PASSWORD);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully"; 
}
catch(PDOException $e) {
	echo "Connection failed: " . $e->getMessage();
}

// define server for memcached
$servers = array(
    array('127.0.0.1', 11211, 33)
);

// Connection creation
$memcache = new Memcached();
$cacheAvailable = $memcache->addServers($servers);


// We have validated and sanitized our data
// We have escaped every risky char with mysql_real_escape_string()
// Now we want to write them to our database :
$id = 29;
$name = 'iphone 11s';
$description = 'new iphone 7 32Gb, and more...';
$price = 660.00;
$sql = "INSERT INTO products (id, name, description, price) VALUES (
$id, '$name', '$description', '$price'
)";

try
{
    // Starts our transaction
    $conn->beginTransaction();

    // Batches a bunch of queries
    $conn->exec($sql);

    // Commits out queries
    if ($conn->commit()) {
        // We build a unique key that we can build again later
        // We will use the word 'product' plus our product's id (eg. "product_12")
        $key = 'all_product';

        // We store an associative array containing our product data
        $sth = $conn->prepare("SELECT * FROM products");
        $sth->execute();

        /* Fetch all of the remaining rows in the result set */
        print("Fetch all of the remaining rows in the result set:\n");
        $result = $sth->fetchAll();

        // And we ask Memcached to store that data
        $memcache->set('product_for_sale', $result, 300000);
    }
} catch (Exception $e) {
    // Something borked, undo the queries!!
    $conn->rollBack();
    echo 'ERROR: ' . $e->getMessage();
}
