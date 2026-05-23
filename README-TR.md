# FonPageBuilder — Özel Widget Sistemi

Bu belge, FonPageBuilder'a **kendi özel widget'larınızı** nasıl ekleyeceğinizi ve
tüm desteklenen **field tiplerini** nasıl kullanacağınızı açıklar.

---

## 🖥️ Frontend Render Akışı

Builder tarafında widget konfigürasyonu (`manifest.json`, `config.php`, `lang/*`) tanımlandıktan sonra frontend render'ı tema tarafında yapılır.

```
templates/website/{tema_adi}/builder/
├── custom-widgets/
│   ├── example_card/
│   │   ├── renderer.php            ← Zorunlu: render sınıfı
│   └── notice_box/
│       ├── renderer.php            ← Zorunlu: render sınıfı
```

### Renderer sınıf kuralı

- Dosya: `templates/website/{tema_adi}/builder/custom-widgets/{type}/renderer.php`
- Sınıf adı: `FonCustomWidgetRenderer_{type}`
- Zorunlu statik metod: `render(object $theme, object $content, string $moduleId, array $pageData): string`

`$theme` nesnesi renderer içinde aktif olarak kullanılabilir:

- Asset kaydı (opsiyonel): `$theme->registerWidgetAsset($assetOrArray)`
- Inline CSS kaydı (opsiyonel): `$theme->registerWidgetStyle($page, $css)`
- Inline JS kaydı (opsiyonel): `$theme->registerWidgetScript('footer', $page, $js)`
- `textLanguage`, `textAreaLanguage` Çok dilli alan türleri değer çözümleme: `$theme::getFieldMultilingualText($field, $lang, $default)`
- `typography` alan türü CSS Render Fonksiyonu: `$theme::parseFontProperties($field)`
- `responsive` alan CSS Render Fonksiyonu: `$theme::parseResponsiveItems($responsiveItems, $widgetId)`

### Özel Bileşenlerde Harici Kütüphane (Asset) Kullanımı

Geliştirdiğiniz özel bileşen (widget) harici bir CSS veya JS kütüphanesine ihtiyaç duyuyorsa (Örn: *lightgallery, Swiper, Select2 vb.*), bu kütüphanelerin sitenize **yalnızca o bileşen sayfada kullanıldığında** yüklenmesini sağlayabilirsiniz. Bu sayede siteniz gereksiz kod yükünden kurtulur ve performans kaybetmez.

Sistem, renderer içindeki varlık (asset) kayıtlarını otomatik olarak $hoptions global dizisine aktarır.


#### Uygulama Adımları (lightgallery Örneği)

Eğer özel bileşeninizde bir galeri kütüphanesi olan lightgallery kullanmak istiyorsanız, sırasıyla şu 3 adımı uygulamalısınız:

###### 1. Adım: Bileşen İçinde Asset'i Kaydetme
Bileşeninizin renderer.php dosyası içerisindeki `addWidgetAssets` metodunda, kütüphanenizin benzersiz kimliğini (slug) sisteme bildirin. Örneğin: `$requiredAssets = 'lightgallery';` Birden fazla varlık eklemek isterseniz, bunları bir dizi içinde tanımlayabilirsiniz. Örneğin: `$requiredAssets = ['lightgallery', 'swiper', 'select2']` gibi.

```php
private static function addWidgetAssets(
        object $theme
    ): void {
    $requiredAssets = ['lightgallery']; // Eklemek istediğiniz asset'leri bu değişkene atayabilirsiniz. Örneğin: ['swiper', 'magnific', 'select2'] vs.

    if (!empty($requiredAssets) && is_callable([$theme, 'registerWidgetAsset'])) {
        $theme->registerWidgetAsset($requiredAssets);
    }

}
```
###### 2. Adım: Kütüphane Dosyalarını Temaya Yükleme
Kullanacağınız kütüphanenin kaynak dosyalarını ilgili tema klasörünüzün altındaki css ve js dizinlerine yükleyin:

- `templates/website/{tema_adi}/css/lightgallery.css`
- `templates/website/{tema_adi}/js/lightgallery.js`

###### 3. Adım: Dosyaları Asset Yöneticisine Tanımlama
Sistem, registerWidgetAsset ile talep ettiğiniz anahtarı css_assets.php ve js_assets.php dosyalarında kontrol etmenizi sağlar.

- builder/custom_assets/css_assets.php dosyasını açın ve kütüphanenin stil dosyalarını bağlayın:

```php
<?php defined('CORE_FOLDER') OR exit('You can not get in here!'); ?>

<?php if (isset($hoptions) && in_array("lightgallery", $hoptions, true)): ?>
    <link rel="stylesheet" href="<?php echo $tadress; ?>css/lightgallery.css">
<?php endif; ?>

```
- builder/custom_assets/js_assets.php dosyasını açın ve kütüphanenin betik dosyalarını bağlayın:

