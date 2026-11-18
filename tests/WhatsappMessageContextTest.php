<?php

use DefStudio\Whatsapper\Events\WhatsappMessageReceived;
use DefStudio\Whatsapper\Facades\Whatsapper;

it('only builds a reply context when sender and message id are present', function (array $content, ?array $context) {
    if ($content['type'] === 'image') {
        Whatsapper::shouldReceive('shouldStoreImages')->once()->andReturnFalse();
    }

    $data = array_merge([
        'id' => 'wamid.incoming',
        'from' => '390000000000',
        'from_user_id' => 'IT.test-sender',
        'timestamp' => '1794991031',
    ], $content);

    if ($context !== null) {
        $data['context'] = $context;
    }

    $event = new WhatsappMessageReceived($data, null, null, [], []);

    expect($event->message->id())->toBe('wamid.incoming')
        ->and($event->messageData)->toBe($data);

    if (isset($context['from'], $context['id'])) {
        expect($event->message->context()->from)->toBe($context['from'])
            ->and($event->message->context()->messageId)->toBe($context['id']);
    } else {
        expect($event->message->context())->toBeNull();
    }
})->with([
    'text' => [['type' => 'text', 'text' => ['body' => 'Hello']]],
    'image' => [['type' => 'image', 'image' => [
        'id' => 'media.test',
        'url' => 'https://example.com/image.jpg',
        'mime_type' => 'image/jpeg',
    ]]],
])->with([
    'no context' => [null],
    'empty context' => [[]],
    'forwarded' => [['forwarded' => true]],
    'frequently forwarded' => [['forwarded' => true, 'frequently_forwarded' => true]],
    'missing sender' => [['id' => 'wamid.original']],
    'missing message id' => [['from' => '390000000001']],
    'null sender' => [['from' => null, 'id' => 'wamid.original']],
    'null message id' => [['from' => '390000000001', 'id' => null]],
    'reply' => [['from' => '390000000001', 'id' => 'wamid.original']],
    'reply with forwarding metadata' => [['from' => '390000000001', 'id' => 'wamid.original', 'forwarded' => true]],
]);
