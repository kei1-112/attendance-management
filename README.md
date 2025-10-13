# coachtech勤怠管理

## 環境構築

## Dockerビルド

1. git clone git@github.com:coachtech-material/laravel-docker-template.git attendance-management
2. docker-compose.ymlのmysqlイメージにplatform: linux/amd64を追記
3. docker-compose.ymlのサービスに以下を追記
    mailhog:
    image: mailhog/mailhog
    container_name: mailhog-attendance
    ports:
      - "1025:1025"
      - "8025:8025"
4. docker-compose up -d --build

## Laravel環境構築

1. docker-compose exec php bash
2. composer install
3. .env.exampleから.envを作成、環境変数を変更
4. .envファイルのMAIL_FROM_ADDRESSをexample@test.comに変更
5. php artisan key:generate
6. composer require laravel/fortify
7. php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"
8. php artisan storage:link
9. php artisan migrate
10. php artisan db:seed

## PHPUnit環境準備
1. docker-compose exec php bash
2. composer config platform.php 8.1.13
3. composer require -dev phpunit/phpunit
4. exit
5. docker-compose exec mysql
6. mysql -u root -p
7. root
8. CREATE DATABASE laravel_test_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
9. GRANT ALL PRIVILEGES ON laravel_test_db.* TO 'laravel_user'@'%';
10. FLUSH PRIVILEGES;
11. .envから.env.testingを作成、DB_DATABASE=laravel_test_db
に変更
12. exit
13. exit
14. docker-compose exec php bash
15. php artisan migrate --env=testing


## 使用技術

- PHP 7.4.9
- Laravel 8.83.8
- MySQL 8.0.26
- Fortify 1.19.1

## ER図

![ER図](src/er-diagram.png)

## ログイン情報
- メールアドレス:test@example.com
- パスワード:password

## URL

- 開発環境:http://localhost/
- phpMyAdmin:http://localhost:8080/