<?php

return [
    'post.created' => [
        \Tests\Stubs\DispatchCounter::class,
    ],
    'post.*' => [
        \Tests\Stubs\DispatchCounter::class,
    ],
];
