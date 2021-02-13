<?php
namespace Soap\Invoices\Interfaces;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Soap\Invoice\Models\Invoice;
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
     * Get invoice lines.
     *
     * @return Collection
     */
    public function getLines(): Collection;

    /**
     * Set next line free sale.
     *
     * @return InvoiceServiceInterface
     */
    public function setFree(): InvoiceServiceInterface;

    /**
     * Set next line complimentary sale.
     *
     * @return InvoiceServiceInterface
     */
    public function setComplimentary(): InvoiceServiceInterface;

    /**
     * Add percentage tax for an invoice line.
     *
     * @param string $identifier
     * @param float $taxPercentage
     * @return InvoiceServiceInterface
     */
    public function addTaxPercentage(string $identifier, float $taxPercentage = 0): InvoiceServiceInterface;

    /**
     * Add fixed tax for an invoice line.
     *
     * @param string $identifier
     * @param int $taxFixed
     * @return InvoiceServiceInterface
     */
    public function addTaxFixed(string $identifier, int $taxFixed = 0): InvoiceServiceInterface;

    /**
     * Use this if the amount does not yet include tax.
     *
     * @param Model  $model          Set reference invoice line model
     * @param Int    $amount         The amount in cents, excluding taxes
     * @param String $description    The description
     * @return self This instance after recalculation
     */
    public function addAmountExclTax(Model $model, int $amount, string $description): self;

    /**
     * Use this if the amount already includes tax.
     *
     * @param Model  $model          Set reference invoice line model
     * @param Int    $amount         The amount in cents, excluding taxes
     * @param String $description    The description
     * @return self This instance after recalculation
     */
    public function addAmountInclTax(Model $model, int $amount, string $description): self;

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
    public function view(array $data = []): View;

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

    /**
     * Find invoices model by invoiceLines.
     *
     * @param Model $model
     * @return Collection
     */
    public function findByInvoiceable(Model $model): Collection;

    /**
     * Find invoices model by related.
     *
     * @param Model $model
     * @return Collection
     */
    public function findByRelated(Model $model): Collection;
}