```php
<?php defined('CORE_FOLDER') OR exit('You can not get in here!'); ?>

<?php if (isset($hoptions) && in_array("lightgallery", $hoptions, true)): ?>
    <script src="<?php echo $tadress; ?>js/lightgallery.js"></script>   
<?php endif; ?>

```
> 💡 **Alternatif ve Genel Kullanım Notu**
>
> Eğer ekleyeceğiniz kütüphane veya özel kodların (CSS/JS) modüler olmasını istemiyor ve sitenizin **tüm sayfalarında aktif olarak yüklenmesini** tercih ediyorsanız; bu kodları doğrudan aşağıdaki genel dosyalara dahil edebilirsiniz:
> - `templates/website/{tema_adi}/css/custom.css`
> - `templates/website/{tema_adi}/js/custom.js`
>
> **Önemli Ayrım:** Bu yöntem kod yönetimini tek bir dosyada toplasa da, ilgili CSS ve JS dosyalarının sitenizin tamamında (gerekli olsun veya olmasın) her sayfa açılışında yükleneceğini ve tarayıcı tarafından önbelleğe alınacağını (cache) unutmamalısınız. Performans odaklı özel bileşen geliştirmelerinde ise `registerWidgetAsset` yöntemi (sayfaya özel yükleme) her zaman en iyi pratiktir (best practice).
> 
---

### Fallback davranışı

`theme.php` içindeki `callWidget()` önce built-in methodu (`{type}_widget`) arar.
Method yoksa otomatik olarak yukarıdaki renderer dosyasını yükleyip çağırır.
Renderer bulunamazsa güvenli şekilde boş string döner.

### `widget_style` davranışı

- `config.php` içinde `style_preview` kullanıyorsanız alan adı **mutlaka** `widget_style` olmalıdır.
- `widget_style.values` içinde tanımlanan **her style anahtarı** için widget clasınızda `renderWidgetStyle{StyleAdi}` fonksyionu bulunmalıdır. Örn: renderWidgetStyleDefault, renderWidgetStyleModern

---

## 📁 Admin Dizin Yapısı
coremio/modules/Addons/FonPageBuilder içindeki `builder/custom-widgets/` klasöründe tüm özel widget'larınızın konfigürasyon dosyaları bulunur.

```
builder/
├── custom-widgets/              ← Tüm özel widget'larınız burada
│   ├── example_card/            ← Örnek widget (type = "example_card")
│   │   ├── manifest.json        ← Zorunlu: Widget meta verisi
│   │   ├── config.php           ← Zorunlu: Widget konfigürasyon sınıfı
│   │   └── lang/
│   │       ├── en.php           ← Zorunlu: İngilizce dil dosyası
│   │       └── tr.php           ← Türkçe dil dosyası
│   └── notice_box/              ← Başka bir örnek widget
│       ├── manifest.json
│       ├── config.php
│       └── lang/
│           ├── en.php
│           └── tr.php
├── CustomWidgetRegistry.php     ← Otomatik keşif ve güvenlik katmanı (DEĞİŞTİRMEYİN)
└── PageData.php                 ← Built-in widget listesi (DEĞİŞTİRMEYİN)
```

---

## 🚀 Admin - Yeni Widget Oluşturma

### Adım 1: Dizin oluşturun
Ana çalışma dizini coremio/modules/Addons/FonPageBuilder/builder/custom-widgets/ altında widget tipinizle aynı adı taşıyan bir klasör oluşturun.

```
builder/custom-widgets/{widget_type}/
```

**Kural:** `{widget_type}` sadece küçük harf, rakam ve alt çizgi içerebilir.
Uzunluk: 3–40 karakter. Küçük harfle başlamalıdır.
**Örnek:** `my_pricing_table`, `faq_box`, `social_links`

---

### Adım 2: `manifest.json` dosyası oluşturun


```json
{
    "type": "my_pricing_table",
    "name": "Pricing Table",
    "group": "content",
    "icon": "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" ...>...</svg>",
    "show": [],
    "version": "1.0.0",
    "author": "Adınız"
}
```

| Alan | Açıklama | Zorunlu |
|------|----------|---------|
| `type` | Widget tipi (dizin adıyla aynı olmalı) | ✅ |
| `name` | Varsayılan görünen ad | ✅ |
| `group` | `content`, `media`, `module`, `other` | ✅ |
| `icon` | SVG veya `<i class="...">` ikonevi | — |
| `show` | Sayfa kısıtlamaları (aşağıya bakın) | — |

**`show` alanı örnekleri:**
```json
// Yalnızca belirli sayfa türlerinde göster
"show": { "only": ["hosting", "server"] }

// Belirli sayfa türlerinde gösterme
"show": { "except": ["domain", "transfer"] }

// Her sayfada göster
"show": []
```

---

