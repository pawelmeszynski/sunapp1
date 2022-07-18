<?php

namespace SunAppModules\Core\Entities;

use Auth;
use Bouncer;
use File;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class Log extends OfflineModel
{
    protected $table = '';
    protected $namespace = 'core::logs';
    protected $actions = ['show'];

    protected static $data = [];

    private static $config;
    private static $files;

    public function __construct(array $attributes = [])
    {
        self::$config = config('log-viewer', []);

        parent::__construct($attributes);
    }

    public function getIdAttribute()
    {
        return $this->original['id'];
    }

    public function getMetaParams()
    {
        return [
            "counter" => [
                'all' => count(self::$data)
            ]
        ];
    }

    /**
     * Prepares data structure for laravel log type
     *
     * @param $file
     * @param $type
     * @return array|void
     */
    protected static function pregLaravelType($file, $type)
    {
        $patterns = !empty(self::$config['patterns'][$type]) ? self::$config['patterns'][$type] : false;

        if (!$patterns) {
            return;
        }

        preg_match_all('/' . $patterns['log'] . '/', $file->getContents(), $logs);

        // if no logs then return
        if (empty($logs[0])) {
            return;
        }

        foreach ($logs[0] as $i => $log) {
            $pattern = $patterns['date'] . $patterns['error'] . $patterns['detail'];

            $data = null;
            preg_match('/^' . $pattern . '$/i', trim($log), $data);

            if (empty($data[0])) {
                continue;
            }

            $return['laravel'][] = [
                'id' => ++$i,
                'date' => $data[1],
                'context' => $data[3],
                'error' => $data[4],
                'detail' => $data[5]
            ];
        }

        return isset($return) ? array_reverse($return) : false;
    }

    /**
     * Returns prepared file content or raw data
     *
     * @param $file
     * @return array
     */
    protected static function getFileContent($file)
    {
        $content = $file->getContents();

        if (empty($content)) {
            return [
                'raw' => trans('core::logs.empty_file')
            ];
        }

        $patterns = !empty(self::$config['patterns']) ? self::$config['patterns'] : [];
        $type = self::getFileType($file);

        if (!empty($patterns[$type])) {
            $method = 'preg' . ucfirst(str_replace('.log', '', $type))  . 'Type';
            if (method_exists(self::class, $method)) {
                if ($return = self::$method($file, $type)) {
                    return $return; // return arrayed content
                }
            }
        }

        return [
            'raw' => nl2br($content) // return raw content
        ];
    }

    /**
     * Returns file log type based on filename
     * eg.: 'laravel-2021-05-05.log => laravel.log'
     *
     * @param $file
     * @return string
     */
    protected static function getFileType($file)
    {
        $types = !empty(self::$config['types']) ? self::$config['types'] : [];
        $name = trim($file->getFilename());

        foreach ($types as $type => $pattern) {
            if (preg_match('/^(' . $pattern . ')$/', $name) === 1) {
                return $type; // return type eg.: 'laravel-2021-05-05.log => laravel.log'
            }
        }
        return $name; // return full filename
    }

    /**
     * Loops through file's array checking if file log should be displayed
     *
     * @return void
     */
    protected static function validateAccess()
    {
        $visible = env('LOG_VISIBLE', !empty(self::$config['visible']) ? static::$config['visible'] : []);
        $visible = (is_string($visible) && $visible !== 'all') ? explode(',', $visible) : $visible;
        $user = Auth::user();

        // return full list of logs if user is a superadmin or config allows all logs
        if ($user->superadmin || (is_string($visible) && $visible === 'all')) {
            return;
        }

        foreach (self::$files as $key => $file) {
            $type = self::getFileType($file);
            if ((is_array($visible) && in_array($type, $visible)) || $visible === $type) {
                continue;
            }
            unset(self::$files[$key]);
        }
    }

    /**
     * Returns all log files
     *
     * @param array|mixed|string[] $columns
     * @return Collection|\Illuminate\Support\Collection|OfflineModel[]
     * @throws \Exception
     */
    public static function all($columns = ['*'])
    {
        if (self::$data) {
            return Collection::make(self::hydrate(self::$data));
        }

        $path = !empty(static::$config['path']) ? static::$config['path'] : storage_path('logs/');

        try {
            self::$files = File::allfiles($path); // returns [] when path is empty
        } catch (\Exception $e) {
            throw new \Exception('Path: ' . addslashes($path) . ' doesn\'t exist.');
        }

        // check if file exists, is readable and it's extension is *.log if not then removes
        self::$files = array_filter(self::$files, function ($file) {
            return (is_readable($file->getPath()) && $file->getExtension() === 'log');
        });

        if (empty(self::$files)) {
            return collect([]);
        }

        // validate access privilege to check if file log can be displayed
        self::validateAccess();

        if (empty(self::$files)) {
            return collect([]);
        }

        // reset array keys
        self::$files = array_values(self::$files);

        foreach (self::$files as $i => $file) {
            self::$data[] = [
                'id' => ++$i,
                'name' => $file->getFilename(),
                'content' => self::getFileContent($file),
                'size' => round($file->getSize() / 1024, 2) . 'KB',
                'updated_at' => new Carbon($file->getMTime())
            ];
        }

        // reverse
        self::$data = array_reverse(self::$data);

        return Collection::make(self::hydrate(self::$data));
    }
}
