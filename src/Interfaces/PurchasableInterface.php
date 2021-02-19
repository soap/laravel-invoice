<?php

namespace Soap\Invoices\Interfaces;

interface PurchasableInterface 
{
    public function getKey();
    
    public function getName();

    public function getPrice();
}