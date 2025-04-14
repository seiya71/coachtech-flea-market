# coachtech-flea-market
## 環境構築
**Dockerビルド**
1. `git clone git@github.com:seiya71/coachtech-flea-market.git`
2. `cd coachtech-flea-market`
3. `mkdir -p docker/mysql/data`
4. `docker-compose up -d --build`
> *MacのM1・M2チップのPCの場合、`no matching manifest for linux/arm64/v8 in the manifest list entries`のメッセージが表示されビルドができないことがあります。
エラーが発生する場合は、docker-compose.ymlファイルの「mysql」内に「platform」の項目を追加で記載してください*
``` bash
mysql:
    platform: linux/x86_64(この文追加)
    image: mysql:8.0.26
    environment:
```

**Laravel環境構築**
1. `docker-compose exec php composer install`
2. `cp src/.env.example src/.env`
3. .envに以下の環境変数を追加
``` text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

STRIPE_PUBLIC_KEY=pk_test_51Qlt38LRILPtxq29bpg4uf2EoD5hLJGMmU6Sfbe40fpKfryoGcVt7ck7mojrEYNl1DB3aaWS2YLVXndFzmm4efFN00yYfGrAuM
STRIPE_SECRET_KEY=sk_test_51Qlt38LRILPtxq29UPrq6N1OS9UD5cahH6c70HQrJlfIbZkzKSGAwl7R15OhRZycuyD8EBojZoLI38cimJQFWurG00joby3YaH

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=8b045afad1fae6
MAIL_PASSWORD=0ef65a6fa989fd
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```
4. キャッシュをクリア
```
docker-compose exec php php artisan config:clear
docker-compose exec php php artisan cache:clear
```
5. アプリケーションキーの作成
``` bash
docker-compose exec php php artisan key:generate
```
6. 権限の調整（エラー防止のため）

Laravelの動作に必要な書き込み権限を付与します：

```bash
docker-compose exec php chmod -R 775 storage
docker-compose exec php chmod -R 775 bootstrap/cache
```

7. `docker-compose exec php php artisan storage:link`

8. マイグレーションの実行
``` bash
docker-compose exec php php artisan migrate
```
9. シーディング用の商品画像の配置場所の作成
``` bash
mkdir -p src/storage/app/public/item_images
```
10. シーディング用の商品画像を設置

商品画像を `storage/app/public/item_images/` に保存する必要があります。  
以下を実行してください。
``` bash
curl -o src/storage/app/public/item_images/腕時計.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg"

curl -o src/storage/app/public/item_images/HDD.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg"

curl -o src/storage/app/public/item_images/玉ねぎ3束.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg"

curl -o src/storage/app/public/item_images/革靴.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg"

curl -o src/storage/app/public/item_images/ノートPC.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg"

curl -o src/storage/app/public/item_images/マイク.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg"

curl -o src/storage/app/public/item_images/ショルダーバッグ.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg"

curl -o src/storage/app/public/item_images/タンブラー.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg"

curl -o src/storage/app/public/item_images/コーヒーミル.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg"

curl -o src/storage/app/public/item_images/メイクセット.png "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg"
```
11. シーディングの実行
``` bash
docker-compose exec php php artisan db:seed
```
**テスト環境構築**
1. `.env.testing.example` を `.env.testing` にコピー
```bash
cp src/.env.testing.example src/.env.testing
```
2.アプリケーションキーの生成
```bash
docker-compose exec php php artisan key:generate --env=testing
```
3.テスト用データベースのマイグレーション
```bash
docker-compose exec php php artisan migrate --env=testing
```
4.テストの実行（必ずこちらのコマンドでテストを実行してください）
```bash
docker-compose exec php php artisan test --env=testing
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
- Mailtrap：https://mailtrap.io/inboxes
### よくあるエラーと対処法

#### 🔸 419 Page Expired が出る
- `.env`に`APP_KEY`が設定されていない可能性があります。`php artisan key:generate` を実行してください。
- セッションが正しく保存されていない可能性があります。`SESSION_DRIVER=file` が設定されているか確認してください。
- キャッシュの不整合があるかもしれません。以下のコマンドを実行してください：

```bash
php artisan config:clear
php artisan cache:clear
```