<?php
namespace Northplay\NorthplayApi\Controllers\Casino\API;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Northplay\NorthplayApi\Traits\ApiResponderTrait;

class MetadataController
{
    use ApiResponderTrait;
    public function __construct()
    {
        $this->config_model = new \Northplay\NorthplayApi\Models\ConfigModel;
        $this->currency_controller = new \Northplay\NorthplayApi\Controllers\Casino\API\Currency\CurrencyController;
        $this->cache_timers = [
            "global" => 1200,
            "subpage" => 1200,
            "currencies" => 600,
            "reset_cache" => 60,
            "undefined" => 300,
        ];
        $this->reset_cache_check();
        $this->global_metadata = $this->metadata_cache_helper("global");
        $this->sub_page_metadata = $this->metadata_cache_helper("subpage");
        $this->currencies_metadata = $this->metadata_cache_helper("currencies");
    }

    public function reset_cache_check() {
        $reset_cache = Cache::get("reset_cache_check");
        if(!$reset_cache) {
            $reset_cache = $this->config_model->get_config_value("force_cache_reset_metadata", "no", "environment");
            Cache::set("reset_cache_check", $reset_cache, now()->addSeconds($this->cache_timers['reset_cache']));
        }
        if($reset_cache === "yes") {
            $this->config_model->update_config_value("force_cache_reset_metadata", "no", "environment");
            Cache::forget("metadata_global");
            Cache::forget("metadata_subpage");
            Cache::forget("metadata_currencies");
        }
    }

    public function build_data($datakey)
    {
        if($datakey === "global") {
            return array(
                "page_url" => $this->config_model->get_config_value("page_url", config("northplay-api.frontend_url")),
                "page_backend_url" => $this->config_model->get_config_value("page_backend_url", config("northplay-api.backend_url")),
                "links_twitter" => $this->config_model->get_config_value("links_twitter", config("northplay-api.frontend_url")),
                "links_github" =>  $this->config_model->get_config_value("links_github", config("northplay-api.frontend_url")),
                "links_email" => $this->config_model->get_config_value("links_email", config("northplay-api.frontend_url")),
            );
        }
        if($datakey === "subpage") {
            $base_title = $this->config_model->get_config_value("page_title", "Casino");
            return array(
                "index" => array(
                    "title" => $this->config_model->get_config_value("page_subtitle_index", "Welcome", "subpage_meta")." - ".$base_title,
                ),
                "logout" => array(
                    "title" => $this->config_model->get_config_value("page_subtitle_logout", "Logout", "subpage_meta")." - ".$base_title,
                ),
                "undefined" => array( //fallback page array
                    "title" => $base_title,
                )
            );
        }
        if($datakey === "currencies") {
            return $this->currency_controller->active_currencies();
        }

    }

    public function metadata_cache_helper($datakey) {
        $cache_key = "metadata_".$datakey;
        $cached_array = Cache::get($cache_key);
        if(!$cached_array) {
            $build_data = $this->build_data($datakey);
            $data = array(
                "success" => true,
                "cached" => now_nice(),
                "data" => $build_data,
            );
            $cache_length = 5;
            if(isset($this->cache_timers[$datakey])) { // overwrite cache length if found in construct
                $cache_length = $this->cache_timers[$datakey];
            }
            if($build_data) {
                Cache::set($cache_key, $data, now()->addSeconds($cache_length));
                $cached_array = Cache::get($cache_key);
            } else {
                save_log("MetadataController", "Building data for ${cache_key} seems to return a empty result.");
            }

            if(!$cached_array) {
                save_log("MetadataController", "Error trying to retrieve ${cache_key} key from cache while just tried to set.");
                return array(
                    "success" => "Error trying to retrieve ${cache_key} key from cache while just tried to set.",
                    "cached" => false,
                    "data" => $this->build_data($datakey),
                );
            }
        }
        return $cached_array;
    }

    
    public function page_helper(Request $request)
    {
        $page_array = [];
        $page_array["set"] = false;
        $page_array["cached"] = false;
        try {
            if(isset($this->sub_page_metadata['data'])) {
                if($request->page) {
                    if(isset($this->sub_page_metadata['data'][$request->page])) {
                        $page_array["data"] = $this->sub_page_metadata['data'][$request->page];
                        $page_array["data"]["page_key"] = $request->page;
                        $page_array["set"] = true;
                        $page_array["success"] = true;
                    }
                }
                if($page_array["set"] === false) {
                    $page_array["data"] = $this->sub_page_metadata['data']["undefined"];
                    $page_array["data"]["page_key"] = "undefined";
                    $page_array["success"] = true;
                }
                $page_array["cached"] = $this->sub_page_metadata['cached'];
            } else {
                $page_array["success"] = "Error trying to retrieve page info from cache";
            }
            return $page_array;
            
        } catch(\Exception $e) {
            save_log("Error", $e->getMessage());
            return array(
                "cached" => false,
                "success" => $e->getMessage()
            );
        }
    }
    

    public function retrieve(Request $request)
    {
        $global = $this->global_metadata;
        $currencies = $this->currencies_metadata;
        $page = $this->page_helper($request);
        $main_data = [
            "page" => $page['data'] ?? false,
            "global" => $global['data'] ?? false,
            "currencies" => $currencies['data'] ?? false,
        ];
        $cache_data = [
            "page" => $page['cached'] ?? false,
            "global" => $global['cached'] ?? false,
            "currencies" => $currencies['cached'] ?? false,
        ];
        return $this->responder_success($main_data, $cache_data, "MetadataController");
    }
}