### Adım 3: `config.php` dosyası oluşturun
'name_widget' alanı zorunludur ve sadece yönetim panelinde bileşen listelemesinde bileşeni tanımlamak için kullanılır.

```php
<?php
class FonCustomWidget_my_pricing_table
{
    public static function getConfig(array $language, array $widgetData): array
    {
        $widget        = $widgetData['widget'] ?? [];
        $multiLanguage = explode(',', (string)($widgetData['language'] ?? 'en'));
        $currentLang   = $widgetData['currentLang'] ?? 'en';

        // ... değerleri hazırla ...

        return [
            'general' => [
                'title'  => $language['widget_general_settings'] ?? 'Genel Ayarlar',
                'icon'   => 'fa fa-cog',
                'fields' => [
                    //Bu alan zorunludur ve widget listesinde bileşeni tanımlamak için kullanılır.
                    'name_widget' => [ 
                        'type'    => 'textLanguage',
                        'default' => 'Fiyatlandırma Tablosu',
                        'values'  => [], // çok dilli değerler
                        'name'    => $language['widget_name_widget'] ?? 'Widget Adı',
                        'desc'    => '',
                        'inline'  => true,
                    ], 
                ],
            ],
            'content' => [
                'title'  => 'İçerik',
                'icon'   => 'far fa-file-alt',
                'fields' => [
                    // ... alanlarınız ...
                ],
            ],
        ];
    }
}
```

**Kural:** Sınıf adı `FonCustomWidget_{type}` formatında OLMAK ZORUNDADIR.

---

### Adım 4: Dil dosyası oluşturun (`lang/en.php`)

```php
<?php
return [
    'widget_name'        => 'My Pricing Table',
    'widget_description' => 'A beautiful pricing table widget.',
    'field_plan_name'    => 'Plan Name',
    'field_price'        => 'Price',
];
```

> ⚠️ Dil dosyasında yalnızca `string key → string value` çiftlerine izin verilir.
> Anahtarlar `[a-z][a-z0-9_]*` deseniyle eşleşmelidir.

---

## 🎨 Desteklenen Field Tipleri

FonPageBuilder'ın `formField()` motoru aşağıdaki tüm field tiplerini destekler.
Her tip için zorunlu/opsiyonel parametreler ve örnek kullanım aşağıda açıklanmıştır.

---

### `text` — Tek satır metin kutusu

```php
'alan_adi' => [
    'type'        => 'text',          // ✅ zorunlu
    'default'     => 'varsayılan',    // ✅ zorunlu
    'name'        => 'Alan Başlığı',  // ✅ zorunlu
    'desc'        => 'Açıklama',      // zorunlu (boş bırakılabilir)
    'placeholder' => 'İpucu...',      // opsiyonel
    'inline'      => true,            // opsiyonel (bool, varsayılan false)
    'responsive'  => false,           // opsiyonel — true ise breakpoint'e göre değer
    'addon'       => ['right' => 'px', 'left' => ''], // opsiyonel
    'as_title'    => false,           // opsiyonel — Repeatable alanlarda akordiyon field taşıcısının başlığı olarak göstermek/güncellemek içindir. Kullanılmazsa #Item1, #Item2 gibi akordiyon başlıkları gösterilir.
    'depend'      => [/*...*/],       // opsiyonel — koşullu görünürlük
],
```

**`responsive: true` için `default`:**
```php
'default' => ["xl" => "", "lg" => "", "md" => "", "sm" => "", "xs" => ""],
```

---

### `textLanguage` — Çok dilli tek satır metin

```php
'alan_adi' => [
    'type'        => 'textLanguage',
    'default'     => 'Varsayılan metin',
    'values'      => ['tr' => 'Türkçe metin', 'en' => 'English text'],
    'name'        => 'Alan Başlığı',
    'desc'        => '',
    'placeholder' => '',            // opsiyonel
    'inline'      => true,          // opsiyonel
    'as_title'    => false,         // opsiyonel
    'depend'      => [/*...*/],     // opsiyonel
],
```

> `values` dizisi her aktif dil kodu için kaydedilmiş değerleri içerir.

---

### `textarea` — Çok satır metin

```php
'alan_adi' => [
    'type'        => 'textarea',
    'default'     => '',
    'name'        => 'Alan Başlığı',
    'desc'        => '',
    'placeholder' => '',   // opsiyonel
    'inline'      => true, // opsiyonel
],
```

---

### `textareaLanguage` — Çok dilli çok satır metin

```php
'alan_adi' => [
    'type'    => 'textareaLanguage',
    'default' => 'Varsayılan',
    'values'  => ['tr' => 'Türkçe', 'en' => 'English'],
    'name'    => 'Alan Başlığı',
    'desc'    => '',
    'inline'  => true, // opsiyonel
],
```

---

### `textareaEditor` — HTML zengin metin editörü (Summernote)

