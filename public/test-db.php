
<?php

echo "Testing database connection...<br>";

// Test with 127.0.0.1

try {

    $pdo1 = new PDO('mysql:host=127.0.0.1;port=3306;dbname=juvo_one', 'juvo', 'YOUR_ACTUAL_PASSWORD');

    echo "✅ Connection with 127.0.0.1: SUCCESS<br>";

} catch (PDOException $e) {

    echo "❌ Connection with 127.0.0.1 failed: " . $e->getMessage() . "<br>";

}

// Test with localhost

try {

    $pdo2 = new PDO('mysql:host=localhost;port=3306;dbname=juvo_one', 'juvo', 'Linkme78');

    echo "✅ Connection with localhost: SUCCESS<br>";

} catch (PDOException $e) {

    echo "❌ Connection with localhost failed: " . $e->getMessage() . "<br>";

}

echo "PHP Version: " . PHP_VERSION . "<br>";

echo "MySQL extensions: " . (extension_loaded('pdo_mysql') ? 'Loaded' : 'Not loaded');

?>

