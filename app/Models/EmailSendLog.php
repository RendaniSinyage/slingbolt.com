<?php
// app/Models/EmailSendLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmailSendLog extends Model
{
    protected $fillable = [
        'user_id',
        'email_template',
        'user_email',
        'context',
        'sent_successfully',
        'error_message',
        'sent_at'
    ];

    protected $casts = [
        'context' => 'array',
        'sent_successfully' => 'boolean',
        'sent_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if email was already sent to user
     */
    public static function wasEmailSent($userId, $template)
    {
        return self::where('user_id', $userId)
                  ->where('email_template', $template)
                  ->where('sent_successfully', true)
                  ->exists();
    }

    /**
     * Log successful email send
     */
    public static function logSuccess($userId, $template, $userEmail, $context = [])
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'email_template' => $template],
            [
                'user_email' => $userEmail,
                'context' => $context,
                'sent_successfully' => true,
                'error_message' => null,
                'sent_at' => Carbon::now()
            ]
        );
    }

    /**
     * Log failed email send
     */
    public static function logFailure($userId, $template, $userEmail, $errorMessage, $context = [])
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'email_template' => $template],
            [
                'user_email' => $userEmail,
                'context' => $context,
                'sent_successfully' => false,
                'error_message' => $errorMessage,
                'sent_at' => Carbon::now()
            ]
        );
    }

    /**
     * Clear email log for re-sending
     */
    public static function clearLog($userId, $template = null)
    {
        $query = self::where('user_id', $userId);
        
        if ($template) {
            $query->where('email_template', $template);
        }
        
        return $query->delete();
    }
}