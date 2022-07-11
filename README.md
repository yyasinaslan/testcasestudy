# Test Case Study

## Kurulum

- Kaynak kodlarını indirin

```shell
git clone https://github.com/yyasinaslan/testcasestudy.git

cd testcasestudy
```

- Sunucuyu çalıştır

```shell
docker compose up
```

Sunucu calistiktan sonra `init.sh` dosyasindaki komutlari otomatik calistirir. Bu komutlar gerekli paketleri ve
veritabani tablolarini kurmaya yarar.

API'ye http://localhost:80/api uzerinden erisebilirsiniz.

## API Dokumantasyon

API dokumantasyonu icin [http://localhost/docs](http://localhost/docs) linkini kullanabilirsiniz.

## Sunucuyu Kapatma

*Sunucuyu kapatmak icin ayni terminalden `CTLR + C` veya asagidaki komutu kullanabilirsiniz.*

```shell
docker compose down
```

