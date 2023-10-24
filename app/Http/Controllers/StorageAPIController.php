<?php

namespace App\Http\Controllers;

use App\Models\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades;

class StorageAPIController extends Controller
{
    private $IMAGE_EXTS = ["jpg", "png", "webp"];
    private $VIDEO_EXTS = ["mp4"];

    /**
     * 指定されたストレージとパスに含まれるファイルを取得します
     * @param Request $request
     * @param string $storageId ストレージID
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function list(Request $request, string $storageId)
    {
        // ストレージIDの存在確認
        $storage = Storage::where("id", "=", $storageId)->first();
        if (!$storage) {
            return response()->json([
                "status" => "error",
                "message" => "storage not found"
            ], 404);
        }

        switch ($storage->storage_type) {
            // 物理ストレージ
            case "physical":
                $physical_uri = $request->get("path", "/");
                if (!str_starts_with($physical_uri, "/")) {
                    return response()->json([
                        "status" => "error",
                        "message" => "path must be starts with '/'"
                    ], 400);
                }

                $physical_uri = $this->path_combine_cs($storage->connection_string, $physical_uri);
                $files = glob($physical_uri.DIRECTORY_SEPARATOR."*");
                $responses = [];
                foreach ($files as $file) {
                    $filetype = $this->guess_filetype($file);

                    $thumbnail_path = $request->get("path", "/");
                    if (!str_ends_with($thumbnail_path, "/")) $thumbnail_path = $thumbnail_path."/";
                    $thumbnail_path = $thumbnail_path.basename($file);

                    $responses[] = [
                        "name" => basename($file),
                        "filetype" => $filetype,
                        "size" => filesize($file),
                        "createDate" => date("c", filectime($file)),
                        "updateDate" => date("c", fileatime($file)),
                        "hidden" => str_starts_with($file, "."),
                        "thumbnail_uri" => url("/api/storage/".$storageId."/thumbnail?path=".urlencode($thumbnail_path))
                    ];
                }

                return response()->json([
                    "status" => "success",
                    "response" => $responses
                ]);

            default:
                return response()->json([
                    "status" => "fail",
                    "message" => "storage type '".$storage->storage_type."' doesn't processable type."
                ], 500);
        }
    }

    public function get(Request $request, string $storageId) {
        // ストレージIDの存在確認
        $storage = Storage::where("id", "=", $storageId)->first();
        if (!$storage) {
            return response()->json([
                "status" => "error",
                "message" => "storage not found"
            ], 404);
        }

        switch ($storage->storage_type) {
            // 物理ストレージ
            case "physical":
                $physical_uri = $request->get("path", "/");
                if (!str_starts_with($physical_uri, "/")) {
                    return response()->json([
                        "status" => "error",
                        "message" => "path must be starts with '/'"
                    ], 400);
                }

                $physical_realuri = $this->path_combine_cs($storage->connection_string, $physical_uri);
                if (is_file($physical_realuri)) return response()->file($physical_realuri);
                else return response()->json([
                    "status" => "error",
                    "message" => "file was not found"
                ], 404);

            default:
                return response()->json([
                    "status" => "fail",
                    "message" => "storage type '".$storage->storage_type."' doesn't processable type."
                ], 500);
        }
    }

    public function thumbnail(Request $request, string $storageId) {
        // ストレージIDの存在確認
        $storage = Storage::where("id", "=", $storageId)->first();
        if (!$storage) {
            return response()->json([
                "status" => "error",
                "message" => "storage not found"
            ], 404);
        }

        switch ($storage->storage_type) {
            // 物理ストレージ
            case "physical":
                $physical_uri = $request->get("path", "/");
                if (!str_starts_with($physical_uri, "/")) {
                    return response()->json([
                        "status" => "error",
                        "message" => "path must be starts with '/'"
                    ], 400);
                }

                $physical_realuri = $this->path_combine_cs($storage->connection_string, $physical_uri);
                if (!is_file($physical_realuri)) return response()->json([
                    "status" => "error",
                    "message" => "file was not found"
                ], 404);

                $hashed_filename = sha1($physical_uri);
                $thumb_path = Facades\Storage::path("/cached_thumbs/physical/").$hashed_filename;
                if (is_file($thumb_path)) {
                    // キャッシュ内に存在するならそれを返す
                    return response()->file($thumb_path);

                } elseif (config("app.thumbnail_generator") != null) {
                    // ジェネレーターが存在するならそれを実行
                    $exec_program = config("app.thumbnail_generator")." \"".$physical_realuri."\" \"".$thumb_path."\"";
                    exec($exec_program);
                    if (is_file($thumb_path)) return response()->file($thumb_path);
                }

                // それ以外はエラーを返す
                return response()->json([
                    "status" => "error",
                    "message" => "specified thumbnail was not found"
                ], 404);

            default:
                return response()->json([
                    "status" => "fail",
                    "message" => "storage type '".$storage->storage_type."' doesn't processable type."
                ], 500);
        }
    }

    public function storages(Request $request) {
        $storages = Storage::select("id", "name", "storage_type")->get();
        return response()->json([
            "status" => "success",
            "response" => $storages
        ]);
    }

    public function storages_add(Request $request) {
        if ($request->post("storage_type") == null || $request->post("connection_string") == null || $request->post("name") == null) {
            return response()->json([
                "status" => "error",
                "message" => "invalid request"
            ], 400);
        }

        $newStorage = new Storage();
        $newStorage->name = $request->post("name");
        $newStorage->storage_type = $request->post("storage_type");
        $newStorage->connection_string = $request->post("connection_string");
        $newStorage->save();

        return response()->json([
            "status" => "success",
            "message" => "successfully to create storage"
        ]);
    }

    private function path_combine_cs(string $base, string $comb, bool $detect_dirtra = true) {
        if ($comb === "/") return $base;

        // Windowsの場合は、バックスラッシュに置き換える
        if (str_contains(PHP_OS, "WIN")) {
            $base = str_replace("/", "\\", $base);
            $comb = str_replace("/", "\\", $comb);
        }

        if (!str_ends_with($base, "/")) {
            $base = $base . DIRECTORY_SEPARATOR;
        }

        $newstr = $base.substr($comb, 1);
        if (str_ends_with($newstr, DIRECTORY_SEPARATOR)) $newstr = substr($newstr, 0, -1);

        // ディレクトリトラバーサルを検知する
        if ($detect_dirtra){
            $newstr = realpath($newstr);
            if (!str_starts_with($newstr, $base)) exit();
        }

        return $newstr;
    }

    private function guess_filetype(string $file) {
        if (is_dir($file)) return "directory";
        $pathinfo = pathinfo(basename($file));
        $extension = $pathinfo['extension'];

        if (in_array($extension, $this->IMAGE_EXTS)) return "image";
        if (in_array($extension, $this->VIDEO_EXTS)) return "video";

    }
}
