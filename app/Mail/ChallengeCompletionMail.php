<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChallengeCompletionMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<int,array{day:int,label:string,is_late:bool}> $days  Checklist tất cả các ngày
     */
    public function __construct(
        public string $userName,
        public string $challengeTitle,
        public string $brandName,
        public array $days,
        public string $completedAt,
        public string $challengeUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '🏆 Chúc mừng bạn đã hoàn thành ' . $this->challengeTitle);
    }

    public function content(): Content
    {
        $lateCount = collect($this->days)->where('is_late', true)->count();

        return new Content(
            view: 'emails.challenge-completion',
            with: ['lateCount' => $lateCount, 'totalDays' => count($this->days)],
        );
    }
}
