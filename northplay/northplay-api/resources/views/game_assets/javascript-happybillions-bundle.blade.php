@php
    // $save_file = \Illuminate\Support\Facades\Storage::putFileAs('rawjs', new \Illuminate\Http\File(storage_path("happybillions-bundle.js")), "happybillions-bundle.js");
    // $check_exists = \Illuminate\Support\Facades\Storage::exists('rawjs/happybillions-bundle.html');
    // if(!$check_exists) { http_response_code(404) & die(); }

    $safety_integrity = "22f357b3cd99ce1e10f54d0401f8fd6b";

    $content = \Illuminate\Support\Facades\Storage::get('rawjs/happybillions-bundle.html');
    $content_integrity = md5($content);
    $mismatch = null;

    if($content_integrity !== $safety_integrity) {
        $mismatch = true;
        $base_error = '\n [[BUNDLE.JS SECURITY ERROR]]';
        if(env('APP_DEBUG') === true) {
            $error_action = ' - ERROR_ACTION: Continued loading the contents because game is in development mode, do not forget to set integrity hash of this file before going production.';
        } else {
            $error_action = ' - ERROR_ACTION: Stopped loading the contents because game is in production mode.' ;
        }

        $error_desc = ' - ERROR_DESC: The integrity ('.$content_integrity.') for this content does not match the one hardcoded internally. \n';

        $final_error = $base_error . '\n\n' . $error_action . '\n\n'. $error_desc . '\n\n';

        $error = 'console.error("'.$final_error.'");';

    }
@endphp

@if($mismatch) 
(() => {
{!! $error !!}
})();
@if(env('APP_DEBUG') === true)
(() => {
{!! $content !!}
})();
@endif

@endif

@if(!$mismatch)
(() => {
{!! $content !!}
})();
@endif
