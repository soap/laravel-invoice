<?php


namespace NeptuneSoftware\Invoice\Services;

use Dompdf\Dompdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use NeptuneSoftware\Invoice\Models\Invoice;
use NeptuneSoftware\Invoice\MoneyFormatter;
use NeptuneSoftware\Invoice\Interfaces\InvoiceServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class InvoiceService implements InvoiceServiceInterface
{
    /**
     * @var Invoice $invoice
     */
    private $invoice;

    /**
     * @var bool $is_free
     */
    private $is_free = false;

    /**
     * @var bool $is_comp
     */
    private $is_comp = false;

    /**
     * @var array $taxes
     */
    private $taxes = [];

    /**
     * @inheritDoc
     */
    public function create(Model $model, ?array $invoice = []): InvoiceServiceInterface
    {
        $this->invoice = $model->invoices()->create($invoice);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    /**
     * @inheritDoc
     */
    public function getLines(): Collection
    {
        return $this->getInvoice()->lines()->get();
    }

    /**
     * @inheritDoc
     */
    public function setFree(): InvoiceServiceInterface
    {
        $this->is_free = true;
        $this->is_comp = false;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setComplimentary(): InvoiceServiceInterface
    {
        $this->is_comp = true;
        $this->is_free = false;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addTaxPercentage(string $identifier, float $taxPercentage = 0): InvoiceServiceInterface
    {
        array_push($this->taxes, [
            'identifier'     => $identifier,
            'tax_fixed'      => null,
            'tax_percentage' => $taxPercentage,
        ]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addTaxFixed(string $identifier, int $taxFixed = 0): InvoiceServiceInterface
    {
        array_unshift($this->taxes, [
            'identifier'     => $identifier,
            'tax_fixed'      => $taxFixed,
            'tax_percentage' => null,
        ]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addAmountExclTax(Model $model, int $amount, string $description): InvoiceServiceInterface
    {
        $tax = 0;
        foreach ($this->taxes as $each) {
            $tax += (null === $each['tax_fixed']) ? $amount * $each['tax_percentage'] : $each['tax_fixed'];
        }

        $this->invoice->lines()->create([
            'amount'           => $amount + $tax,
            'description'      => $description,
            'tax'              => $tax,
            'tax_details'      => $this->taxes,
            'invoiceable_id'   => $model->id,
            'invoiceable_type' => get_class($model),
            'is_free'          => $this->is_free,
            'is_complimentary' => $this->is_comp,
        ]);

        $this->recalculate();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addAmountInclTax(Model $model, int $amount, string $description): InvoiceServiceInterface
    {
        $exc = $amount;
        $tax = 0;
        foreach ($this->taxes as $each) {
            if (null !== $each['tax_fixed']) {
                $exc -= $each['tax_fixed'];
                $tax += $each['tax_fixed'];
            } else {
                $tax += ($exc * $each['tax_percentage']) / (1 + $each['tax_percentage']);
            }
        }

        $this->invoice->lines()->create([
            'amount'           => $amount,
            'description'      => $description,
            'tax'              => $tax,
            'invoiceable_id'   => $model->id,
            'invoiceable_type' => get_class($model),
            'is_free'          => $this->is_free,
            'is_complimentary' => $this->is_comp,
        ]);

        $this->recalculate();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function recalculate(): Invoice
    {
        $lines         = $this->getLines();
        $free          = $lines->where('is_free', true)->toBase();
        $complimentary = $lines->where('is_complimentary', true)->toBase();
        $other         = $lines->where('is_free', false)
                               ->where('is_complimentary', false)
                               ->toBase();

        $this->invoice->total    = $other->sum('amount');
        $this->invoice->tax      = $other->sum('tax');
        $this->invoice->discount = $free->sum('amount') + $complimentary->sum('amount') + $other->sum('discount');

        $this->invoice->save();

        $this->is_free = false;
        $this->is_comp = false;
        $this->taxes   = [];

        return $this->invoice;
    }

    /**
     * @inheritDoc
     */
    public function view(array $data = []): \Illuminate\Contracts\View\View
    {
        return View::make('invoice::receipt', array_merge($data, [
            'invoice' => $this->invoice,
            'moneyFormatter' => new MoneyFormatter(
                $this->invoice->currency,
                config('invoice.locale')
            ),
        ]));
    }

    /**
     * @inheritDoc
     */
    public function pdf(array $data = []): string
    {
        if (! defined('DOMPDF_ENABLE_AUTOLOAD')) {
            define('DOMPDF_ENABLE_AUTOLOAD', false);
        }

        if (file_exists($configPath = base_path().'/vendor/dompdf/dompdf/dompdf_config.inc.php')) {
            require_once $configPath;
        }

        $dompdf = new Dompdf;
        $dompdf->loadHtml($this->view($data)->render());
        $dompdf->render();
        return $dompdf->output();
    }

    /**
     * @inheritDoc
     */
    public function download(array $data = []): Response
    {
        $filename = $this->invoice->reference . '.pdf';

        return new Response($this->pdf($data), 200, [
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findByReference(string $reference): ?Invoice
    {
        return Invoice::where('reference', $reference)->first();
    }

    /**
     * @inheritDoc
     */
    public function findByReferenceOrFail(string $reference): Invoice
    {
        return Invoice::where('reference', $reference)->firstOrFail();
    }
}