```php
'alan_adi' => [
    'type'    => 'textareaEditor',
    'default' => '<p>İçerik</p>',
    'name'    => 'Alan Başlığı',
    'desc'    => '',
    'inline'  => true, // opsiyonel
],
```

---

### `textareaEditorLanguage` — Çok dilli HTML zengin metin editörü

```php
'alan_adi' => [
    'type'    => 'textareaEditorLanguage',
    'default' => '',
    'values'  => ['tr' => '<p>Türkçe</p>', 'en' => '<p>English</p>'],
    'name'    => 'Alan Başlığı',
    'desc'    => '',
    'inline'  => true, // opsiyonel
],
```

---

### `hidden` — Gizli input

```php
'alan_adi' => [
    'type'    => 'hidden',
    'default' => 'sabit_deger',
    'name'    => '',
    'desc'    => '',
],
```

---

### `number` — Sayı girişi

```php
'alan_adi' => [
    'type'        => 'number',
    'default'     => '10',
    'name'        => 'Alan Başlığı',
    'desc'        => '',
    'min'         => 0,       // ✅ zorunlu
    'max'         => 100,     // ✅ zorunlu
    'step'        => 1,       // ✅ zorunlu
    'inline'      => true,    // opsiyonel
    'responsive'  => false,   // opsiyonel
    'addon'       => ['right' => 'px', 'left' => ''], // opsiyonel
    'placeholder' => '',      // opsiyonel
    'depend'      => [/*...*/], // opsiyonel
],
```

---

### `select` — Açılır seçim listesi

```php
'alan_adi' => [
    'type'      => 'select',
    'default'   => 'secenek1',         // tek değer veya dizi (multiple için)
    'name'      => 'Alan Başlığı',
    'desc'      => '',
    'values'    => [                   // ✅ zorunlu — ['değer' => 'Etiket', ...]
        'secenek1' => 'Seçenek 1',
        'secenek2' => 'Seçenek 2',
    ],
    'inline'    => true,               // opsiyonel
    'multiple'  => false,              // opsiyonel — çoklu seçim
    'class'     => '',                 // opsiyonel — ek CSS sınıfı
    'responsive'=> false,              // opsiyonel
    'addon'     => ['right' => ''],    // opsiyonel
    'depend'    => [/*...*/],          // opsiyonel
],
```

---

### `bool` — Evet/Hayır toggle düğmesi

```php
'alan_adi' => [
    'type'    => 'bool',
    'default' => 'yes',    // ✅ 'yes' veya 'no'
    'name'    => 'Alan Başlığı',
    'desc'    => '',
    'inline'  => true,     // opsiyonel
    'depend'  => [/*...*/], // opsiyonel
],
```

> ⚠️ `default` değeri `'yes'` veya `'no'` olmalıdır — `'1'`/`'0'` veya `true`/`false` DEĞIL.

---

### `slider` — Kaydırıcı (range slider)

```php
'alan_adi' => [
    'type'    => 'slider',
    'default' => '50',
    'name'    => 'Alan Başlığı',
    'desc'    => '',
    'min'     => 0,       // ✅ zorunlu
    'max'     => 100,     // ✅ zorunlu
    'step'    => 1,       // ✅ zorunlu
    'decimal' => 0,       // opsiyonel — ondalık basamak sayısı
    'inline'  => true,    // opsiyonel
    'depend'  => [/*...*/], // opsiyonel
],
```

---

### `color` — Renk seçici

```php
'alan_adi' => [
    'type'    => 'color',
    'default' => '#3366FF',    // HEX, RGB veya RGBA
    'name'    => 'Alan Başlığı',
    'desc'    => '',
    'inline'  => true,         // opsiyonel
    'depend'  => [/*...*/],    // opsiyonel
],
```

---

### `gradient` — Gradient renk seçici

```php
'alan_adi' => [
    'type'    => 'gradient',
    'default' => [
        'sc'  => 'rgba(0,122,122,1)',   // başlangıç rengi
        'ec'  => 'rgba(255,156,1,1)',   // bitiş rengi
        'scp' => '0',                   // başlangıç pozisyonu (%)
        'ecp' => '100',                 // bitiş pozisyonu (%)
        'gt'  => 'linear',              // 'linear' veya 'radial'
        'lga' => '45',                  // linear açı (derece)
        'rga' => 'center_center',       // radial pozisyon
    ],
    'name'    => 'Alan Başlığı',
    'desc'    => '',
    'inline'  => true, // opsiyonel
],
```

---

### `icon` — FontAwesome ikon seçici

```php
'alan_adi' => [
    'type'    => 'icon',
    'default' => 'fas fa-star',    // FontAwesome sınıfı
    'name'    => 'Alan Başlığı',
    'desc'    => '',
    'inline'  => true,             // opsiyonel
    'depend'  => [/*...*/],        // opsiyonel
],
```

---

### `image` — Resim seçici

