<?php

namespace ElementContextYT;

use YOOtheme\Builder;

include_once __DIR__ . '/src/ContextTransform.php';
include_once __DIR__ . '/src/EventsListener.php';

return [
    'events' => [
        'builder.type' => [
            EventsListener::class => [
                ['@onBuilderType'],
            ],
        ],
        'customizer.init' => [
			EventsListener::class => [
				['@onCustomizerInit'],
			]
		],
    ],
    'extend' => [
        Builder::class => function (Builder $builder, $app) {
            if (!(apply_filters('element_context_yt_disable', false))) {
                $builder->addTransform('render', $app(ContextTransform::class));
            }
        }
    ]
];
