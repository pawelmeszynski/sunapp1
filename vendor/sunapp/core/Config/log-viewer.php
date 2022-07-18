<?php

$return = [
    'path' => storage_path('logs/'),
    'types' => [
        'laravel.log' => 'laravel(\-[0-9]{2,4}){3}\.log', // type => check rule
    ],
    'visible' => [
        'laravel.log' // allow log for eveyryone to see
        // worker.log
    ],
    'patterns' => [
        'laravel.log' => [
            'log' => '\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}([\+-]\d{4})?\].*',
            'date' => '\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}([\+-]\d{4})?)\](?:.*?(\w+)\.|.*?)',
            'error' => '([a-zA-Z]+)',
            'detail' => ': (.*?)( in .*?:[0-9]+)?'
        ]
    ]
    //visible => 'all',
];

$channels = env('LOG_CHANNEL_LIST');
if ($channels) {
    $channels = explode(',', trim($channels));
    $types = [];

    foreach ($channels as $channel) {
        $channel = env(trim($channel));

        if (
            $channel
            && ($logging = config('logging.channels.' . $channel))
        ) {
            if (isset($logging['driver']) && isset($logging['path'])) {
                $file = trim(str_replace($return['path'], '', $logging['path']));
                if ($logging['driver'] == 'daily') {
                    $pattern = str_replace('.log', '(\-[0-9]{2,4}){3}\.log', $file);
                    $types = array_merge($types, [$file => $pattern]);
                } else {
                    $types = array_merge($types, [$file]);
                }
            }
        }
    }
    $return['types'] = !empty($types) ? $types : $return['types'];
}

return $return;