```php
'alan_adi' => [
    'type'    => 'image',
    'default' => '',    // yol veya URL; boş = placeholder gösterilir
    'name'    => 'Alan Başlığı',
    'desc'    => '',
    'inline'  => true,  // opsiyonel
],
```

---

### `imageLanguage` — Çok dilli resim seçici

```php
'alan_adi' => [
    'type'    => 'imageLanguage',
    'default' => '',
    'values'  => ['tr' => '/resim-tr.jpg', 'en' => '/resim-en.jpg'],
    'name'    => 'Alan Başlığı',
    'desc'    => '',
    'inline'  => true, // opsiyonel
],
```

---

### `video` — Video seçici

```php
'alan_adi' => [
    'type'    => 'video',
    'default' => '',    // .mp4 video yolu veya URL
    'name'    => 'Alan Başlığı',
    'desc'    => '',
    'inline'  => true, // opsiyonel
],
```

---

### `videoLanguage` — Çok dilli video seçici

```php
'alan_adi' => [
    'type'    => 'videoLanguage',
    'default' => '',
    'values'  => ['tr' => '/video-tr.mp4', 'en' => '/video-en.mp4'],
    'name'    => 'Alan Başlığı',
    'desc'    => '',
    'inline'  => true, // opsiyonel
],
```

---

### `typography` — Tipografi (font) ayarları

```php
'alan_adi' => [
    'type'       => 'typography',
    'default'    => PageData::defaultTypography(),  // veya el ile dizi
    'name'       => 'Alan Başlığı',
    'desc'       => '',
    'inline'     => true,   // opsiyonel
    'text-align' => true,   // opsiyonel — false: metin hizalama bölümünü gizle
],
```

**`PageData::defaultTypography()` yapısı:**
```php
[
    'font-size'      => ['value' => '', 'unit' => 'px'],
    'line-height'    => ['value' => '', 'unit' => 'px'],
    'letter-spacing' => ['value' => '', 'unit' => 'px'],
    'font-style'     => ['underline' => '0', 'italic' => '0', 'uppercase' => '0'],
    'font-weight'    => '0',
    'text-align'     => ['left' => '0', 'center' => '0', 'right' => '0'],
]
```

---

### `border` — Kenarlık seçici

```php
'alan_adi' => [
    'type'    => 'border',
    'default' => 'none',             // 'none' veya '1px solid #000000'
    'name'    => 'Alan Başlığı',
    'desc'    => '',
    'inline'  => true, // opsiyonel
],
```

---

### `shadow` — Gölge seçici

```php
'alan_adi' => [
    'type'    => 'shadow',
    'default' => 'none',             // 'none' veya '0px 4px 8px #000000'
    'name'    => 'Alan Başlığı',
    'desc'    => '',
    'inline'  => true, // opsiyonel
],
```

---

### `headings` — HTML başlık etiketi seçici

```php
'alan_adi' => [
    'type'    => 'headings',
    'default' => 'h3',    // h1, h2, h3, h4, h5, h6, div, p
    'name'    => 'Alan Başlığı',
    'desc'    => '',
    'inline'  => true,    // opsiyonel
    'depend'  => [/*...*/], // opsiyonel
],
```

---

### `datetime` — Tarih/Saat girişi

```php
'alan_adi' => [
    'type'    => 'datetime',
    'default' => '',
    'name'    => 'Alan Başlığı',
    'desc'    => '',
    'kind'    => 'date',    // 'date', 'time' veya 'datetime-local'
    'min'     => '',        // opsiyonel — minimum tarih
    'addon'   => [],        // opsiyonel
    'inline'  => true,      // opsiyonel
],
```

---

### `style_preview` — Widget Style Önizlemeli stil seçici

> ⚠️ Bu alanın anahtarı **daima** `widget_style` olmalıdır.
> Frontend renderer bu anahtarı kullanarak farklı sitiller oluşturmanıza izin verir.

```php
'widget_style' => [
    'type'     => 'style_preview',
    'default'  => 'style1',
    'name'     => 'Alan Başlığı',
    'desc'     => '',
    'values'   => [             // ✅ zorunlu
        'style1' => 'Stil 1',
        'style2' => 'Stil 2',
    ],
    'previews' => [             // ✅ zorunlu — her değer için önizleme görseli URL'i
        'style1' => '/resim/style1.png',
        'style2' => '/resim/style2.png',
    ],
    'inline'   => true, // opsiyonel
],
```

---

### `seperator` — Görsel bölüm ayırıcı

```php
'alan_adi' => [
    'type'    => 'seperator',
    'default' => 'Bölüm Başlığı',  // Gösterilecek başlık metni
    'name'    => '',
    'desc'    => '',
],
```

---

### `info` — Bilgi/uyarı notu

