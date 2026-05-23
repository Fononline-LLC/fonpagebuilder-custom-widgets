<?php
/**
 * example_card widget — Türkçe dil dosyası
 *
 * KURAL: Bu dosya bir PHP dizi döndürmelidir.
 * Anahtarlar yalnızca şu karakterleri içerebilir: a-z, 0-9, _
 * Değerler düz metin olmalıdır (HTML içermemeli).
 */
return [
    // ---- Widget meta ----
    'widget_name'        => 'Örnek Kart',
    'widget_description' => 'Başlık, ikon ve metin içeren özelleştirilebilir kart bileşeni.',

    // ---- Genel ----
    'field_widget_name'  => 'Widget Adı',

    // ---- İçerik ----
    'field_title'        => 'Kart Başlığı',
    'field_content_text' => 'İçerik Metni',
    'field_content_desc' => 'Kart açıklama metnini giriniz.',
    'field_icon'         => 'İkon',
    'field_icon_desc'    => 'Kart için bir ikon seçiniz.',
    'field_card_color'   => 'Kart Rengi',
    'field_card_color_desc' => 'Kartın tema rengini belirler.',
    'field_align'        => 'Hizalama',

    'color_primary'      => 'Birincil',
    'color_secondary'    => 'İkincil',
    'color_success'      => 'Başarı',
    'color_warning'      => 'Uyarı',
    'color_danger'       => 'Tehlike',
    'color_info'         => 'Bilgi',
    'color_dark'         => 'Koyu',
    'color_light'        => 'Açık',

    'align_left'         => 'Sol',
    'align_center'       => 'Orta',
    'align_right'        => 'Sağ',

    // ---- Bağlantı ----
    'tab_link'           => 'Bağlantı',
    'field_button_text'  => 'Buton Metni',
    'field_button_text_desc' => 'Boş bırakılırsa buton gösterilmez.',
    'field_button_url'   => 'Bağlantı URL',
    'field_button_url_desc' => 'Örnek: /urunler veya https://example.com',
    'field_button_target' => 'Hedef',
    'target_self'        => 'Aynı Pencere',
    'target_blank'       => 'Yeni Pencere',

    // ---- Stil ----
    'field_border_radius' => 'Köşe Yuvarlaklığı (px)',
    'field_padding'       => 'İç Boşluk (padding)',
    'field_padding_desc'  => 'Örnek: 20px veya 10px 20px',
];

