# 環境構築

1. Dockerを起動する

2. プロジェクト直下で、以下のコマンドを実行する

```
make init
```

※ Makefileにより、環境構築に必要なコマンドをまとめて実行できます。

## メール認証
本アプリでは Mailhog を使用しています。

メールは以下のURLから確認できます。

http://localhost:8025

ユーザー登録後、認証メールが送信されるため、
Mailhog上でメールを確認し、認証リンクをクリックしてください。


## ER図
![alt](ER.png)

## テストアカウント
name: 一般ユーザ
email: general1@gmail.com
password: password
-------------------------
name: 一般ユーザ
email: general2@gmail.com
password: password
-------------------------

## PHPUnitを利用したテストに関して
以下のコマンド:
```
//テスト用データベースの作成
docker-compose exec mysql bash
mysql -u root -p
//パスワードはrootと入力
create database test_database;

docker-compose exec php bash
php artisan migrate:fresh --env=testing
./vendor/bin/phpunit
```
※.env.testingにもStripeのAPIキーを設定してください。
