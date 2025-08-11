# ▪️Larvel 飲食店オーダー管理アプリ：Urbanspoon

<!-- <img src="storage/app/public/images/toppage.jpg" height="300px" alt="Urbanspoon トップページ"> -->
<!-- <img src="https://raw.githubusercontent.com/Ako-in/laravel-restaurant-order/master/public/images/toppage.jpg" height="300px" alt="トップページ画像"> -->
<img src="https://raw.githubusercontent.com/Ako-in/laravel-restaurant-order/master/public/images/readme/customer/toppage.jpg" height="250px" alt="Urbanspoon トップページ">

https://urbanspoon.sakura.ne.jp/customer/login  
https://urbanspoon.sakura.ne.jp/admin/login

## 開発理由

昨今の飲食店のオーダーシステムはトレンドで今後も増えていく傾向があると感じ、  
注文システム以外にも店舗側が管理しやすくなるシステムを目指して
アプリ作成を決めました。

## 使用技術

バックエンド

-   PHP(8.3.13)
-   Laravel(9.52.20)

フロントエンド

-   JavaScript
-   HTML/CSS
-   Bootstrap

データベース

-   MySQL

-   git
-   さくらインターネット

## 機能一覧

<カスタマー側>

-   ログイン機能
-   検索機能
    各種ワンクリック検索、絞り込み検索
-   カート機能
    カートに入れる
    カート内編集(数量修正、削除)
-   注文確定機能
-   注文履歴一覧表示機能
-   クレジット決済機能
-   ログアウト

<アドミン(店舗)側>

-   ログイン機能
-   注文一覧機能
    新規注文表示  
    注文数量修正  
    注文個別ステータス変更（メニュー別）  
    注文全体ステータス表示(複数注文の時)
-   新規メニュー・カテゴリ作成機能

-   在庫管理機能
-   売上一覧機能  
    月別売上グラフ表示(目標、実績、売上件数)  
    カテゴリ別売上割合表示  
    売上 CSV ファイルダウンロード機能  
    全件売上金額、アイテム表示
-   売上目標設定機能  
    年間・月間売上目標設定  
    目標表示  
    目標編集
-   ログアウト

## アプリの特徴

### カスタマー側の表示

<img src="https://raw.githubusercontent.com/Ako-in/laravel-restaurant-order/master/public/images/readme/customer/urbanspoon_customer_login2.png" height="250px" alt="Urbanspoon カスタマーログイン">

<img src="https://raw.githubusercontent.com/Ako-in/laravel-restaurant-order/master/public/images/readme/customer/urbanspoon_customer_login2.png" height="250px" alt="Urbanspoon カスタマーログイン">

<img src="https://raw.githubusercontent.com/Ako-in/laravel-restaurant-order/master/public/images/readme/customer/urbanspoon_customer_toppagemenu.png" height="250px" alt="Urbanspoon カスタマーメニュートップページ">

<img src="https://raw.githubusercontent.com/Ako-in/laravel-restaurant-order/master/public/images/readme/customer/urbanspoon_customer_cart.png" height="250px" alt="Urbanspoon カスタマーカート">

<img src="https://raw.githubusercontent.com/Ako-in/laravel-restaurant-order/master/public/images/readme/customer/urbanspoon_customer_carteditqty.png" height="250px" alt="Urbanspoon カスタマーカート数量変更">

<img src="https://raw.githubusercontent.com/Ako-in/laravel-restaurant-order/master/public/images/readme/customer/urbanspoon_customer_cartDelete.png" height="250px" alt="Urbanspoon カスタマーカート削除">

<img src="https://raw.githubusercontent.com/Ako-in/laravel-restaurant-order/master/public/images/readme/customer/urbanspoon_customer_orderhistory.png" height="250px" alt="Urbanspoon カスタマー注文履歴">

<img src="https://raw.githubusercontent.com/Ako-in/laravel-restaurant-order/master/public/images/readme/customer/urbanspoon_customer_payment.png" height="250px" alt="Urbanspoon カスタマー支払い">

<img src="https://raw.githubusercontent.com/Ako-in/laravel-restaurant-order/master/public/images/readme/customer/urbanspoon_customer_afterpayment.png" height="250px" alt="Urbanspoon カスタマー支払い完了">

### 　管理者側の表示

<img src="https://raw.githubusercontent.com/Ako-in/laravel-restaurant-order/master/public/images/readme/admin/urbanspoon_admin_login.png" height="250px" alt="Urbanspoon 管理者ログイン">

<img src="https://raw.githubusercontent.com/Ako-in/laravel-restaurant-order/master/public/images/readme/admin/urbanspoon_admin_toppage.png" height="250px" alt="Urbanspoon 管理者トップページ">

<img src="https://raw.githubusercontent.com/Ako-in/laravel-restaurant-order/master/public/images/readme/admin/urbanspoon_admin_orderlist.png" height="250px" alt="Urbanspoon 管理者注文リスト">

<img src="https://raw.githubusercontent.com/Ako-in/laravel-restaurant-order/master/public/images/readme/admin/urbanspoon_admin_orderconfirm.png" height="250px" alt="Urbanspoon 管理者注文詳細">

<img src="https://raw.githubusercontent.com/Ako-in/laravel-restaurant-order/master/public/images/readme/admin/urbanspoon_admin_menutop2.png" height="250px" alt="Urbanspoon 管理者メニュー一覧">

<img src="https://raw.githubusercontent.com/Ako-in/laravel-restaurant-order/master/public/images/readme/admin/urbanspoon_admin_menuedit.png" height="250px" alt="Urbanspoon 管理者メニュー編集">

## 苦労したこと

## 今後の展望
