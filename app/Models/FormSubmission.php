<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'form_id',
        'form_data',
        'ip_address',
        'user_agent',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'form_data' => 'array',
        ];
    }

    /**
     * Get the form that owns the submission.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the user who submitted the form.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a specific field value from form data.
     */
    public function getFieldValue(string $fieldName, $default = null)
    {
        return $this->form_data[$fieldName] ?? $default;
    }

    /**
     * Check if the submission was made by a logged-in user.
     */
    public function isFromUser(): bool
    {
        return !is_null($this->user_id);
    }

    /**
     * Get the submitter's name (from user or form data).
     */
    public function getSubmitterNameAttribute(): ?string
    {
        if ($this->user) {
            return $this->user->name;
        }

        return $this->getFieldValue('name') ?: $this->getFieldValue('full_name');
    }

    /**
     * Get the submitter's email (from user or form data).
     */
    public function getSubmitterEmailAttribute(): ?string
    {
        if ($this->user) {
            return $this->user->email;
        }

        return $this->getFieldValue('email');
    }
}
