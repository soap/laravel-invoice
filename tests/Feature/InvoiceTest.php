<?php

namespace NeptuneSoftware\Invoicable\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use NeptuneSoftware\Invoicable\AbstractTestCase;
use NeptuneSoftware\Invoicable\CustomerTestModel;
use NeptuneSoftware\Invoicable\Interfaces\InvoiceServiceInterface;
use NeptuneSoftware\Invoicable\ProductTestModel;

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
    public function canAddAmountExclTaxToInvoice()
    {
        $this->service->create($this->customer);

        $this->service->addAmountExclTax($this->product, 100, 'Some description', 0.21);
        $this->service->addAmountExclTax($this->product, 100, 'Some description', 0.21);

        $this->assertEquals('242', (string) $this->service->getInvoice()->total);
        $this->assertEquals('42', (string) $this->service->getInvoice()->tax);
    }

    /** @test */
    public function canAddAmountInclTaxToInvoice()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();

        $this->service->addAmountInclTax($this->product, 121, 'Some description', 0.21);
        $this->service->addAmountInclTax($this->product, 121, 'Some description', 0.21);

        $this->assertEquals('242', (string) $new_invoice->total);
        $this->assertEquals('42', (string) $new_invoice->tax);
    }

    /** @test */
    public function canHandleNegativeAmounts()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();

        $this->service->addAmountInclTax($this->product, 121, 'Some description', 0.21);
        $this->service->addAmountInclTax($this->product, -121, 'Some negative amount description', 0.21);

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

        $this->service->addAmountInclTax($this->product, 121, 'Some description', 0.21);
        $this->service->addAmountInclTax($this->product, 121, 'Some description', 0.21);
        $view = $this->service->view();
        $rendered = $view->render(); // fails if view cannot be rendered
        $this->assertTrue(true);
    }

    /** @test */
    public function canGetInvoicePdf()
    {
        $this->service->create($this->customer);
        $this->service->addAmountInclTax($this->product, 121, 'Some description', 0.21);
        $this->service->addAmountInclTax($this->product, 121, 'Some description', 0.21);
        $pdf = $this->service->pdf();  // fails if pdf cannot be rendered
        $this->assertTrue(true);
    }

    /** @test */
    public function canDownloadInvoicePdf()
    {
        $this->service->create($this->customer);
        $this->service->addAmountInclTax($this->product, 121, 'Some description', 0.21);
        $this->service->addAmountInclTax($this->product, 121, 'Some description', 0.21);
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

        $this->service->setFree()->addAmountExclTax($this->product, 100, 'Free sale', 0.21);
        $this->service->addAmountExclTax($this->product, 100, 'Some description', 0.21);

        $this->assertEquals('121', (string) $new_invoice->total);
        $this->assertEquals('21', (string) $new_invoice->tax);
        $this->assertEquals('121', (string) $new_invoice->discount);
    }

    /** @test */
    public function canSaleComplimentary()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();

        $this->service->setComplimentary()->addAmountExclTax($this->product, 100, 'Complimentary sale', 0.21);
        $this->service->addAmountExclTax($this->product, 100, 'Some description', 0.21);

        $this->assertEquals('121', (string) $new_invoice->total);
        $this->assertEquals('21', (string) $new_invoice->tax);
        $this->assertEquals('121', (string) $new_invoice->discount);
    }

    /** @test */
    public function canSaleComplimentaryAndFree()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();

        $this->service->setComplimentary()->addAmountExclTax($this->product, 100, 'Complimentary sale', 0.21);
        $this->service->setFree()->addAmountInclTax($this->product, 121, 'Free sale', 0.21);

        $this->assertEquals('0', (string) $new_invoice->total);
        $this->assertEquals('0', (string) $new_invoice->tax);
        $this->assertEquals('242', (string) $new_invoice->discount);
    }

    /** @test */
    public function canSaleComplimentaryAndFreeAndRegular()
    {
        $new_invoice = $this->service->create($this->customer)->getInvoice();

        $this->service->setComplimentary()->addAmountExclTax($this->product, 100, 'Complimentary sale', 0.21);
        $this->service->setFree()->addAmountInclTax($this->product, 121, 'Free sale', 0.21);
        $this->service->addAmountInclTax($this->product, 121, 'Regular sale', 0.21);

        $this->assertEquals('121', (string) $new_invoice->total);
        $this->assertEquals('21', (string) $new_invoice->tax);
        $this->assertEquals('242', (string) $new_invoice->discount);
    }
}
