# coachtech-flea-market
## 環境構築
**Dockerビルド**
1. `git@github.com:seiya71/coachtech-flea-market.git`
2. DockerDesktopアプリを立ち上げる
3. `docker-compose up -d --build`
> *MacのM1・M2チップのPCの場合、`no matching manifest for linux/arm64/v8 in the manifest list entries`のメッセージが表示されビルドができないことがあります。
エラーが発生する場合は、docker-compose.ymlファイルの「mysql」内に「platform」の項目を追加で記載してください*
``` bash
mysql:
    platform: linux/x86_64(この文追加)
    image: mysql:8.0.26
    environment:
```

**Laravel環境構築**
1. `docker-compose exec php bash`
2. `composer install`
3. 「.env.example」ファイルを 「.env」ファイルに命名を変更。または、新しく.envファイルを作成
4. ストレージのリンクを作成
```
php artisan storage:link
```
5. .envに以下の環境変数を追加
``` text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

STRIPE_KEY=【Stripeの公開キーをここに入力】
STRIPE_SECRET=【Stripeの秘密キーをここに入力】

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=【Mailtrapのユーザー名をここに入力】
MAIL_PASSWORD=【Mailtrapのパスワードをここに入力】
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=【送信元のメールアドレス】
MAIL_FROM_NAME="${APP_NAME}"
```

6. キャッシュをクリア
```
php artisan config:clear
php artisan cache:clear
```
7. アプリケーションキーの作成
``` bash
php artisan key:generate
```

8. マイグレーションの実行
``` bash
php artisan migrate
```

9. シーディングの実行
``` bash
php artisan db:seed
```
## 使用技術（実行環境）
* nginx 1.21.1
* mysql 8.0.26
* php 7.4.9-fpm
* Laravel Framework 8.83.28
* Fortify
* Storage
* Stripe
* Mailtrap
## ER図
![/ER](/ER.drawio.png)
## URL
- 開発環境：http://localhost/
- phpMyAdmin:：http://localhost:8080/