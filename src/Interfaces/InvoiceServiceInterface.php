<?php


namespace NeptuneSoftware\Invoicable\Interfaces;

use Illuminate\Database\Eloquent\Model;
use NeptuneSoftware\Invoicable\Models\Invoice;
use Symfony\Component\HttpFoundation\Response;

interface InvoiceServiceInterface
{
    /**
     * Generate invoice referencing Eloquent model.
     *
     * @param Model $model        Eloquent model.
     * @param array|null $invoice Invoice attributes.
     * @return InvoiceServiceInterface
     */
    public function create(Model $model, ?array $invoice = []): InvoiceServiceInterface;

    /**
     * Get invoice model.
     *
     * @return Invoice
     */
    public function getInvoice(): Invoice;

    /**
     * Use this if the amount does not yet include tax.
     *
     * @param Model  $model          Set reference invoice line model
     * @param Int    $amount         The amount in cents, excluding taxes
     * @param String $description    The description
     * @param float  $taxPercentage  The tax percentage (i.e. 0.21). Defaults to 0
     * @return Invoice This instance after recalculation
     */
    public function addAmountExclTax(Model $model, int $amount, string $description, float $taxPercentage = 0): Invoice;

    /**
     * Use this if the amount already includes tax.
     *
     * @param Model  $model          Set reference invoice line model
     * @param Int    $amount         The amount in cents, excluding taxes
     * @param String $description    The description
     * @param float  $taxPercentage  The tax percentage (i.e. 0.21). Defaults to 0
     * @return Invoice This instance after recalculation
     */
    public function addAmountInclTax(Model $model, int $amount, string $description, float $taxPercentage = 0): Invoice;

    /**
     * Recalculates total and tax based on lines
     * @return Invoice This instance
     */
    public function recalculate(): Invoice;

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
     * @return Invoice|null
     */
    public function findByReference(string $reference): ?Invoice;

    /**
     * Find or fail invoice model.
     *
     * @param string $reference
     * @return Invoice
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByReferenceOrFail(string $reference): Invoice;
}
