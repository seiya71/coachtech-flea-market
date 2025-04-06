# coachtech-flea-market
## 環境構築
**Dockerビルド**
1. `git clone git@github.com:seiya71/coachtech-flea-market.git`
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
9. シーディング用の商品画像の配置場所の作成
``` bash
mkdir -p storage/app/public/item_images
```
10. シーディング用の商品画像を設置

商品画像を `storage/app/public/item_images/` に保存する必要があります。  
以下を実行してください。
``` bash
curl -o storage/app/public/item_images/腕時計.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg"

curl -o storage/app/public/item_images/HDD.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg"

curl -o storage/app/public/item_images/玉ねぎ3束.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg"

curl -o storage/app/public/item_images/革靴.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg"

curl -o storage/app/public/item_images/ノートPC.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg"

curl -o storage/app/public/item_images/マイク.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg"

curl -o storage/app/public/item_images/ショルダーバッグ.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg"

curl -o storage/app/public/item_images/タンブラー.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg"

curl -o storage/app/public/item_images/コーヒーミル.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg"

curl -o storage/app/public/item_images/メイクセット.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg"
```
11.シンボリックリンクの作成
``` bash
php artisan storage:link
```

12. シーディングの実行
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
- phpMyAdmin：http://localhost:8080/