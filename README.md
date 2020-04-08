# laravel-invoice

![PHP Composer](https://github.com/neptunesoftware/laravel-invoice/workflows/PHP%20Composer/badge.svg)
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

This package has been changed, updated and redistributed.
To utilize original distribution, see [sandervanhooft/laravel-invoicable](https://github.com/sandervanhooft/laravel-invoicable)
repository.

**IMPORTANT**
> This fork is going to be maintained by @neptunesoftware and 
> it's not compatible with original repository.

Easy invoice creation for Laravel. Unlike Laravel Cashier, this package is payment gateway agnostic.

## What is different?

In order to follow changes, see [changelog](CHANGELOG.md) file.

## Structure

```
.
├── config              # Configuration file
├── database            # Database files
│   └── migrations      
├── resources           # Resource files 
│   └── views           
├── src                 # Soruce files
│   ├── Interfaces      
│   ├── Models          
│   ├── Providers       
│   ├── Scopes          
│   ├── Services        
│   └── Traits          
└── tests               # Test files
    ├── Feature         
    └── Unit            
```

## Install

Via Composer

``` bash
$ composer require neptunesoftware/laravel-invoice
```

You can publish the migration with:

``` bash
$ php artisan vendor:publish --provider="NeptuneSoftware\Invoice\Providers\InvoiceServiceProvider" --tag="migrations"
```

After the migration has been published you can create the invoices and invoice_lines tables by running the migrations:

``` bash
$ php artisan migrate
```

Optionally, you can also publish the `invoice.php` config file with:

``` bash
$ php artisan vendor:publish --provider="NeptuneSoftware\Invoice\Providers\InvoiceServiceProvider" --tag="config"
```

This is what the default config file looks like:

``` php

return [
    'default_currency' => 'TRY',
    'default_status' => 'concept',
    'locale' => 'tr_TR',
    'table_names' => [
        'invoices' => 'invoices',
        'invoice_lines' => 'invoice_lines',
    ]
];
```

If you'd like to override the design of the invoice blade view and pdf, publish the view:

``` bash
$ php artisan vendor:publish --provider="NeptuneSoftware\Invoice\Providers\InvoiceServiceProvider" --tag="views"
```

You can now edit `receipt.blade.php` in `<project_root>/resources/views/invoice/receipt.blade.php` to match your style.


## Usage

__Money figures are in cents!__

Add the HasInvoice trait to the Eloquent model which needs to send or receive invoices (typically a Customer or Company model):

``` php
use Illuminate\Database\Eloquent\Model;
use NeptuneSoftware\Invoice\Traits\HasInvoice;

class Order extends Model
{
    use HasInvoice; // enables the ->invoices() Eloquent relationship
}
```

Now you can create invoices for a customer:


``` php
$customer = Customer::first();
$product = Product::first(); // Any model to be referenced in an invoice line
$service = $service->create($customer); // Injected dependency 

// To add a line to the invoice, use these example parameters:
//  Amount:
//      118 (₺1,18) incl tax
//      100 (₺1,00) excl tax
//  Description: 'Some description'
//  Tax percentage: 0.18 (18%)

# Scenerio 1:
$service->addTaxPercentage('VAT', 0.18)->addAmountInclTax($product, 118, 'Some description');
$service->addTaxPercentage('VAT', 0.18)->addAmountExclTax($product, 100, 'Some description');

# Scenerio 2:
$service->addTaxFixed('VAT', 18)->addAmountInclTax($product, 118, 'Some description');
$service->addTaxFixed('VAT', 18)->addAmountExclTax($product, 100, 'Some description');

# Scenerio 3 for taxes:
$service->addTaxPercentage('VAT', 0.18)->addAmountInclTax($product, 118, 'Some description');
$service->addTaxFixed('VAT', 18)->addAmountExclTax($product, 100, 'Some description');

// Invoice totals are now updated
$invoice = $service->getInvoice();
echo $invoice->total; // 236
echo $invoice->tax; // 36

// Set additional information (optional)
$invoice->currency; // defaults to 'TRY' (see config file)
$invoice->status; // defaults to 'concept' (see config file)
$invoice->receiver_info; // defaul ts to null
$invoice->sender_info; // defaults to null
$invoice->payment_info; // defaults to null
$invoice->note; // defaults to null

// access individual invoice lines using Eloquent relationship
$service->lines;
$service->lines();

// Access as pdf
$service->download(); // download as pdf (returns http response)
$service->pdf(); // or just grab the pdf (raw bytes)

// Handling discounts
// By adding a line with a negative amount.
$invoice = $invoice->setReference($product)->addAmountInclTax(-118, 'A nice discount', 0.18);

// Or by applying the discount and discribing the discount manually
$invoice = $invoice->setReference($product)->addAmountInclTax(118 * (1 - 0.30), 'Product XYZ incl 30% discount', 0.18);

// Convenience methods
$service->findByReference($reference);
$service->findByReferenceOrFail($reference);
$service->invoicable() // Access the related model
```


## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Credits
- [Burak](https://github.com/ikidnapmyself)
- [Fatih](https://github.com/kablanfatih)
- [Uğur](https://github.com/ugurdnlr)
- [Sander van Hooft](https://github.com/sandervanhooft)
- [All Contributors][link-contributors]
- Inspired by [Laravel Cashier](https://github.com/laravel/cashier)'s invoices.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/neptunesoftware/laravel-invoice.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[ico-downloads]: https://img.shields.io/packagist/dt/neptunesoftware/laravel-invoice.svg

[link-packagist]: https://packagist.org/packages/neptunesoftware/laravel-invoice
[link-downloads]: https://packagist.org/packages/neptunesoftware/laravel-invoice
[link-contributors]: ../../contributors
