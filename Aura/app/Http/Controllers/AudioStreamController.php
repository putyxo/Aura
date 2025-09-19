<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AudioStreamController extends Controller
{
    public function stream(Request $request, $filename)
    {
        $path = storage_path('app/public/audios/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        $size   = filesize($path);
        $length = $size;
        $start  = 0;
        $end    = $size - 1;

        header('Content-Type: audio/mpeg');
        header("Accept-Ranges: bytes");

        if ($request->headers->has('Range')) {
            $range = $request->header('Range');
            [$param, $range] = explode('=', $range, 2);
            if (strpos($range, ',') !== false) {
                header("HTTP/1.1 416 Requested Range Not Satisfiable");
                header("Content-Range: bytes $start-$end/$size");
                exit;
            }
            if ($range0 = strpos($range, '-')) {
                $c_start = intval(substr($range, 0, $range0));
                $c_end   = ($range0 < strlen($range)-1) ? intval(substr($range, $range0+1)) : $end;
            } else {
                $c_start = intval($range);
                $c_end   = $end;
            }
            $c_end   = ($c_end > $end) ? $end : $c_end;
            if ($c_start > $c_end || $c_start > $size - 1) {
                header("HTTP/1.1 416 Requested Range Not Satisfiable");
                header("Content-Range: bytes $start-$end/$size");
                exit;
            }
            $start  = $c_start;
            $end    = $c_end;
            $length = $end - $start + 1;
            header("HTTP/1.1 206 Partial Content");
        }

        header("Content-Range: bytes $start-$end/$size");
        header("Content-Length: " . $length);

        $fp = fopen($path, 'rb');
        fseek($fp, $start);
        $buffer = 1024 * 8;
        while (!feof($fp) && ($p = ftell($fp)) <= $end) {
            if ($p + $buffer > $end) {
                $buffer = $end - $p + 1;
            }
            echo fread($fp, $buffer);
            flush();
        }
        fclose($fp);
        exit;
    }
}
