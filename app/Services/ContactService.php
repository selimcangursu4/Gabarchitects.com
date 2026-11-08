<?php

namespace App\Services;

use App\Enums\ContactStatus;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class ContactService
{
    /**
     * Mesajı kaydeder ve bildirim e-postası gönderir.
     * E-posta gönderilemezse mesaj yine kaydedilir (kayıt kaybolmaz), hata loglanır.
     */
    public function submit(array $data, Request $request): ContactMessage
    {
        $message = ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'privacy_consent' => true,
            'consented_at' => now(),
            'status' => ContactStatus::New,
            'source' => $data['source'],
            'property_id' => $data['property_id'] ?? null,
            'source_url' => Str::limit((string) url()->previous(), 250, ''),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 490, ''),
        ]);

        $recipient = settings('contact.notification_email') ?: config('mail.from.address');

        if ($recipient) {
            try {
                Mail::to($recipient)->send(new ContactMessageReceived($message));
            } catch (Throwable $e) {
                Log::error('İletişim bildirimi gönderilemedi', ['contact_message_id' => $message->id, 'error' => $e->getMessage()]);
            }
        }

        return $message;
    }
}
