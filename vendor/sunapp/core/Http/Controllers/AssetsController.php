<?php

namespace SunAppModules\Core\Http\Controllers;

use Illuminate\Http\Request;
use MatthiasMullie\Minify;
use Theme;
use File;
use Str;

class AssetsController extends Controller
{
    public function handleAsset(Request $request)
    {
        $path = $request->path();

        if (str_ends_with($path, '.min.js')) {
            $response = $this->handleScriptAsset($path);
        } elseif (str_ends_with($path, '.min.css')) {
            $response = $this->handleStyleAsset($path);
        } else {
            abort(404);
        }

        echo $response;
    }

    public function handleScriptAsset($path)
    {
        $unminified_path = str_replace('.min.js', '.js', $path);

        if (\File::exists(public_path($unminified_path))) {
            $minifier = new Minify\JS(public_path($unminified_path));
            $minified = $minifier->minify($path);
            header("Content-type: application/javascript", true);
            return $minified;
        } else {
            abort(404);
        }
    }

    public function handleStyleAsset($path)
    {
        $unminified_path = str_replace('.min.css', '.css', $path);

        if (\File::exists(public_path($unminified_path))) {
            $minifier = new Minify\CSS(public_path($unminified_path));
            $minified = $minifier->minify($path);
            header("Content-type: text/css", true);
            return $minified;
        } else {
            abort(404);
        }
    }


    public function minifyBackendAssets()
    {
        $theme = env('APP_THEME');
        try {
            if (Theme::exists($theme)) {
                $theme_path = Theme::theme($theme)->getThemePath();
                $assets_path = $theme_path . 'assets';
                $modules_path = $theme_path . 'modules';
                $files = array_merge(File::allfiles($assets_path), File::allfiles($modules_path));

                foreach ($files as $file) {
                    $pathinfo = pathinfo($file);
                    $file_path = $pathinfo['dirname'] . '/' . $pathinfo['basename'];
                    if (Str::endsWith($file_path, '.css') && !Str::endsWith($file_path, '.min.css')) {
                        $minifier = new Minify\CSS($file_path);
                        $minifier->minify(str_replace('.css', '.min.css', $file_path));
                    } elseif (Str::endsWith($file_path, '.js') && !Str::endsWith($file_path, '.min.js')) {
                        $minifier = new Minify\JS($file_path);
                        $minifier->minify(str_replace('.js', '.min.js', $file_path));
                    }
                }
            }
        } catch (\Exception $e) {
            //
        }

        return redirect()->back();
    }
}
