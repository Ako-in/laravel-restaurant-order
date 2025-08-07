@extends('layouts.admin')

@section('content')

@if(session('flash_message'))
    <div class="alert alert-success mt-3">
        {{ session('flash_message') }}
    </div>
@endif

@if(session('error_message'))
    <div class="alert alert-danger mt-3">
        {{ session('error_message') }}
    </div>
@endif

{{-- startDateがNullの時のアラートを表示 --}}
@if(session('general_message'))
  <div class="alert alert-danger mt-3">
    {{ session('general_message') }}
  </div>
@endif

{{-- すでに目標が設定されている時のアラートを表示 --}}
@if(session('exist_message'))
  <div class="alert alert-danger mt-3">
    {{ session('exist_message') }}
  </div>
@endif




<div class="container py-4">
  <div>
      <div class="d-flex justify-content-center mb-2"> {{-- タイトルを中央寄せ --}}
          <h4 class="mb-0">売上目標編集(年間、月間)</h4>
      </div>
      <div class="mb-4"> {{-- 戻るボタンの親divに下マージン --}}
          <a href="{{ route('admin.sales_target.index') }}" class="btn btn-primary">戻る</a>
      </div>
  </div>
  <div class="row justify-content-center">
      <div class="col-md-8">
          <form action="{{ route('admin.sales_target.update',$salesTarget->id) }}" method="POST" enctype="multipart/form-data">
              @csrf
              @method('PUT')

              {{-- 売り上げ目標額 --}}
              <div class="mb-3">
                  <label class="target_amount" class="form-label">売上目標額：</label>
                  <input type="number" name="target_amount" id="target_amount" class="form-select mb-3" value="{{ number_format($salesTarget->target_amount) }}" placeholder="{{ number_format($salesTarget->target_amount) }}" required>
                  @error('target_amount')
                      <div class="text-danger mt-2">{{ $message }}</div>
                  @enderror
              </div>

              {{-- 目標の種類 --}}
              <div class="mb-3">
                <label for="period_type"class="form-label">目標の種類：</label>
                <p class="form-label">{{$salesTarget->period_type}}</p>
              
                {{-- <select name="period_type" id="period_type" class="form-select"required>
                  
                    <option value="">目標の種類を選択してください</option>
                    <option value="yearly" {{ old('period_type',$salesTarget->period_type) == 'yearly' ? 'selected' : '' }}>年間目標</option>
                    <option value="monthly" {{ old('period_type',$salesTarget->period_type) == 'monthly' ? 'selected' : '' }}>月間目標</option>
                    {{-- <option value="yearly" {{ old('period_type') == 'yearly' ? 'selected' : '' }}>年間目標</option>
                    <option value="monthly" {{ old('period_type') == 'monthly' ? 'selected' : '' }}>月間目標</option> --}}
                    {{-- <option value="daily" {{ old('period_type') == 'daily' ? 'selected' : '' }}>日別目標</option> --}}
                {{-- </select>  --}}
                @error('period_type')
                  <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
              </div>

              {{-- 開始年月選択グループ 初期は非表示--}}
              <div id="start_date_group_wrapper"class="mb-3">
                {{-- <div id="start_date_group" class="flex flex-col mt-4"> JavaScriptで表示/非表示を制御 --}}
                  <label class="start_date">開始年月:</label>
                  <p class="select-form">{{$salesTarget->start_date}}</p>
                  {{-- <p class="select-form">{{$salesTarget->Month}}</p> --}}
                  {{-- <div class="row g-2">
                    <div class="col-md-6"> 
                      <select name="start_year" id="start_year"class="form-select">
                        <option value="">年度を選択してください</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}" {{ old('start_year', $selectedYear) == $year ? 'selected' : '' }}>{{ $year }}年</option>
                        @endforeach
                      </select>
                      @error('start_year')
                          <div class="text-danger mt-2">{{ $message }}</div>
                      @enderror
                    </div>
                    <div class="col-md-6" id="start_month_container">
                      <select name="start_month" id="start_month" class="form-select">
                        <option value="">月を選択してください</option>
                        {{-- @foreach ($months as $month)
                            <option value="{{ $month }}" {{ old('start_month') == $month ? 'selected' : '' }}>{{ $month }}</option>
                        @endforeach --}}
                        {{-- @foreach (range(1, 12) as $monthNum) {{-- Make sure this line is exactly like this --}}
                        {{-- <option value="{{ sprintf('%02d', $monthNum) }}" {{ old('start_month',$selectedMonth) == sprintf('%02d', $monthNum) ? 'selected' : '' }}>
                            {{ $monthNum }}月 {{-- And this line as well, to display "1月", "2月", etc. --}}
                        {{-- </option>
                    @endforeach
                      </select>  
                      @error('start_month')
                          <div class="text-danger mt-2">{{ $message }}</div>
                      @enderror
                    </div>
                  </div> --}}

                  {{-- 開始日(年月日)表示グループ --}}
                <div id="start_date_display_group" class="mb-3"style="display:none;">
                  <label class="form-label">開始日</label>
                  <div class="d-flex align-items-center">
                    <span id="start_year_display"class="me-1"></span>年 
                    <span id="start_month_display"class="me-1"></span>月
                    <span id="start_day_display"class="me-1"></span>日
                  </div>
                  
                </div>
                <div>
                  {{-- 終了日表示グループ --}}
                  <div id="end_date_display_group" class="mb-3"style="display:none;">
                    <label class="form-label">終了日</label>
                    <div class="d-flex align-items-center">
                      <span id="end_year_display"class="me-1"></span>年 
                      <span id="end_month_display"class="me-1"></span>月
                      <span id="end_day_display"class="me-1"></span>日
                    </div>
                    
                  </div>
                  
  
                </div>

                  
                  
                  {{-- <select name="start_day" id="start_day">
                    <option value="">日にちを選択してください</option>
                    @for ($day = 1; $day <= 31; $day++)
                        <option value="{{ $day }}" {{ old('day') == $day ? 'selected' : '' }}>{{ $day }}</option>
                    @endfor
                  </select> --}}

                {{-- </div> --}}
              </div>
                
                {{-- <div>
                  <label for="end_date" class="text-gray-800">終了年月</label>
                  <select name="end_year" id="end_year">
                    <option value="">年度を選択してください</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}" {{ old('end_year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                  </select>
                  <select name="end_month" id="end_month">
                    <option value="">月を選択してください</option>
                    @foreach ($months as $month)
                        <option value="{{ $month }}" {{ old('end_month') == $month ? 'selected' : '' }}>{{ $month }}</option>
                    @endforeach
                  </select>
                  <select name="end_day" id="end_day">
                    <option value="">日にちを選択してください</option>
                    @for ($day = 1; $day <= 31; $day++)
                        <option value="{{ $day }}" {{ old('end_day') == $day ? 'selected' : '' }}>{{ $day }}</option>
                    @endfor
                  </select>
                </div>
              </div> --}}
              {{-- <div>
                終了日表示グループ
                <div id="end_date_display_group" class="mb-3"style="display:none;">
                  <label class="form-label">終了日</label>
                  <div class="d-flex align-items-center">
                    <span id="end_year_display"class="me-1"></span>年 
                    <span id="end_month_display"class="me-1"></span>月
                    <span id="end_day_display"class="me-1"></span>日
                  </div>
                  
                </div>
                

              </div> --}}
              

              {{-- <div class="flex flex-col mt-4">
                <label for="year" class="text-gray-800">年度：</label>
                <select name="year" id="year" class="form-select">
                    <option value="">年度を選択してください</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}" {{ old('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
                
              </div>

              <div class="flex flex-col mt-4">
                <label for="month" class="text-gray-800">月：</label>
                <select name="month" id="month" class="form-select" required>
                  <option value="">月を選択してください</option>
                  @foreach ($months as $month)
                      <option value="{{ $month }}" {{ old('month') == $month ? 'selected' : '' }}>{{ $month }}</option>
                  @endforeach
                </select>
               
              </div> --}}
              
          
              <button type="submit" class="btn btn-secondary mt-3">売上登録編集</button>
          </form>

          {{-- <div class="mt-3">
            <p>過去の更新履歴</p>
            <hr>




          </div> --}}
      </div>
  </div>
</div>
@endsection

{{-- @push('scripts')
<script>
  document.addEventListener('DOMContentLoaded',function(){
    console.log('DOMContentLoadedイベントが発火しました');
  // document.getElementById('period_type').addEventListener('change', function() {
    const periodTypeSelect = document.getElementById('period_type');//目標の種類年間、月間
    const startYearSelect = document.getElementById('start_year');//セレクトボックス開始日
    const startMonthSelect = document.getElementById('start_month');//セレクトボックス終了日


    // 表示・非表示を制御するコンテナ
    // const startDateGroup = document.getElementById('start_date_group');//開始年月選択グループ
    const startDateGroupWrapper = document.getElementById('start_date_group_wrapper'); // 開始年月選択グループのラッパー非表示
    const startMonthContainer = document.getElementById('start_month_container');//開始月コンテナ
    const startDateDisplayGroup = document.getElementById('start_date_display_group');//開始日表示グループ
    const endDateDisplayGroup = document.getElementById('end_date_display_group');//終了日表示グループ


    // 開始日表示
    const startYearDisplay = document.getElementById('start_year_display'); // 開始年表示
    const startMonthDisplay = document.getElementById('start_month_display'); // 開始月表示
    const startDayDisplay = document.getElementById('start_day_display'); // 開始日表示（必要に応じて追加）

    // 終了日表示
    const endYearDisplay = document.getElementById('end_year_display');//
    const endMonthDisplay = document.getElementById('end_month_display');
    // const endYearDisplay = document.getElementById('start_year_display');
    // const endMonthDisplay = document.getElementById('start_month_display');
    const endDayDisplay = document.getElementById('end_day_display');

    // ヘルパー関数: 月の日数を取得 (うるう年対応)
    function daysInMonth(year, month) {
      return new Date(year, month, 0).getDate();
    }

    function updateFieldsVisibility() {
        console.log('updateFieldsVisibility関数が実行されました。');
        const selectedType = periodTypeSelect.value;
        // 全ての日付フィールドグループと終了日表示を一旦非表示に
        startDateGroupWrapper.style.display = 'none'; 
        // startDateGroup.style.display = 'none';
        startMonthContainer.style.display = 'none'; // 月のコンテナを非表示
        startDateDisplayGroup.style.display = 'none'; // 開始日表示グループを非表示
        // endDateDisplayGroup.style.display = 'none';

        // required 属性のリセット
        startYearSelect.removeAttribute('required');
        startMonthSelect.removeAttribute('required');

        // 目標の種類に応じて表示/非表示を切り替える
        if (selectedType === 'monthly') {
            // startDateGroup.style.display = 'block';
            startDateGroupWrapper.style.display = 'block'; // 年月選択グループを表示
            startMonthContainer.style.display = 'block'; // 月のコンテナを表示
            // startDateDisplayGroup.style.display = 'block'; // 開始日表示グループを表示
            // endDateDisplayGroup.style.display = 'block'; // 終了日表示グループを表示
            startYearSelect.setAttribute('required', 'required');
            startMonthSelect.setAttribute('required', 'required');
            // startDateDisplayGroup.setAttribute('required', 'required'); // 開始日表示グループも必須に設定
            // endDateDisplayGroup.setAttribute('required', 'required'); // 終了日表示グループも必須に設定
        } else if (selectedType === 'yearly') {
            // startDateGroup.style.display = 'block';
            startDateGroupWrapper.style.display = 'block'; // ID変更を反映
            startMonthContainer.style.display = 'none'; // 年間目標では月は非表示
            endDateDisplayGroup.style.display = 'block'; // 終了日表示グループを表示
            startYearSelect.setAttribute('required', 'required');
            // startMonthSelect は required を解除したまま
        }
        calculateAndDisplayEndDate(); // この関数の最後で calculateAndDisplayEndDate を呼び出す
    }
  
    // --- 終了日を計算し表示を更新する関数 ---
    function calculateAndDisplayEndDate() {
        // 関数スコープ内で変数を定義
        const selectedType = periodTypeSelect.value;
        const startYear = parseInt(startYearSelect.value);
        const startMonth = parseInt(startMonthSelect.value); // 1-12の数値

        console.log('calculateAndDisplayEndDate関数が実行されました。');
        console.log('selectedType:', selectedType);
        console.log('startYear:', startYear);
        console.log('startMonth:', startMonth);

        // 開始年が未選択、または月間目標で月が未選択の場合は終了日をクリアして非表示
        if (isNaN(startYear) || (selectedType === 'monthly' && isNaN(startMonth))) {
            endYearDisplay.textContent = '';
            endMonthDisplay.textContent = '';
            endDayDisplay.textContent = '';
            endDateDisplayGroup.style.display = 'none';
            return;//これ以上処理を続けない
        }

        let endDate = null; // Dateオブジェクトを格納するための変数

        if (selectedType === 'monthly') {
            console.log('月間目標を選択 - calculateAndDisplayEndDate');
            // 月間目標の場合: その月の最終日
            // Date(年, 月-1, 日) - JavaScriptの月は0始まり (0=1月, 11=12月)
            endDate = new Date(startYear, startMonth - 1, daysInMonth(startYear, startMonth));
        } else if (selectedType === 'yearly') {
            console.log('年間目標を選択 - calculateAndDisplayEndDate');
            // 年間目標の場合: 翌年の3月31日 (要件)
            endDate = new Date(startYear + 1, 2, 31); // 2は3月を表す（0始まり）
        } else {
            console.log('どの目標タイプにも該当しません - calculateAndDisplayEndDate');
        }

        // 計算されたendDateが有効な日付であれば表示を更新
        if (endDate && !isNaN(endDate.getTime())) {
            endYearDisplay.textContent = endDate.getFullYear();
            endMonthDisplay.textContent = (endDate.getMonth() + 1).toString().padStart(2, '0');
            endDayDisplay.textContent = endDate.getDate().toString().padStart(2, '0');
            endDateDisplayGroup.style.display = 'block';
        } else {
            // 無効な日付の場合は表示をクリアして非表示
            endYearDisplay.textContent = '';
            endMonthDisplay.textContent = '';
            endDayDisplay.textContent = '';
            endDateDisplayGroup.style.display = 'none';
        }
    }

    // **2. イベントリスナーの設定は、関数定義の後に一度だけ行う！**
    // 目標の種類が変更されたら、フォームの表示と終了日を更新
    periodTypeSelect.addEventListener('change', updateFieldsVisibility);

    // 開始年が変更されたら、終了日を再計算して表示
    startYearSelect.addEventListener('change', calculateAndDisplayEndDate);

    // 開始月が変更されたら、終了日を再計算して表示
    startMonthSelect.addEventListener('change', calculateAndDisplayEndDate);

    // **3. ページロード時の初期設定とold()値の復元**
    // old()値がある場合はそれを反映し、そうでなければ初期状態を設定する
    if (periodTypeSelect.value) { 
        if ("{{ old('start_year') }}") startYearSelect.value = "{{ old('start_year') }}";
        if ("{{ old('start_month') }}") startMonthSelect.value = "{{ old('start_month') }}";
        updateFieldsVisibility(); // old値に基づいて表示を更新
    } else {
        updateFieldsVisibility(); // old値がない場合も初期表示を設定
    }


});
</script>
@endpush
 --}}
