<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_reference',
        'screenshot_path',
        'amount',
        'status',
        'admin_notes',
        'approved_by',
        'approved_at',
        'is_ai_verified',
        'ai_confidence_score',
        'ai_recommendation',
        'ai_analysis_result',
        'transaction_id_extracted',
        'ai_reviewed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'is_ai_verified' => 'boolean',
        'ai_analysis_result' => 'array',
        'ai_reviewed_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function approve($adminId, $premiumDays = 30)
    {
        $this->status = 'approved';
        $this->approved_by = $adminId;
        $this->approved_at = now();
        $this->save();

        // Activate premium for the user
        $this->user->activatePremium($premiumDays);
    }

    public function reject($adminId, $notes = null)
    {
        $this->status = 'rejected';
        $this->approved_by = $adminId;
        $this->approved_at = now();
        if ($notes) {
            $this->admin_notes = $notes;
        }
        $this->save();
    }
}
