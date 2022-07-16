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

---

## Görev 1 - Siparişler

### Sipariş Listeleme

```http request
GET http://localhost/api/orders
```

### Sipariş Ekleme

```http request
POST http://localhost/api/orders
```

Sipariş eklendikten sonra ürün stok güncellemesi yapılır.

Örnek payload:

```json
{
    "customerId": 2,
    "items": [
        {
            "productId": 102,
            "quantity": 6,
            "unitPrice": 23.42
        },
        {
            "productId": 101,
            "quantity": 10,
            "unitPrice": 9.99
        }
    ]
}
```

### Sipariş Silme

```http request
DELETE http://localhost/api/orders/{order_id}
```

Sipariş silindikten sonra ürün stok güncellemesi yapılır.

---

## Görev 2 - İndirimler

### Indirimleri listele

```http request
GET http://localhost/api/discounts
```

### Indirim Ekle

```http request
POST http://localhost/api/discounts
```

Örnek payload

```javascript
const payload = {
    name: 'Buy 5 Get 1 free in categoryId 2 products',
    type: 'product', // cart veya product. Sepet veya ürün bazlı indirim
    filters: null, // Filtre. category, product seçilebilir. 
    // Filtre örnek: {category:2} anlamı sepetteki idsi 2 olan kategorideki ürünleri seçer.
    rule: {
        // İndirim koşulu
        condition: "{product.category} == 2 && {product.quantity} >= 6",
        // İndirim miktarı (php fonskiyonları kkulanılabilir)
        amount: "floor({product.quantity} / 6) * {product.unitPrice}"
    },
    priority: 2
}
```

İndirim kuralında kullanılabilecek değişkenler:

Sepet tipi indirimler için

| Degisken | Aciklama |
| -------- | -------- |
| {cart.total} | Sipariş toplamı veya önceki indirim varsa alttoplam. |
| {cart.vat} | Sipariş KDV miktarı. |
| {cart.quantity} | Siparişteki toplam ürün sayısı. |
| {cart.cheapest} | Siparişteki en ucuz ürün. Filtre geçersiz sayılır. |
| {cart.most_expensive} | Siparişteki en pahalı ürün. Filtre geçersiz sayılır. |


Ürün tipi indirimler için

| Degisken | Aciklama |
| -------- | -------- |
| {product.category} | Ürün kategori id. |
| {product.quantity} | Alınan ürün sayısı. |
| {product.unitPrice} | Ürünün birim fiyatı |
| {product.total} | Ürün toplam fiyat |
| {product.id} | Ürün id. |

Bu değişkenler arttırılabilir.

### Verilen Sipariş için İndirimleri Hesapla

```http request
GET http://localhost/api/discounts/{order_id}
```

İnidirim hesaplaması için açık kaynaklı ecommerce sitelerini inceledim ancak kendi istediğim tarzda kodlamak istedim.
Hem daha eğlenceli oldu benim için :)

Her bir indirim kuralı aşağıdaki adımlarla hesaplanmaktadır:

- Süresi geçmemiş bütün indirim kuralları veritabanından önem sırasına göre çağrılır.
- Her indirim kuralı döngü içinde çalıştırılır.
- Önce indirim kuralında filtre varsa o filtreye göre ürünler seçilir.
- Eğer filtre varsa ve hiç bir ürün filtreden geçmediyse o indirim kuralı hesaplanmaz. Null olarak döner.
- İnidirim kuralı sepet türündeyse (type) sipariş için değişkenler hesaplanır.
- İndirim kuralında condition ve amount formüllerinde bu değişkenler yerine konularak hesaplanır.
- Eğer indirim koşulu (condition) sağlanırsa indirim miktarı (amount) kadar alttoplamdan çıkarılarak geri dönderilir.
- Ürün tipi indirim kuralında ise aynı aşamalar yapılır. Tek fark her bir ürün çeşidi için koşul ve miktar heasplaması
  ayrı yapılır ve toplanır.

***Not:** İndirim kuralında koşul ve miktar formülleri hesaplanırken PHP eval fonksiyonu deneme amaçlı kullanılmıştır.
Daha sonra eval() yerine daha güvenli bir parser ve formül hesaplayıcı yapılması gerekmektedir.*

Örnek çıktı:

```javascript
const response = {
    "orderId": 3,
    "discounts": [
        {
            "discountReason": "Buy atleast 2 items in category 1, get %20 discount for the cheapest item",
            "discountAmount": 2.26,
            "subtotal": 1272.92
        },
        {
            "discountReason": "Buy 5 get 1 in category id 2",
            "discountAmount": 11.28,
            "subtotal": 1261.64
        },
        {
            "discountReason": "10 percent over 1000",
            "discountAmount": 126.16,
            "subtotal": 1135.48
        }
    ],
    "totalDiscount": 139.7,
    "discountedTotal": 1135.48
}
```

---

## Sunucuyu Kapatma

*Sunucuyu kapatmak icin ayni terminalden `CTLR + C` veya asagidaki komutu kullanabilirsiniz.*

```shell
docker compose down
```

