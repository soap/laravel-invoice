<?php

namespace NeptuneSoftware\Invoice\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use NeptuneSoftware\Invoice\AbstractTestCase;
use NeptuneSoftware\Invoice\CustomerTestModel;
use NeptuneSoftware\Invoice\Interfaces\InvoiceServiceInterface;
use NeptuneSoftware\Invoice\ProductTestModel;

class InvoiceTest extends AbstractTestCase
{
    use DatabaseMigrations;

    private $invoice;

    /**
     * @var ProductTestModel $product
     */
    private $product;

    /**
     * @var CustomerTestModel $customer
     */
    private $customer;

    /**
     * @var InvoiceServiceInterface $service
     */
    private $service;


    public function setUp(): void
    {
        parent::setUp();
        $this->customer = new CustomerTestModel();
        $this->customer->save();
        $this->product = new ProductTestModel();
        $this->product->save();

        $this->service  = $this->app->make(InvoiceServiceInterface::class);
    }


    /** @test */
    public function canCreateInvoice()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();

        $this->assertEquals('0', (string) $new_invoice->total);
        $this->assertEquals('0', (string) $new_invoice->tax);
        $this->assertEquals('TRY', $new_invoice->currency);
        $this->assertEquals('concept', $new_invoice->status);
        $this->assertNotNull($new_invoice->reference);
    }

    /** @test */
    public function canAddAmountExclTaxPercentageToInvoice()
    {
        $this->service->create($this->customer);

        $this->service->addTaxPercentage('VAT', 0.18)->addAmountExclTax($this->product, 100, 'Some description');
        $this->service->addTaxPercentage('VAT', 0.18)->addAmountExclTax($this->product, 100, 'Some description');

        $this->assertEquals('236', (string) $this->service->getInvoice()->total);
        $this->assertEquals('36', (string) $this->service->getInvoice()->tax);
    }

    /** @test */
    public function canAddAmountInclTaxPercentageToInvoice()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();

        $this->service->addTaxPercentage('VAT', 0.18)->addAmountInclTax($this->product, 118, 'Some description');
        $this->service->addTaxPercentage('VAT', 0.18)->addAmountInclTax($this->product, 118, 'Some description');

        $this->assertEquals('236', (string) $new_invoice->total);
        $this->assertEquals('36', (string) $new_invoice->tax);
    }

    /** @test */
    public function canAddAmountExclTaxAmountToInvoice()
    {
        $this->service->create($this->customer);

        $this->service->addTaxFixed('VAT', 18)->addAmountExclTax($this->product, 100, 'Some description');
        $this->service->addTaxFixed('VAT', 18)->addAmountExclTax($this->product, 100, 'Some description');

        $this->assertEquals('236', (string) $this->service->getInvoice()->total);
        $this->assertEquals('36', (string) $this->service->getInvoice()->tax);
    }

    /** @test */
    public function canAddAmountInclTaxAmountToInvoice()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();

        $this->service->addTaxFixed('VAT', 18)->addAmountInclTax($this->product, 118, 'Some description');
        $this->service->addTaxFixed('VAT', 18)->addAmountInclTax($this->product, 118, 'Some description');

        $this->assertEquals('236', (string) $new_invoice->total);
        $this->assertEquals('36', (string) $new_invoice->tax);
    }

    /** @test */
    public function canAddAmountExclTaxAmountAndTaxPercentageToInvoice()
    {
        $this->service->create($this->customer);

        $this->service->addTaxFixed('VAT', 18)->addAmountExclTax($this->product, 100, 'Some description');
        $this->service->addTaxPercentage('VAT', 0.18)->addAmountExclTax($this->product, 100, 'Some description');

        $this->assertEquals('236', (string) $this->service->getInvoice()->total);
        $this->assertEquals('36', (string) $this->service->getInvoice()->tax);
    }

    /** @test */
    public function canAddAmountInclTaxAmountAndTaxPercentageToInvoice()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();

        $this->service->addTaxFixed('VAT', 18)->addAmountInclTax($this->product, 118, 'Some description');
        $this->service->addTaxPercentage('VAT', 0.18)->addAmountInclTax($this->product, 118, 'Some description');

        $this->assertEquals('236', (string) $new_invoice->total);
        $this->assertEquals('36', (string) $new_invoice->tax);
    }

    /** @test */
    public function canAddAmountExclMultipleTaxToInvoice()
    {
        $new_invoice = $this->service->create($this->customer);

        $this->service
            ->addTaxFixed('TAX1', 1)
            ->addTaxPercentage('TAX2', 0.18)
            ->addTaxFixed('TAX3', 30)
            ->addAmountExclTax($this->product, 100, 'Some description');

        $this->assertEquals('149', (string) $new_invoice->getInvoice()->total);
        $this->assertEquals('49', (string) $new_invoice->getInvoice()->tax);
    }

    /** @test */
    public function canAddAmountInclMultipleTaxToInvoice()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();

        $this->service
            ->addTaxFixed('TAX1', 1)
            ->addTaxPercentage('TAX2', 0.18)
            ->addTaxFixed('TAX3', 30)
            ->addAmountInclTax($this->product, 149, 'Some description');

        $this->assertEquals('149', (string) $new_invoice->total);
        $this->assertEquals('49', (string) $new_invoice->tax);
    }

    /** @test */
    public function canHandleNegativeAmounts()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();

        $this->service->addTaxPercentage('VAT', 0.18)->addAmountInclTax($this->product, 118, 'Some description');
        $this->service->addTaxPercentage('VAT', 0.18)->addAmountInclTax($this->product, -118, 'Some negative amount description');

        $this->assertEquals('0', (string) $new_invoice->total);
        $this->assertEquals('0', (string) $new_invoice->tax);
    }

    /** @test */
    public function hasUniqueReference()
    {
        $references = array_map(function () {
            return $this->service->create($this->customer)->getInvoice()->reference;
        }, range(1, 100));

        $this->assertCount(100, array_unique($references));
    }

    /** @test */
    public function canGetInvoiceView()
    {
        $this->service->create($this->customer);

        $this->service->addTaxPercentage('VAT', 0.18)->addAmountInclTax($this->product, 118, 'Some description');
        $this->service->addTaxPercentage('VAT', 0.18)->addAmountInclTax($this->product, 118, 'Some description');
        $view = $this->service->view();
        $rendered = $view->render(); // fails if view cannot be rendered
        $this->assertTrue(true);
    }

    /** @test */
    public function canGetInvoicePdf()
    {
        $this->service->create($this->customer);
        $this->service->addTaxPercentage('VAT', 0.18)->addAmountInclTax($this->product, 118, 'Some description');
        $this->service->addTaxPercentage('VAT', 0.18)->addAmountInclTax($this->product, 118, 'Some description');
        $pdf = $this->service->pdf();  // fails if pdf cannot be rendered
        $this->assertTrue(true);
    }

    /** @test */
    public function canDownloadInvoicePdf()
    {
        $this->service->create($this->customer);
        $this->service->addTaxPercentage('VAT', 0.18)->addAmountInclTax($this->product, 118, 'Some description');
        $this->service->addTaxPercentage('VAT', 0.18)->addAmountInclTax($this->product, 118, 'Some description');
        $download = $this->service->download(); // fails if pdf cannot be rendered
        $this->assertTrue(true);
    }

    /** @test */
    public function canFindByReference()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();
        $this->assertEquals($new_invoice->id, $this->service->findByReference($new_invoice->reference)->id);
    }

    /** @test */
    public function canFindByReferenceOrFail()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();
        $this->assertEquals($new_invoice->id, $this->service->findByReferenceOrFail($new_invoice->reference)->id);
    }

    /** @test */
    public function canFindByReferenceOrFailThrowsExceptionForNonExistingReference()
    {
        $this->expectException('Illuminate\Database\Eloquent\ModelNotFoundException');
        $this->service->findByReferenceOrFail('non-existing-reference');
    }

    /** @test */
    public function canAccessRelated()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();
        // Check if correctly set on invoice
        $this->assertEquals(CustomerTestModel::class, $new_invoice->related_type);
        $this->assertEquals($this->customer->id, $new_invoice->related_id);

        // Check if related is accessible
        $this->assertNotNull($new_invoice->related);
        $this->assertEquals(CustomerTestModel::class, get_class($new_invoice->related));
        $this->assertEquals($this->customer->id, $new_invoice->related->id);
    }

    /** @test */
    public function canSaleFree()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();

        $this->service->setFree()->addTaxPercentage('VAT', 0.18)->addAmountExclTax($this->product, 100, 'Free sale');
        $this->service->addTaxPercentage('VAT', 0.18)->addAmountExclTax($this->product, 100, 'Some description');

        $this->assertEquals('118', (string) $new_invoice->total);
        $this->assertEquals('18', (string) $new_invoice->tax);
        $this->assertEquals('118', (string) $new_invoice->discount);
    }

    /** @test */
    public function canSaleComplimentary()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();

        $this->service->setComplimentary()->addTaxPercentage('VAT', 0.18)->addAmountExclTax($this->product, 100, 'Complimentary sale');
        $this->service->addTaxPercentage('VAT', 0.18)->addAmountExclTax($this->product, 100, 'Some description');

        $this->assertEquals('118', (string) $new_invoice->total);
        $this->assertEquals('18', (string) $new_invoice->tax);
        $this->assertEquals('118', (string) $new_invoice->discount);
    }

    /** @test */
    public function canSaleComplimentaryAndFree()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();

        $this->service->setComplimentary()->addTaxPercentage('VAT', 0.18)->addAmountExclTax($this->product, 100, 'Complimentary sale');
        $this->service->setFree()->addTaxPercentage('VAT', 0.18)->addAmountInclTax($this->product, 118, 'Free sale');

        $this->assertEquals('0', (string) $new_invoice->total);
        $this->assertEquals('0', (string) $new_invoice->tax);
        $this->assertEquals('236', (string) $new_invoice->discount);
    }

    /** @test */
    public function canSaleComplimentaryAndFreeAndRegular()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();

        $this->service->setComplimentary()->addTaxPercentage('VAT', 0.18)->addAmountExclTax($this->product, 100, 'Complimentary sale');
        $this->service->setFree()->addTaxPercentage('VAT', 0.18)->addAmountInclTax($this->product, 118, 'Free sale');
        $this->service->addTaxPercentage('VAT', 0.18)->addAmountInclTax($this->product, 118, 'Regular sale');

        $this->assertEquals('118', (string) $new_invoice->total);
        $this->assertEquals('18', (string) $new_invoice->tax);
        $this->assertEquals('236', (string) $new_invoice->discount);
    }
}
