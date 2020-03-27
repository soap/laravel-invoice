<?php

namespace NeptuneSoftware\Invoicable\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use NeptuneSoftware\Invoicable\AbstractTestCase;
use NeptuneSoftware\Invoicable\CustomerTestModel;
use NeptuneSoftware\Invoicable\Interfaces\BillServiceInterface;
use NeptuneSoftware\Invoicable\ProductTestModel;

class BillTest extends AbstractTestCase
{
    use DatabaseMigrations;

    private $bill;

    /**
     * @var ProductTestModel $product
     */
    private $product;

    /**
     * @var CustomerTestModel $customer
     */
    private $customer;

    /**
     * @var BillServiceInterface $service
     */
    private $service;


    public function setUp(): void
    {
        parent::setUp();
        $this->customer = new CustomerTestModel();
        $this->customer->save();
        $this->product = new ProductTestModel();
        $this->product->save();

        $this->service  = $this->app->make(BillServiceInterface::class);
    }


    /** @test */
    public function canCreateBill()
    {
        $new_bill = $this->service->create($this->customer)->getBill();

        $this->assertEquals('0', (string) $new_bill->total);
        $this->assertEquals('0', (string) $new_bill->tax);
        $this->assertEquals('TRY', $new_bill->currency);
        $this->assertEquals('concept', $new_bill->status);
        $this->assertNotNull($new_bill->reference);
    }

    /** @test */
    public function canAddAmountExclTaxToBill()
    {
        $this->service->create($this->customer);

        $this->service->addAmountExclTax($this->product, 100, 'Some description', 0.21);
        $this->service->addAmountExclTax($this->product, 100, 'Some description', 0.21);

        $this->assertEquals('242', (string) $this->service->getBill()->total);
        $this->assertEquals('42', (string) $this->service->getBill()->tax);
    }

    /** @test */
    public function canAddAmountInclTaxToBill()
    {
        $new_bill = $this->service->create($this->customer)->getBill();

        $this->service->addAmountInclTax($this->product, 121, 'Some description', 0.21);
        $this->service->addAmountInclTax($this->product, 121, 'Some description', 0.21);

        $this->assertEquals('242', (string) $new_bill->total);
        $this->assertEquals('42', (string) $new_bill->tax);
    }

    /** @test */
    public function canHandleNegativeAmounts()
    {
        $new_bill = $this->service->create($this->customer)->getBill();

        $this->service->addAmountInclTax($this->product, 121, 'Some description', 0.21);
        $this->service->addAmountInclTax($this->product, -121, 'Some negative amount description', 0.21);

        $this->assertEquals('0', (string) $new_bill->total);
        $this->assertEquals('0', (string) $new_bill->tax);
    }

    /** @test */
    public function hasUniqueReference()
    {
        $references = array_map(function () {
            return $this->service->create($this->customer)->getBill()->reference;
        }, range(1, 100));

        $this->assertCount(100, array_unique($references));
    }

    /** @test */
    public function canGetBillView()
    {
        $this->service->create($this->customer);

        $this->service->addAmountInclTax($this->product, 121, 'Some description', 0.21);
        $this->service->addAmountInclTax($this->product, 121, 'Some description', 0.21);
        $view = $this->service->view();
        $rendered = $view->render(); // fails if view cannot be rendered
        $this->assertTrue(true);
    }

    /** @test */
    public function canGetBillPdf()
    {
        $this->service->create($this->customer);
        $this->service->addAmountInclTax($this->product, 121, 'Some description', 0.21);
        $this->service->addAmountInclTax($this->product, 121, 'Some description', 0.21);
        $pdf = $this->service->pdf();  // fails if pdf cannot be rendered
        $this->assertTrue(true);
    }

    /** @test */
    public function canDownloadBillPdf()
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
        $new_bill = $this->service->create($this->customer)->getBill();
        $this->assertEquals($new_bill->id, $this->service->findByReference($new_bill->reference)->id);
    }

    /** @test */
    public function canFindByReferenceOrFail()
    {
        $new_bill = $this->service->create($this->customer)->getBill();
        $this->assertEquals($new_bill->id, $this->service->findByReferenceOrFail($new_bill->reference)->id);
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
        $new_bill = $this->service->create($this->customer)->getBill();
        // Check if correctly set on bill
        $this->assertEquals(CustomerTestModel::class, $new_bill->related_type);
        $this->assertEquals($this->customer->id, $new_bill->related_id);

        // Check if related is accessible
        $this->assertNotNull($new_bill->related);
        $this->assertEquals(CustomerTestModel::class, get_class($new_bill->related));
        $this->assertEquals($this->customer->id, $new_bill->related->id);
    }

    /** @test */
    public function canSaleFree()
    {
        $new_bill = $this->service->create($this->customer)->getBill();

        $this->service->setFree()->addAmountExclTax($this->product, 100, 'Free sale', 0.21);
        $this->service->addAmountExclTax($this->product, 100, 'Some description', 0.21);

        $this->assertEquals('121', (string) $new_bill->total);
        $this->assertEquals('21', (string) $new_bill->tax);
        $this->assertEquals('121', (string) $new_bill->discount);
    }

    /** @test */
    public function canSaleComplimentary()
    {
        $new_bill = $this->service->create($this->customer)->getBill();

        $this->service->setComplimentary()->addAmountExclTax($this->product, 100, 'Complimentary sale', 0.21);
        $this->service->addAmountExclTax($this->product, 100, 'Some description', 0.21);

        $this->assertEquals('121', (string) $new_bill->total);
        $this->assertEquals('21', (string) $new_bill->tax);
        $this->assertEquals('121', (string) $new_bill->discount);
    }

    /** @test */
    public function canSaleComplimentaryAndFree()
    {
        $new_bill = $this->service->create($this->customer)->getBill();

        $this->service->setComplimentary()->addAmountExclTax($this->product, 100, 'Complimentary sale', 0.21);
        $this->service->setFree()->addAmountInclTax($this->product, 121, 'Free sale', 0.21);

        $this->assertEquals('0', (string) $new_bill->total);
        $this->assertEquals('0', (string) $new_bill->tax);
        $this->assertEquals('242', (string) $new_bill->discount);
    }

    /** @test */
    public function canSaleComplimentaryAndFreeAndRegular()
    {
        $new_bill = $this->service->create($this->customer)->getBill();

        $this->service->setComplimentary()->addAmountExclTax($this->product, 100, 'Complimentary sale', 0.21);
        $this->service->setFree()->addAmountInclTax($this->product, 121, 'Free sale', 0.21);
        $this->service->addAmountInclTax($this->product, 121, 'Regular sale', 0.21);

        $this->assertEquals('121', (string) $new_bill->total);
        $this->assertEquals('21', (string) $new_bill->tax);
        $this->assertEquals('242', (string) $new_bill->discount);
    }
}
