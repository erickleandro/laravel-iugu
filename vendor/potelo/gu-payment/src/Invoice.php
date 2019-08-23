<?php

namespace Potelo\GuPayment;

use Dompdf\Dompdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Iugu_Invoice;
use Symfony\Component\HttpFoundation\Response;

class Invoice
{
    /**
     * The user instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $user;

    /**
     * The Iugu invoice instance.
     *
     * @var \Iugu_Invoice
     */
    protected $invoice;

    /**
     * Create a new invoice instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model $user
     * @param  Iugu_Invoice $invoice
     */
    public function __construct($user, Iugu_Invoice $invoice)
    {
        $this->user = $user;
        $this->invoice = $invoice;
    }

    /**
     * Get a Carbon date for the invoice.
     *
     * @param  \DateTimeZone|string  $timezone
     * @return \Carbon\Carbon
     */
    public function date($timezone = null)
    {
        $carbon = Carbon::createFromFormat('Y-m-d', $this->invoice->due_date);

        return $timezone ? $carbon->setTimezone($timezone) : $carbon;
    }

    /**
     * Get the total amount that was paid (or will be paid).
     *
     * @return string
     */
    public function total()
    {
        $total = $this->invoice->total;

        return $total;
    }


    /**
     * Determine if the invoice has a discount.
     *
     * @return bool
     */
    public function hasDiscount()
    {
        return ! is_null($this->invoice->discount);
    }


    /**
     * Get the View instance for the invoice.
     *
     * @param  array  $data
     * @return \Illuminate\View\View
     */
    public function view(array $data)
    {
        return View::make(
            'gu-payment::receipt',
            array_merge($data, ['invoice' => $this, 'user' => $this->user])
        );
    }

    /**
     * Capture the invoice as a PDF and return the raw bytes.
     *
     * @param  array  $data
     * @return string
     */
    public function pdf(array $data)
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
     * Create an invoice download response.
     *
     * @param  array   $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(array $data)
    {
        $filename = $data['product'].'_'.$this->date()->month.'_'.$this->date()->year.'.pdf';

        return new Response($this->pdf($data), 200, [
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Get the Iugu invoice instance.
     *
     * @return Iugu_Invoice
     */
    public function asIuguInvoice()
    {
        return $this->invoice;
    }

    /**
     * Dynamically get values from the Iugu invoice.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->invoice->{$key};
    }
}