```php
'alan_adi' => [
    'type'    => 'info',
    'default' => 'Açıklama mesajı.',
    'style'   => 'info',                   // ✅ zorunlu: info, warning, success, danger, primary
    'icon'    => 'fas fa-info-circle',     // ✅ zorunlu: ikon CSS sınıfı
    'name'    => '',
    'desc'    => '',
],
```

---

### `category` — Ürün kategorisi seçici

```php
'alan_adi' => [
    'type'    => 'category',
    'default' => 'hosting:5,8',    // '{tip}:{id,id}' formatı veya boş - alabileceği değerler: 'hosting:1,2,3', 'server:4,5', 'special:6', 'sms:7', 'software:8',
    'name'    => 'Alan Başlığı',
    'desc'    => '',
    'inline'  => true, // opsiyonel
],
```

---

### `sources` — Kaynak/sayfa seçici

```php
'alan_adi' => [
    'type'        => 'sources',
    'default'     => 'none',        // 'none', 'items:1,2,3' veya 'cats:4,5'
    'source_type' => 'hosting',     // ✅ zorunlu — 'hosting', 'server', 'special', 'sms'
    'name'        => 'Alan Başlığı',
    'desc'        => '',
    'inline'      => true,          // opsiyonel
],
```

---

### `package` — Paket / Ürün seçici
Sitenizdeki ürün/paket kategorilerinden seçim yapmaya yarar. `sources` alanına benzer şekilde çalışır ancak sadece ürün/paket ögelerini listeler. Bağımsız olarak istediğiniz paketler seçilebilir. `plan_group` parametresi ile ürün grubu bazında filtreleme yapılabilir. Bu tanımlama isteğe bağlıdır `all` değeri girilirse hiçbir ürün grubu gösterilmeden sadece tüm paketler listelenir ve seçilebilir. `hosting` gibi bir ürün grubu belirtilirse, sadece o ürün grubuna ait paketler gösterilir ve seçilebilir. Hiç tanımlanmamışsa koşullu seçenek listesiyle önce ürün grubu altında ise paket seçme listesi gösterilir. `plan_group` parametresi tanımlanmışsa, `default` değeri bu plana ait paketler arasından seçilmelidir. Örneğin `hosting:1,2` → Hosting ürün id'si 1 ve 2 olan paketler seçilir. `server:5,8` → Sunucu ürün id'si 5 ve 8 olan paketler seçilir.
```php
'alan_adi' => [
    'type'        => 'package',
    'default'     => 'all:none', // 'all:none' veya product_group:1,2,3 formatı. Örneğin 'hosting:1,2' → Hosting ürün id'si 1 ve 2 olan paketler seçilir. 'server:5,8' → Sunucu ürün id'si 5 ve 8 olan paketler seçilir.
    'plan_group' => 'all',     // 🧩 opsiyonel — 'all', 'hosting', 'server', 'special', 'sms'  
    'name'        => 'Alan Başlığı',
    'desc'        => '',
    'inline'      => true,          // opsiyonel
],
```

---

### `repeatable` — Dinamik yinelenen öge listesi

Kullanıcının çalışma zamanında yeni satırlar ekleyip silebileceği dinamik liste oluşturur.
`child` anahtarı `getRepeatableItems()` ile üretilir — her eleman bir satırın field tanımlarıdır.

```php
// getConfig() içinde:
'items' => [
    'type'  => 'repeatable',
    'name'  => 'İtem Listesi',   // panel başlığı
    'child' => self::getRepeatableItems($language, $widgetData),
],
```

**`getRepeatableItems()` — Tüm satırları döndürür:**
```php
protected static function getRepeatableItems(array $language, array $widgetData): array
{
    $widget = $widgetData['widget'] ?? [];
    $data   = [
        'language'      => $language,
        'currentLang'   => $widgetData['currentLang']  ?? 'en',
        'multilanguage' => explode(',', (string)($widgetData['language'] ?? 'en')),
    ];

    $items = [];

    if (!empty($widget['items']) && is_array($widget['items'])) {
        foreach ($widget['items'] as $id => $item) {
            if ($item) {
                $items[] = self::getRepeatableItem((int) $id, $item, $data);
            }
        }
    } else {
        // Kayıtlı veri yoksa tek boş varsayılan satır oluştur
        $items[] = self::getRepeatableItem(0, [], $data);
    }

    return $items;
}
```

**`getRepeatableItem()` — Tek satırın field tanımlarını döndürür:**
```php
protected static function getRepeatableItem(int $id, array $item, array $data): array
{
    return [
        'alan_adi' => [
            'type'    => 'text',
            'default' => !empty($item['alan_adi']) ? $item['alan_adi'] : '',
            'name'    => $data['language']['field_alan_adi'] ?? 'Alan Adı',
            'desc'    => '',
        ],
        // ... diğer field tanımları
    ];
}
```

> ⚠️ `getRepeatableItem()` içindeki `'child'` anahtarlı alanlara **flat (düz)** biçimde erişilir.
> Detaylar için aşağıdaki `child` bölümüne bakın.

