<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LogModel extends Eloquent  {
    protected $table = 'northplay_logs';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'type',
        'uuid',
    ];
    protected $casts = [
        'data' => 'json',
        'extra_data' => 'json',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    public static function log_count() {
        $value = Cache::get('logmodel:log_count');
        if(!$value) {
            Cache::set('logmodel:log_count', self::count(), 600);
            return self::count();
        }
        return $value;
    }
    public static function auto_clean()
    {
        LogModel::truncate();
        Cache::forget('logmodel:log_count');
        Log::notice('Truncated datalogger collection automatic because surpassed 2000 entries.');
    }

    public static function save_log($type, $data, $extra_data = NULL)
    {
        if(self::log_count() > 2000) {
            self::auto_clean();
        }

        $data ??= [];
        $data = morph_array($data);
        $extra_data ??= [];
        $extra_data = morph_array($extra_data);
        $logger = new LogModel();
        $logger->type = $type;
		$logger->uuid = Str::orderedUuid();
		$logger->data = $data;
        $logger->extra_data = $extra_data;
		$logger->timestamps = true;
		$logger->save();
        Log::debug('[Northplay Logger] - '.$type.': '.json_encode($data, JSON_PRETTY_PRINT));
    }
}
