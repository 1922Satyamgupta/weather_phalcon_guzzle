<?php
// print_r(apache_get_modules());
// echo "<pre>"; print_r($_SERVER); die;
// $_SERVER["REQUEST_URI"] = str_replace("/phalt/","/",$_SERVER["REQUEST_URI"]);
// $_GET["_url"] = "/";
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config;
use Phalcon\Config\ConfigFactory;
use Phalcon\Session\Manager;
// use Phalcon\Session\Adapter\Stream;
use Phalcon\Escaper;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream;
use App\translate\Locale;
// use Phalcon\Logger\LoggerFactory;
// use Phalcon\Logger\AdapterFactory;

$config = new Config([]);

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// Register an autoloader
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/models/",
        
    ]
);


$loader->registerNamespaces(
    [
        
        
    ]
);
$loader->register();

$container = new FactoryDefault();

$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);

$application = new Application($container);



// $container->set(
//     'db',
//     function () {
//         $fileName = '../app/etc/config.php';
//         // $factory  = new ConfigFactory();
//         // return $factory->newInstance('php', $fileName);
//         $config = new Config([]);
//         $array = new \Phalcon\config\Adapter\Php($fileName);
//         $config->merge($array);
//         return $config;
//     }, 
//     true
// );

// $container->set(
//     'mongo',
//     function () {
//         $mongo = new MongoClient();

//         return $mongo->selectDB('phalt');
//     },
//     true
// );

$container->setShared(
    'session',
    function () {
        $session = new Manager();
        $files = new Stream(
            [
                'savePath' => '/tmp',
            ]
        );
        $session->setAdapter($files);
        $session->start();

        return $session;
    }
);
$container->set(
    'config',
    function () {
        $fileName = '../app/etc/config.php';
        // $factory  = new ConfigFactory();
        // return $factory->newInstance('php', $fileName);
        $config = new Config([]);
        $array = new \Phalcon\config\Adapter\Php($fileName);
        $config->merge($array);
        return $config;
    }, 
    true
);
$container->set(
    'db',
    function () {
        $config = $this->getConfig();
        return new Mysql(
            [
                'host'     => $config->db->host,
                'username' =>  $config->db->username,
                'password' =>  $config->db->password,
                'dbname'   => $config->db->dbname,
                ]
        );
        }
);

$container->set(
    'logger',
    function () {
        $adapters1 = new Stream("../app/storage/logs/register.log");
        $adapters2 = new Stream("../app/storage/logs/login.log");
        $logger  = new Logger(
       'messages',
       [
        'register' => $adapters1,
        'login' => $adapters2
    ]
);
 return $logger;
    }
);


$container->set(
    'escaper',
    function () {
        return new Escaper();
    }
);

try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