---

### `child_repeatable` — İç içe yinelenen öge listesi

`repeatable` bir ögenin **içinde**, kullanıcının yine ekleyip silebileceği alt liste oluşturur.

```php
'sosyal_linkler' => [
    'type'    => 'child_repeatable',
    'name'    => 'Sosyal Linkler',
    'desc'    => '',
    // Kaydedilmiş veri varsa onu kullan; yoksa alan yapısına uygun
    // varsayılan satır(lar) ata — her dizi elemanı bir satırı temsil eder
    // ve 'values' içindeki 'name' anahtarlarıyla eşleşmelidir.
    'default' => !empty($item['sosyal_linkler']) ? $item['sosyal_linkler'] : [
        [
            'icon' => 'fab fa-facebook',
            'url'  => 'https://facebook.com',
        ],
        [
            'icon' => 'fab fa-twitter',
            'url'  => 'https://twitter.com',
        ],
    ],
    // 'values' dizisi: Her satırda hangi alt alanların olduğunu tanımlar ve bunların formda nasıl görüneceğini belirler.
    // Kullanılabilecek Alan (field) 'type' değerleri: 'image', 'text', 'number', 'textLanguage', 'select', 'checkbox','color ve 'icon'
    'values'  => [                          // ✅ zorunlu — sütun tanımları
        [
            'name' => 'icon',               // kaydedilecek data anahtarı
            'lang' => 'İkon',              // formda görünen etiket
            'type' => 'icon',              // field tipi
        ],
        [
            'name' => 'url',
            'lang' => 'Link',
            'type' => 'text',
        ],
    ],
    'inline'  => true,                      // opsiyonel
],
```

> ✅ `default` dizisinin her elemanı, `values` içindeki `'name'` anahtarlarıyla **birebir eşleşmelidir**.
> Yani yukarıdaki örnekte her satır `['icon' => '...', 'url' => '...']` biçiminde olmalıdır.
> Boş varsayılan istiyorsanız `'default' => !empty($item['alan']) ? $item['alan'] : []` kullanabilirsiniz.

| Parametre | Açıklama | Zorunlu |
|-----------|----------|---------|
| `type` | `'child_repeatable'` | ✅ |
| `name` | Panel etiket başlığı | ✅ |
| `default` | Başlangıç verileri (dizi dizisi) | ✅ |
| `values` | Her sütunu tanımlayan dizi (`name`, `lang`, `type`) | ✅ |
| `desc` | Açıklama | — |
| `inline` | Satır içi görünüm | — |

**Veriye erişim:**
```php
// $item['sosyal_linkler'] → [['icon' => 'fab fa-facebook', 'url' => '...'], ...]
foreach ($item['sosyal_linkler'] as $link) {
    echo $link['icon'];
    echo $link['url'];
}
```

---

### `child` — Çoklu alt alan grubu (anahtar, tip değil)

`'child'` bir **field tipi değil**, bir alan tanımındaki **özel anahtardır**.
Birden fazla alt alanı (sub-field) tek başlık altında Bootstrap row/cols içinde gruplayarak gösterir.
Bu kullanımda üst alan tanımında `'type'` anahtarı **OLMAZ**.

```php
'alan_grubu' => [
    'name'  => 'Normal / Hover Renk',   // ✅ zorunlu — grup başlığı
    'desc'  => '',
    'child' => [
        'normal' => [                    // alt alan anahtarı
            'type'    => 'color',
            'default' => '#007a7a',
            'name'    => 'Normal',
            'desc'    => '',
        ],
        'hover' => [
            'type'    => 'color',
            'default' => '#ff9c01',
            'name'    => 'Hover',
            'desc'    => '',
        ],
    ],
    'depend' => [/*...*/],               // opsiyonel
],
```

**`child_col` — Alt alanları sekme olarak gösterme (opsiyonel):**
```php
'alan_grubu' => [
    'name'      => 'Gradient',
    'desc'      => '',
    'child_col' => 'tab',               // 'tab' → satırdaki sütunlar yerine sekme (tab) görünümü
    'child'     => [
        'normal' => ['type' => 'gradient', /* ... */],
        'hover'  => ['type' => 'gradient', /* ... */],
    ],
],
```

#### Veri Erişim Farkı — Kritik!

| Konum | Erişim biçimi | Örnek |
|-------|---------------|-------|
| **Üst seviye** (repeatable dışında) | Dizi anahtarı | `$widget['alan_grubu']['normal']` |
| **repeatable içinde** | Flat (alt-çizgiyle) | `$item['alan_grubu_normal']` |

