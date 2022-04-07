<?php

use Phalcon\Mvc\Model;

class Orders extends Model
{

    public $cust_name;
    public $cust_address;
    public $zipcode;
    public $products;
    public $quantity;
    public $date;
}
