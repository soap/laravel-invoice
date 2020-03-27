<?php


namespace NeptuneSoftware\Invoicable\Services;

use Dompdf\Dompdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use NeptuneSoftware\Invoicable\Models\Bill;
use NeptuneSoftware\Invoicable\MoneyFormatter;
use NeptuneSoftware\Invoicable\Scopes\InvoiceScope;
use NeptuneSoftware\Invoicable\Interfaces\BillServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class BillService implements BillServiceInterface
{
    /**
     * @var Bill $bill
     */
    private $bill;

    /**
     * @var bool $is_free
     */
    private $is_free = false;

    /**
     * @var bool $is_comp
     */
    private $is_comp = false;

    /**
     * @inheritDoc
     */
    public function create(Model $model, ?array $bill = []): BillServiceInterface
    {
        $this->bill = $model->bills()->create($bill);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBill(): Bill
    {
        return $this->bill;
    }

    /**
     * @inheritDoc
     */
    public function getLines(): Collection
    {
        return $this->getBill()->lines();
    }

    /**
     * @inheritDoc
     */
    public function setFree(): BillServiceInterface
    {
        $this->is_free = true;
        $this->is_comp = false;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setComplimentary(): BillServiceInterface
    {
        $this->is_comp = true;
        $this->is_free = false;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addAmountExclTax(Model $model, int $amount, string $description, float $taxPercentage = 0): BillServiceInterface
    {
        $tax = $amount * $taxPercentage;

        $this->bill->lines()->create([
            'amount'           => $amount + $tax,
            'description'      => $description,
            'tax'              => $tax,
            'tax_percentage'   => $taxPercentage,
            'invoiceable_id'   => $model->id,
            'invoiceable_type' => get_class($model),
            'is_free'          => $this->is_free,
            'is_complimentary' => $this->is_comp,
        ]);

        $this->is_free = false;
        $this->is_comp = false;

        $this->recalculate();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addAmountInclTax(Model $model, int $amount, string $description, float $taxPercentage = 0): BillServiceInterface
    {
        $this->bill->lines()->create([
            'amount'           => $amount,
            'description'      => $description,
            'tax'              => $amount - $amount / (1 + $taxPercentage),
            'tax_percentage'   => $taxPercentage,
            'invoiceable_id'   => $model->id,
            'invoiceable_type' => get_class($model),
            'is_free'          => $this->is_free,
            'is_complimentary' => $this->is_comp,
        ]);

        $this->is_free = false;
        $this->is_comp = false;

        $this->recalculate();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function recalculate(): Bill
    {
        $free          = $this->bill->lines()->where('is_free', true)->get();
        $complimentary = $this->bill->lines()->where('is_complimentary', true)->get();
        $other         = $this->bill->lines()
                                       ->where('is_free', false)
                                       ->where('is_complimentary', false)
                                       ->get();

        $this->bill->total    = $other->sum('amount');
        $this->bill->tax      = $other->sum('tax');
        $this->bill->discount = $free->sum('amount') + $complimentary->sum('amount') + $other->sum('discount');

        $this->bill->save();
        return $this->bill;
    }

    /**
     * @inheritDoc
     */
    public function view(array $data = []): \Illuminate\Contracts\View\View
    {
        return View::make('invoicable::receipt', array_merge($data, [
            'invoice' => $this->bill,
            'moneyFormatter' => new MoneyFormatter(
                $this->bill->currency,
                config('invoicable.locale')
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
        $filename = $this->bill->reference . '.pdf';

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
    public function findByReference(string $reference): ?Bill
    {
        return Bill::where('reference', $reference)->withoutGlobalScope(InvoiceScope::class)->first();
    }

    /**
     * @inheritDoc
     */
    public function findByReferenceOrFail(string $reference): Bill
    {
        return Bill::where('reference', $reference)->withoutGlobalScope(InvoiceScope::class)->firstOrFail();
    }
}
