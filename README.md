# Test Case Study

## Kurulum

- Kaynak kodlarını indirin:

```shell
git clone https://github.com/yyasinaslan/ideasoftcasestudy.git
```

- Gereken paketleri yükle

```shell
composer install
```

- Sunucuyu çalıştır\
  **Not:** *Bu kod sunucuyu arka planda calistirir. Kapatmak icin [sunucu kapatma kodunu](#sunucuyu-kapatma) kullanin*

```shell
docker compose up -d
```

- Veritabanını ilk ayarlarını yap

```shell
php artisan migrate
```

## Sunucuyu kapatma

```shell
docker compose down
```

