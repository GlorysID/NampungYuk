<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SpaceSignal implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public int $spaceId,
        public int $from,
        public ?int $to,
        public string $kind, // offer | answer | ice | join | leave
        public array $payload = [],
    ) {
        //
    }

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('space.'.$this->spaceId)];
    }

    public function broadcastAs(): string
    {
        return 'space.signal';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
            'kind' => $this->kind,
            'payload' => $this->payload,
        ];
    }
}
