<?php

namespace App\admin\Extensions\Tools;

use Encore\admin\admin;
use Encore\admin\Grid\Tools\AbstractTool;

class CsvImport extends AbstractTool
{
  protected $action; //コンストラクタで受け取るURLを保持するため

  public function __construct($action) //コンストラクタでPOSTリクエストのURLを受け取る
  {
    $this->action = $action;
  }

  protected function script()
  {
    // CSRFトークンをJavaScriptに渡すための設定
    $token = csrf_token();
    // $this->actionを使ってURLを指定
    return <<< SCRIPT
      $('.csv-import-button').click(function() {// クラス名を明確にする
        document.getElementById("csv_import_file_input").click();
      });

      $('#csv_import_file_input').change(function() {
        var file = $(this)[0].files[0];// ファイルが選択されたときの処理
        if (file) {
          return; // ファイルが選択された場合は何もしない
        }
        var formData = new FormData();
        formData.append("_token", "{$token}"); // CSRFトークンを追加
        formData.append('csv_file', file);// コントローラーが期待する 'csv_file' としてファイルを追加
        
        $.ajax({
          type: 'POST', // POSTリクエスト
          url: "{$this->action}", // POSTリクエストのURL
          data: formData,
          processData: false, // jQueryがデータを処理しないように設定
          contentType: false, // jQueryがContent-Typeを設定しないように設定
          success: function(response) {
            // 成功時の処理
            $.pjax.reload("#pjax-container"); // Pjaxを使ってコンテナを再読み込み
            toastr.success('CSVのアップロードが成功しました'); // トースター通知
          },
          error: function(xhr, status, error) {
            // エラー時の処理
            toastr.error('CSVのアップロードに失敗しました: ' + error); // トースター通知
          }
        });
      });
      SCRIPT;
  }

  public function render()
  {
    admin::script($this->script()); //このツールが描画されるときにJavaScriptをページに追加
    //  return view('csv_upload');
    return <<<EOT
      <div class="btn-group pull-right" style="margin-right: 10px">
          <a class="btn btn-sm btn-primary csv-import-button"><i class="fa fa-upload"></i> CSVインポート</a>
          <input type="file" id="csv_import_file_input" style="display: none;">
      </div>
      EOT;
  }
}
