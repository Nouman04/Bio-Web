<?php

namespace App\Notifications;

/**
 * Every quiz notification is stored so it can be listed in-app, and pushed
 * live when broadcasting is actually configured.
 *
 * The broadcast channel is added conditionally on purpose: with no Pusher
 * credentials the driver throws, and a missing live update must never take a
 * quiz submission down with it.
 */
trait SendsOverPusher
{
    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->broadcastingConfigured()) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    private function broadcastingConfigured(): bool
    {
        $driver = config('broadcasting.default');

        return match ($driver) {
            'pusher' => filled(config('broadcasting.connections.pusher.key'))
                && filled(config('broadcasting.connections.pusher.secret')),
            'reverb' => filled(config('broadcasting.connections.reverb.key')),
            'ably' => filled(config('broadcasting.connections.ably.key')),
            'log' => true,
            default => false,
        };
    }
}
