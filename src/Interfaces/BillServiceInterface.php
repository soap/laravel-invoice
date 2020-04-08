<?php
namespace NeptuneSoftware\Invoice\Interfaces;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use NeptuneSoftware\Invoice\Models\Bill;
use Symfony\Component\HttpFoundation\Response;

interface BillServiceInterface
{
    /**
     * Generate bill referencing Eloquent model.
     *
     * @param Model $model      Eloquent model with HasInvoice trait
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
     * Get bill lines.
     *
     * @return Collection
     */
    public function getLines(): Collection;

    /**
     * Set next line free sale.
     *
     * @return BillServiceInterface
     */
    public function setFree(): BillServiceInterface;

    /**
     * Set next line complimentary sale.
     *
     * @return BillServiceInterface
     */
    public function setComplimentary(): BillServiceInterface;

    /**
     * Add percentage tax for an bill line.
     *
     * @param string $identifier
     * @param float $taxPercentage
     * @return BillServiceInterface
     */
    public function addTaxPercentage(string $identifier, float $taxPercentage = 0): BillServiceInterface;

    /**
     * Add fixed tax for an bill line.
     *
     * @param string $identifier
     * @param int $taxFixed
     * @return BillServiceInterface
     */
    public function addTaxFixed(string $identifier, int $taxFixed = 0): BillServiceInterface;

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
     * @return Bill This instance
     */
    public function recalculate(): Bill;

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
