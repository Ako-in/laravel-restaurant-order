<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\Interpreter;
use Goodby\CSV\Import\Standard\Reader;
use Goodby\CSV\Import\Standard\LexerConfig;

class CsvImportController extends Controller
{
    // public function index()
    // {
    //     return view('admin.csv_import.index');
    // }

    public function importStore(Request $request)
    {
        Log::info('CSVImportController@importStore: CSVインポート処理開始');

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ], [
            'csv_file.required' => 'CSVファイルは必須です。',
            'csv_file.file' => '有効なファイルを選択してください。',
            'csv_file.mimes' => 'CSVまたはTXTファイルをアップロードしてください。',
            'csv_file.max' => 'ファイルサイズは2MB以下でなければなりません。',
        ]);

        $file = $request->file('csv_file');
        Log::info('CSVImportController@importStore: アップロードされたファイル', ['file_name' => $file->getClientOriginalName()]);
        $filePath = $file->getRealPath(); // アップロードされた一時ファイルのパス

        $lexer_config = new LexerConfig();
        $lexer = new Lexer($lexer_config);

        $interpreter = new Interpreter();
        $interpreter->unstrict();

        $rows = [];
        $hasHeader = true;
        $firstRow = true;

        $interpreter->addObserver(function ($row) use (&$rows, &$hasHeader, &$firstRow) {
            if ($firstRow) {
                $firstRow = false; // 最初の行はヘッダーとみなす
                if ($hasHeader) {
                    return; // ヘッダー行はスキップ
                }
            }
            $rows[] = $row; // データ行を保存
        });

        $lexer->parse($filePath, $interpreter);

        $importedCount = 0;
        foreach ($rows as $key => $value){
            if(count($value) == 7){//モデルに７つのカラムがある場合
                OrderItem::create([
                    'order_id' => $value[0],
                    'menu_id' => $value[1],
                    'qty' => $value[2],
                    'price' => $value[3],
                    'tax' => $value[4],
                    'subtotal' => $value[5],
                    'created_at' => $value[6],

                ]);
                $importedCount++;
            }else{
                Log::warning('CSVImportController@importStore: 不正な行が検出されました', ['row' => $value, 'line_number' => $key + 1]);
                // 不正な行の処理をここに追加することもできます
            }
            
            
        }

        // CSVファイルの処理ロジックをここに追加

        // $reader = new Reader(new Lexer(), new Interpreter());
            
        //     // CSVヘッダーをスキップしたい場合 (例: 最初の行がヘッダー)
        //     $firstRow = true; 
        //     $reader->each(function ($row) use (&$firstRow) {
        //         if ($firstRow) {
        //             $firstRow = false; // ヘッダー行をスキップ
        //             return;
        //         }
        //         // ここでCSVの各行 ($row) のデータをデータベースに保存する処理を記述
        //         // 例:
        //         // \App\Models\YourModel::create([
        //         //     'column1' => $row[0],
        //         //     'column2' => $row[1],
        //         //     // ... 必要に応じて他のカラムも
        //         // ]);
        //         Log::info('CSVImportController@importStore: 処理中のCSV行', ['row' => $row]);
        //     });

        Log::info('CSVImportController@importStore: CSVファイル処理完了');
        return response()->json(['message' => 'CSVファイルが正常にインポートされました。']);

        // return redirect()->back()->with('success', 'CSVファイルが正常にインポートされました。');
    }

    public function render()
   {
      admin::script($this->script());
      return <<<EOT
      <div class="btn-group pull-right" style="margin-right: 10px">
          <a class="btn btn-sm btn-primary csv-import-button"><i class="fa fa-upload"></i> CSVインポート</a>
          <input type="file" id="csv_import_file_input" style="display: none;">
      </div>
      EOT;
   }
}
