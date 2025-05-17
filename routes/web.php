<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-db', function () {
    try {
        // Esto hará una consulta simple
        $version = DB::selectOne('SELECT VERSION() AS v')->v;
        return "Conexión OK, MySQL versión: {$version}";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
Route::get('/db/tables', function () {
    $rows = DB::select('SHOW TABLES');
    // El array resultante tiene claves según el nombre de la columna devuelta,
    // que suele ser "Tables_in_<tu_base_datos>"
    $key = 'Tables_in_' . env('DB_DATABASE');
    $tables = array_map(fn($r) => $r->$key, $rows);
    return response()->json($tables);
});

Route::get('/db/columns-all', function () {
    // Lista de tablas que ya conoces
    $tables = [
        "cache","cache_locks","failed_jobs","job_batches","jobs",
        "leccion","lecciones","migrations","nivel","opciones",
        "password_reset_tokens","pregunta","sessions","users"
    ];

    $columns = [];
    foreach ($tables as $table) {
        // getColumnListing devuelve un array de nombres de columna
        $columns[$table] = Schema::getColumnListing($table);
    }

    // Devuelve un JSON con la estructura:
    // { "cache": ["key","value",...], "cache_locks": [...], ... }
    return response()->json($columns);
});


Route::get('/db/records/{table}', function (Request $request, $table) {
    // 1. Comprueba que la tabla exista
    if (! Schema::hasTable($table)) {
        return response()->json([
            'error' => "La tabla «{$table}» no existe."
        ], 404);
    }

    // 2. Recupera todos los registros
    $rows = DB::table($table)->get();

    // 3. Devuelve JSON
    return response()->json([
        'table'   => $table,
        'count'   => $rows->count(),
        'records' => $rows,
    ]);
});
