<?php
namespace NeptuneSoftware\Invoicable\Interfaces;

use Illuminate\Database\Eloquent\Model;
use NeptuneSoftware\Invoicable\Models\Bill;
use Symfony\Component\HttpFoundation\Response;

interface BillServiceInterface
{
    /**
     * Generate bill referencing Eloquent model.
     *
     * @param Model $model      Invoicable Eloquent model
     * @param array|null $bill  Bill attributes
     * @return $this
     */
    public function create(Model $model, ?array $bill = []): self;

    /**
     * Get bill model.
     *
     * @return Bill
     */
    public function getBill(): Bill;

    /**
     * Set reference invoice line model.
     *
     * @param Model $model Eloquent model.
     * @return $this
     */
    public function setInvoiceable(Model $model): self;

    /**
     * Use this if the amount does not yet include tax.
     * @param Int $amount The amount in cents, excluding taxes
     * @param String $description The description
     * @param int $taxPercentage The tax percentage (i.e. 0.21). Defaults to 0
     * @return Bill This instance after recalculation
     */
    public function addAmountExclTax($amount, $description, $taxPercentage = 0): Bill;

    /**
     * Use this if the amount already includes tax.
     * @param Int $amount The amount in cents, including taxes
     * @param String $description The description
     * @param int $taxPercentage The tax percentage (i.e. 0.21). Defaults to 0
     * @return Bill This instance after recalculation
     */
    public function addAmountInclTax($amount, $description, $taxPercentage = 0): Bill;

    /**
     * Recalculates total and tax based on lines
     * @return Bill This instance
     */
    public function recalculate(): Bill;

    /**
     * Get the View instance for the invoice.
     *
     * @param  array  $data
     * @return \Illuminate\View\View
     */
    public function view(array $data = []): \Illuminate\Contracts\View\View;

    /**
     * Capture the invoice as a PDF and return the raw bytes.
     *
     * @param  array  $data
     * @return string
     */
    public function pdf(array $data = []): string;

    /**
     * Create an invoice download response.
     *
     * @param  array  $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(array $data = []): Response;

    /**
     * Find invoice model.
     *
     * @param string $reference
     * @return Bill|null
     */
    public function findByReference(string $reference): ?Bill;

    /**
     * Find or fail invoice model.
     *
     * @param string $reference
     * @return Bill
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByReferenceOrFail(string $reference): Bill;
}
