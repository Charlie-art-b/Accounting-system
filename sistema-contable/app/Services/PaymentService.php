<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * Create a payment for a payable model (AccountReceivable or AccountPayable).
     * Minimal validations: amount > 0 and uses DB transaction + lockForUpdate.
     *
     * @param Model $payable
     * @param float $amount
     * @param string|\DateTime|Carbon|null $paidAt
     * @param string|null $note
     * @param int|null $createdBy
     * @return Payment
     */
    public function createPayment(Model $payable, float $amount, $paidAt = null, ?string $note = null, ?int $createdBy = null): Payment
    {
        return DB::transaction(function () use ($payable, $amount, $paidAt, $note, $createdBy) {
            $payableClass = get_class($payable);

            $locked = $payableClass::where('id', $payable->id)->lockForUpdate()->first();

            if (! $locked) {
                throw new \RuntimeException('Registro no encontrado para el pago.');
            }

            if ($amount <= 0) {
                throw ValidationException::withMessages(['amount' => 'El monto debe ser mayor que 0.']);
            }

            $paidAtObj = $paidAt ? Carbon::parse($paidAt) : Carbon::now();

            // If user provided a date-only value (parsed time is midnight),
            // preserve the date but set the time to now so records show a sensible time.
            if ($paidAt && $paidAtObj->hour === 0 && $paidAtObj->minute === 0 && $paidAtObj->second === 0) {
                $now = Carbon::now();
                $paidAtObj = $paidAtObj->setTime($now->hour, $now->minute, $now->second);
            }

            // Fecha no puede ser futura
            if ($paidAtObj->gt(Carbon::now())) {
                throw ValidationException::withMessages(['paid_at' => 'La fecha de pago no puede ser futura.']);
            }

            // Si tiene issue_date, la fecha no puede ser anterior
            if (! empty($locked->issue_date)) {
                $issue = Carbon::parse($locked->issue_date);
                if ($paidAtObj->lt($issue)) {
                    throw ValidationException::withMessages(['paid_at' => 'La fecha de pago no puede ser anterior a la fecha de emisión.']);
                }
            }

            // Calcular pendiente
            $total = (float) ($locked->total_amount ?? 0);
            $paid = (float) ($locked->paid_amount ?? 0);
            $pending = max(0, $total - $paid);

            if ($amount > $pending) {
                throw ValidationException::withMessages(['amount' => 'El pago excede el monto pendiente. Pendiente: ' . number_format($pending, 2)]);
            }

            // Evitar duplicados exactos (mismo monto, misma fecha y misma nota)
            $noteToCheck = $note ?? '';
            $exists = Payment::where('payable_type', $payableClass)
                ->where('payable_id', $locked->id)
                ->where('amount', $amount)
                ->whereDate('paid_at', $paidAtObj->toDateString())
                ->where('note', $noteToCheck)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages(['duplicate' => 'Pago duplicado: ya existe un pago con el mismo monto, fecha y nota.']);
            }

            // Evitar pagos con fecha anterior al último pago registrado
            $last = Payment::where('payable_type', $payableClass)
                ->where('payable_id', $locked->id)
                ->where('is_reversal', false)
                ->orderBy('paid_at', 'desc')
                ->first();

            if ($last && Carbon::parse($last->paid_at)->gt($paidAtObj)) {
                throw ValidationException::withMessages(['paid_at' => 'La fecha de este pago no puede ser anterior al último pago registrado.']);
            }

            // Create payment record
            $payment = Payment::create([
                'payable_type' => $payableClass,
                'payable_id' => $locked->id,
                'amount' => $amount,
                'paid_at' => $paidAtObj,
                'note' => $note,
                'created_by' => $createdBy ?? auth()->id() ?? null,
            ]);

            // Update payable's paid_amount if attribute exists
            if (isset($locked->paid_amount)) {
                $locked->paid_amount = (float) $locked->paid_amount + $amount;
            }

            // If model has payment_date field, set to paidAt
            if (isset($locked->payment_date)) {
                $locked->payment_date = $paidAtObj->toDateString();
            }

            $locked->save();

            return $payment;
        });
    }

    /**
     * Reverse a previously created payment.
     * Creates a reversal payment record and updates payable paid_amount.
     *
     * @param Payment $payment
     * @param int|null $createdBy
     * @return Payment
     */
    public function reversePayment(Payment $payment, ?int $createdBy = null): Payment
    {
        return DB::transaction(function () use ($payment, $createdBy) {
            if ($payment->is_reversal) {
                throw ValidationException::withMessages(['payment' => 'No se puede deshacer un pago que ya es reverso.']);
            }

            // Check if already reversed
            $already = Payment::where('reversed_payment_id', $payment->id)->exists();
            if ($already) {
                throw ValidationException::withMessages(['payment' => 'Este pago ya fue revertido anteriormente.']);
            }

            $payableClass = $payment->payable_type;
            $locked = $payableClass::where('id', $payment->payable_id)->lockForUpdate()->first();

            if (! $locked) {
                throw new \RuntimeException('Registro no encontrado para reverso.');
            }

            $reverseAmount = (float) $payment->amount;

            // Create reversal payment (amount as negative to reflect subtraction)
            $reversal = Payment::create([
                'payable_type' => $payment->payable_type,
                'payable_id' => $payment->payable_id,
                'amount' => -1 * $reverseAmount,
                'paid_at' => now(),
                'note' => 'Reverso de pago #' . $payment->id . ($payment->note ? ': ' . $payment->note : ''),
                'is_reversal' => true,
                'reversed_payment_id' => $payment->id,
                'created_by' => $createdBy ?? auth()->id() ?? null,
            ]);

            // Subtract from payable paid_amount
            if (isset($locked->paid_amount)) {
                $locked->paid_amount = max(0, (float) $locked->paid_amount - $reverseAmount);
            }

            // If payment_date exists and no paid_amount left, clear
            if (isset($locked->payment_date) && (float) $locked->paid_amount <= 0) {
                $locked->payment_date = null;
            }

            $locked->save();

            return $reversal;
        });
    }
}
