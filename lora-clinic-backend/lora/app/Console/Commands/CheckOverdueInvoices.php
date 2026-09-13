<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Enums\NotificationType;
use App\Models\Invoice;
use App\Models\Notification;
use App\Notifications\PaymentOverdueReminder;
use Illuminate\Console\Command;

/**
 * يبحث يوميًا عن الفواتير التي تجاوزت تاريخ الاستحقاق ولم تُسدَّد بالكامل بعد،
 * يحوّل حالتها إلى "متأخرة"، وينشئ تنبيه "دفع متأخر" (مرة واحدة فقط لكل فاتورة).
 */
class CheckOverdueInvoices extends Command
{
    protected $signature = 'invoices:check-overdue';

    protected $description = 'وسم الفواتير المتأخرة عن السداد وإنشاء تنبيهات لها';

    public function handle(): int
    {
        $overdueInvoices = Invoice::with('patient')
            ->whereDate('due_date', '<', now()->toDateString())
            ->where('remaining_amount', '>', 0)
            ->where('status', '!=', InvoiceStatus::Paid->value)
            ->get();

        $flagged = 0;

        foreach ($overdueInvoices as $invoice) {
            if ($invoice->status !== InvoiceStatus::Overdue) {
                $invoice->update(['status' => InvoiceStatus::Overdue->value]);
            }

            $alreadyNotified = Notification::where('related_invoice_id', $invoice->id)
                ->where('type', NotificationType::PaymentOverdue->value)
                ->exists();

            if (! $alreadyNotified) {
                Notification::create([
                    'type' => NotificationType::PaymentOverdue,
                    'title' => "فاتورة {$invoice->invoice_number} متأخرة - {$invoice->patient->name} لم يسدد بعد",
                    'related_patient_id' => $invoice->patient_id,
                    'related_invoice_id' => $invoice->id,
                    'event_date' => $invoice->due_date,
                ]);

                $invoice->patient->notify(new PaymentOverdueReminder($invoice));
                $flagged++;
            }
        }

        $this->info("تم فحص الفواتير المتأخرة، عدد التنبيهات الجديدة: {$flagged}");

        return self::SUCCESS;
    }
}