```php
// Üst seviye (repeatable DIŞINDA) — getConfig() içinde:
'card_colors' => [
    'name'  => 'Kart Rengi',
    'child' => [
        'normal' => [
            'type'    => 'color',
            'default' => !empty($widget['card_colors']['normal'])  // ← dizi anahtarı
                ? $widget['card_colors']['normal'] : '#ffffff',
            'name' => 'Normal', 'desc' => '',
        ],
        'hover' => [
            'type'    => 'color',
            'default' => !empty($widget['card_colors']['hover'])   // ← dizi anahtarı
                ? $widget['card_colors']['hover'] : '#f0f0f0',
            'name' => 'Hover', 'desc' => '',
        ],
    ],
],

// repeatable İÇİNDE — getRepeatableItem() içinde:
'button_color' => [
    'name'  => 'Düğme Rengi',
    'child' => [
        'normal' => [
            'type'    => 'color',
            'default' => !empty($item['button_color_normal'])  // ← flat, alt-çizgi
                ? $item['button_color_normal'] : '#007a7a',
            'name' => 'Normal', 'desc' => '',
        },
        'hover' => [
            'type'    => 'color',
            'default' => !empty($item['button_color_hover'])   // ← flat, alt-çizgi
                ? $item['button_color_hover'] : '#ff9c01',
            'name' => 'Hover', 'desc' => '',
        ],
    ],
],
```

---

## 🔗 Opsiyonel Ortak Parametreler

### `inline` — Satır içi görünüm

```php
'inline' => true,   // Etiket ve alan yan yana
'inline' => false,  // Etiket üstte, alan altta (varsayılan)
```

---

### `responsive` — Ekrana duyarlı değer

`text`, `number`, `select` tiplerinde desteklenir.
`true` olunca her ekran boyutu (xl/lg/md/sm/xs) için ayrı değer girilebilir.

```php
'responsive' => true,
'default'    => ["xl" => "", "lg" => "", "md" => "", "sm" => "", "xs" => ""],
```

---

### `addon` — Ek metin (input-group)

`text` ve `number` tiplerinde giriş kutusunun sol/sağına metin ekler.

```php
'addon' => ['right' => 'px'],
'addon' => ['left'  => '$'],
'addon' => ['left'  => 'min:', 'right' => 's'],
```

---

### `depend` — Koşullu görünürlük

```php
'depend' => [
    ["alan_adi", "=",  "bagimli_alan_adi"],        // alan_adi === 'bagimli_alan_adi' ise göster
    ["alan_adi", "!=", "bagimli_alan_adi"],        // alan_adi !== 'bagimli_alan_adi' değil ise göster
    ["alan_adi", "=", ["bagimli_alan_adi","bagimli_alan_adi2],        // alan_adi belirtilen bagimli_alan_adi değerleri içerisindeyse göster
    ["alan_adi", "!=", ["bagimli_alan_adi","bagimli_alan_adi2],        // alan_adi belirtilen bagimli_alan_adi değerleri içerisinde değilse göster
    ["cok_dilli_alan",  "!=", "", "ml"],       // çok dilli alan doluysa göster
],
```

Birden fazla kural **VE** (AND) mantığıyla çalışır.

**Örnek:**
```php
'depend' => [
    ["heading_type",  "=",  "animation"],
    ["animated_text", "!=", "", "ml"],
],
```

---

## 🔒 Güvenlik Kuralları

```php
// ❌ YASAK - Kod yürütme
eval(...);  system(...);  exec(...);  shell_exec(...);  passthru(...);  assert(...);

// ❌ YASAK - Dosya yazma/silme
file_put_contents(...);  unlink(...);  rename(...);

// ❌ YASAK - Ağ erişimi
curl_exec(...);  fsockopen(...);

// ❌ YASAK - Süper global erişimi
$_POST[...];  $_GET[...];  $_SERVER[...];  $_COOKIE[...];
```

---

## ✅ İzin Verilen Kullanımlar

```php
// ✓ PageData yardımcı metodları
PageData::button_types($language);
PageData::aligns($language, 'horizontal');
PageData::defaultTypography();
PageData::borders_style($language);
PageData::cssAnimations($language);

// ✓ PHP standart dizgi/dizi fonksiyonları
implode(', ', $array);
array_map(fn($v) => trim($v), $array);
sprintf('%d öğe', count($items));
```

---

## 📝 Hazır Örnek Widgetlar

- **`example_card`** — `text`, `select`, `icon`, `bool`, `color`, `number`, `slider`, `responsive` ve **`child` (üst seviye, normal/hover renk grubu)** kullanımını gösteren kart bileşeni
- **`notice_box`** — `bool`, `select`, `textLanguage`, `textareaLanguage`, `depend`, **`repeatable`**, **`child` (repeatable içinde flat erişim)** ve **`child_repeatable`** kullanımını gösteren bildirim kutusu

Bu dosyaları kendi widget'larınız için şablon olarak kullanabilirsiniz.
Üretim ortamına geçmeden önce `example_card` ve `notice_box` dizinlerini silebilirsiniz.
