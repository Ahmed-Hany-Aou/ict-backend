<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function handleAIResult(Request $request)
    {
        // n8n sends { payload: { ... } }
        $data = $request->input('payload', $request->all());

        // 1. Locate payment
        $paymentId = data_get($data, 'payment_id');
        $payment   = Payment::findOrFail($paymentId);

        // 2. Basic AI fields
        $aiRecommendation   = data_get($data, 'recommendation');   // "approve" / "review" / "reject"
        $aiConfidence       = (float) data_get($data, 'confidence_score', 0);
        $aiReason           = data_get($data, 'reason');
        $extracted          = (array) data_get($data, 'extracted_data', []);

        // 3. Extracted fields (raw from AI)
        $extractedAmount        = isset($extracted['amount']) ? (float) $extracted['amount'] : null;
        $extractedDateRaw       = $extracted['date']            ?? null; // "YYYY-MM-DDTHH:MM:SS"
        $extractedTxnId         = $extracted['transaction_id']  ?? null;
        $extractedSender        = trim(strtolower($extracted['sender_account']   ?? ''));
        $extractedRecipient     = trim(strtolower($extracted['recipient_account'] ?? ''));

        // 4. Business constants
        $expectedAmount     = (float) config('pricing.premium.discounted_price');   // your official price
        $officialRecipient  = 'ahmedhanycib@instapay';                              // your Instapay handle

        // 5. Amount match (exact)
        $amountMatch = $extractedAmount !== null
            ? abs($extractedAmount - $expectedAmount) < 0.001
            : false;

        // 6. Date recency (within last 24 hours)
        $dateIsRecent = false;
        $parsedDate   = null;

        if ($extractedDateRaw) {
            try {
                // Let Carbon parse ISO string; assume your app timezone
                $parsedDate   = Carbon::parse($extractedDateRaw, config('app.timezone'));
                $dateIsRecent = $parsedDate->greaterThanOrEqualTo(now()->subHours(24));
            } catch (\Throwable $e) {
                $dateIsRecent = false;
            }
        }

        // 7. Recipient must be EXACT official handle
        $recipientOk = $extractedRecipient === strtolower($officialRecipient);

        // 8. Sender must end with "@instapay"
        $senderOk = $extractedSender !== '' && str_ends_with($extractedSender, '@instapay');

        // 9. Duplicate transaction ID check
        $duplicateTxn = false;
        if ($extractedTxnId) {
            $duplicateTxn = Payment::where('transaction_id_extracted', $extractedTxnId)
                ->where('id', '!=', $payment->id)
                ->exists();
        }

        // 10. Optional: compare with user-entered transaction id (if you store it)
        $userProvidedTxn   = $payment->transaction_id_user ?? null;  // change to your column name
        $txnMismatchWithUser = $userProvidedTxn && $extractedTxnId && $userProvidedTxn !== $extractedTxnId;

        // 11. Collect human-readable reasons
        $reasons = [];

        if (!$amountMatch) {
            $reasons[] = sprintf(
                'Amount mismatch: expected %.2f EGP, extracted %s.',
                $expectedAmount,
                $extractedAmount !== null ? number_format($extractedAmount, 2) : 'NULL'
            );
        }

        if (!$recipientOk) {
            $reasons[] = sprintf(
                'Recipient handle mismatch: expected %s, extracted %s.',
                $officialRecipient,
                $extractedRecipient ?: 'NULL'
            );
        }

        if (!$senderOk) {
            $reasons[] = 'Sender account does not look like a valid Instapay handle (must end with @instapay).';
        }

        if (!$dateIsRecent) {
            $reasons[] = 'Payment date/time is older than 24 hours or could not be parsed.';
        }

        if ($duplicateTxn) {
            $reasons[] = 'Duplicate transaction ID detected – this ID already exists for another payment.';
        }

        if ($txnMismatchWithUser) {
            $reasons[] = 'Transaction ID in screenshot does not match the ID entered by the user.';
        }

        // 12. Decide final status (Laravel is the boss)

        // Hard fails -> always REJECT
        $hardFail = !$amountMatch || !$recipientOk || $duplicateTxn;

        // Soft fails -> keep PENDING for manual review
        $softFail = !$hardFail && ($txnMismatchWithUser || !$dateIsRecent || !$senderOk);

        if ($hardFail) {
            $finalStatus = 'rejected';
        } elseif ($softFail) {
            $finalStatus = 'pending'; // admin must review
        } else {
            $finalStatus = 'approved';
        }

        // 13. Build admin notes (AI reason + backend reasons)
        $backendReason = $reasons ? implode(' ', $reasons) : null;

        $combinedNotes = trim(implode(' ', array_filter([
            $payment->admin_notes ?? null,
            $backendReason,
            $aiReason ? 'AI note: ' . $aiReason : null,
        ])));

        // 14. Persist to DB
        $payment->update([
            'status'                => $finalStatus,
            'is_ai_verified'        => true,
            'ai_confidence_score'   => $aiConfidence,
            'ai_recommendation'     => $aiRecommendation, // Ensure this column exists or remove if not needed
            'ai_analysis_result'    => $data,                    // full JSON from n8n
            'transaction_id_extracted' => $extractedTxnId,
            'ai_reviewed_at'        => now(),
            'admin_notes'           => $combinedNotes,
        ]);

        // 15. Respond back to n8n
        return response()->json([
            'success'       => true,
            'payment_id'    => $payment->id,
            'final_status'  => $payment->status,
            'amount_match'  => $amountMatch,
            'date_is_recent'=> $dateIsRecent,
            'recipient_ok'  => $recipientOk,
            'sender_ok'     => $senderOk,
            'duplicate_txn' => $duplicateTxn,
            'txn_mismatch_with_user' => $txnMismatchWithUser,
            'message'       => $backendReason ?: 'Payment evaluated successfully.',
        ]);
    }
}
