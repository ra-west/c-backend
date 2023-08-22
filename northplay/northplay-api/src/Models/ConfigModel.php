<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;

class ConfigModel extends Eloquent  {
    protected $table = 'northplay_config';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'key',
        'value',
        'category',
    ];

    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'extra_data' => 'json',
    ];
    protected $hidden = [
        'id',
    ];

    public function new_config_key($key, $value, $category)
    {
        $config_controller = new ConfigModel();
        $config_controller->insert([
            "key" => $key,
            "value" => $value,
            "category" => $category,
        ]);
        save_log("ConfigModel", "Config value ".$key." was set.");

        return $this->get_config_value($key);
    }

    public function get_config_value($key, $defaultValue = NULL, $defaultCategory = NULL)
    {
        $config_controller = new ConfigModel();
        $config_row = $config_controller->where("key", $key)->first();

        if(!$config_row) {
            if($defaultValue === NULL) {
                save_log("ConfigModel", $key." was not found in config and no defaultValue set");
                abort(400, "Key ". $key . " not found in config.");
            }
            if($defaultValue !== NULL) {
                if($defaultCategory === NULL) {
                    $defaultCategory = "undefined";
                }
                return $this->new_config_key($key, $defaultValue, $defaultCategory);
            }
        }

        return $config_row->value;
    }

    public function update_config_value($key, $newValue, $defaultCategory = NULL)
    {
        $config_controller = new ConfigModel();
        $config_row = $config_controller->where("key", $key)->first();

        if(!$config_row) {
            save_log("ConfigModel", "Tried updating key but failed.");
            return $this->new_config_key($key, $newValue, $defaultCategory);
        } else {
            save_log("ConfigModel", "Updated key [".$key."] from [".$config_row->value."] to value [".$newValue."]");
            $config_row->update([
                "value" => $newValue
            ]);
            $config_row = $config_controller->where("key", $key)->first();
        }

        return $config_row->value;
    }

}