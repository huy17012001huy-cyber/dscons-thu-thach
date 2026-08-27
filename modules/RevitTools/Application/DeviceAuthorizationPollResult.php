<?php

declare(strict_types=1);

namespace Modules\RevitTools\Application;

use App\Models\ToolSession;

final readonly class DeviceAuthorizationPollResult
{
    public function __construct(
        public string $status,
        public ?string $credential = null,
        public ?ToolSession $session = null,
    ) {}

    /** @return array{credential:string,session:ToolSession}|null */
    public function approval(): ?array
    {
        if ($this->status !== 'approved' || ! $this->credential || ! $this->session) {
            return null;
        }

        return ['credential' => $this->credential, 'session' => $this->session];
    }
}
