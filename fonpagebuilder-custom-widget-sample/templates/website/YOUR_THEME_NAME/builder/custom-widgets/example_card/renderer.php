<?php
/**
 * Example Card custom widget renderer.
 */
final class FonCustomWidgetRenderer_example_card
{
    /**
     * Example Card widget'ini frontend'de render etmek için verileri hazırlar ve widget stilini/stillerini render eder.
     *
     * @param object $theme Aktif tema nesnesi.
     * @param object $content Widget içerik verisi.
     * @param string $moduleId Widget modül kimliği. Tema tarafından benzersiz ve güvenli bir şekilde oluşturulur. Örn: 'example_card_1', 'custom_abc123', vs.
     * @param array<string,mixed> $pageData Sayfa verileri.
     * @param object $row Satır verisi.
     * @param bool $isLCPCandidate LCP adayı işareti.
     * @param object|null $widget Ham widget nesnesi.
     * @return string Render edilmiş HTML.
     * @throws \Throwable Beklenmeyen kritik hatalarda üst katmana bırakılır.
     */
    public static function render(
        object $theme,
        object $content,
        string $moduleId,
        array $pageData
    ): string {

        // Geliştirme aşamasında içerik verisini incelemek için kullanabilirsiniz. Yayına almadan önce kaldırmayı unutmayın.
        /*echo "<pre>content<br>";
        print_r(json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        echo "</pre>";*/

        $lang = Language::selected(); //Global olarak temada o anda aktif olan dil kodunu elde eder. Örn: 'en', 'tr', vs.

        $widgetId = 'fon-widget-' . ($moduleId !== '' ? preg_replace('/[^a-zA-Z0-9_-]/', '', $moduleId) : uniqid('custom-', true)); // Güvenli ve benzersiz widget ID'si oluşturur. moduleId geçerli değilse uniqid kullanır.

        $widgetData = [];
        $widgetData['name_widget']= $theme::getFieldMultilingualText($content->name_widget ?? null, $lang, 'Example Card'); // Bu değer standarttır ve sadece yönetim panelinde gösterilir ve bileşeni tanımlamak için kullanılır. İsterseniz bileşenlerinizde de kullanabilirsiniz.
        $widgetData['title'] = $theme::getFieldMultilingualText($content->title ?? null, $lang, 'Card Title');
        $widgetData['content_text'] =$theme::getFieldMultilingualText($content->content_text ?? null, $lang, '');
        $widgetData['icon_class'] = (string) (isset($content->icon_class) && $content->icon_class != 'empty' ? $content->icon_class : 'fa-solid fa-bolt');
        $widgetData['card_color'] = (string) (isset($content->card_color) && $content->card_color ? $content->card_color : '#007a7a');
        $widgetData['text_align'] = (string) (isset($content->text_align) && $content->text_align ? $content->text_align : 'left');
        $widgetData['button_text'] = (string) (isset($content->button_text) && $content->button_text ? $content->button_text : 'Learn More');
        $widgetData['button_url'] = (string) (isset($content->button_url) && $content->button_url ? $content->button_url : '#');
        $widgetData['button_target'] = (string) (isset($content->button_target) && $content->button_target ? $content->button_target : '_self');
        $widgetdata['widget_style'] =  (string)(isset($content->widget_style) && $content->widget_style ? $content->widget_style : 'default');
        $widgetData['card_text_color_normal'] = (string)(isset($content->card_text_color->normal) && $content->card_text_color->normal ? $content->card_text_color->normal : '#333333');
        $widgetData['card_text_color_hover'] = (string)(isset($content->card_text_color->hover) && $content->card_text_color->hover ? $content->card_text_color->hover : '#007a7a');
        $widgetData['card_bg_color_normal'] = (string)(isset($content->card_bg_color->normal) && $content->card_bg_color->normal ? $content->card_bg_color->normal : '#ffffff');
        $widgetData['card_bg_color_hover'] = (string)(isset($content->card_bg_color->hover) && $content->card_bg_color->hover ? $content->card_bg_color->hover : '#f0f9f9');
        $widgetData['border_radius'] = (string) (isset($content->border_radius) && $content->border_radius ? $content->border_radius : '10');
        $widgetData['opacity'] = (string) (isset($content->opacity) && $content->opacity ? $content->opacity : '100');
        $widgetData['bg_color'] = (string) (isset($content->bg_color) && $content->bg_color ? $content->bg_color : '#fff');
        $widgetData['padding'] = (object) (isset($content->padding) && $content->padding ? $content->padding : (object)['xl'=> '20px', 'lg' => '20px', 'md' => '20px', 'sm' => '20px', 'xs' => '20px']);

        //Widget Özel CSS Biçimlendirme:
        self::renderWidgetCss(
            $widgetId,
            $widgetData,
            $theme,
            $pageData
        );

        //Widget Özel JS Biçimlendirme:
        self::renderWidgetJs(
            $widgetId,
            $widgetData,
            $theme,
            $pageData
        );

        // Widget Özel Asset'leri (CSS/JS dosyaları, fontlar, vs.) ekleme:
        self::addWidgetAssets(
            $theme
        );

		// Widget HTML İçeriğini Render Etme:
        // 1. Stil adını belirle (Yoksa veya geçersizse direkt 'default' döner).
        // Eğer birden fazla stil sunuyorsanız her stil için bir render fonksiyonu oluşturmalısınız. Örneğin: renderWidgetStyleDefault, renderVidgetStyleModern vb.
        $funcWidget  = 'renderWidgetStyle' . ucfirst($widgetdata['widget_style'] ?? 'default');

        // 2. Metod var mı kontrol et, yoksa 'default' metoduna geri dön (fallback)
        if (!method_exists(self::class, $funcWidget)) {
            $funcWidget = 'renderWidgetStyleDefault';
        }

        // 3. Widget stilinize uygun şekilde tek bir yerden return et
        return self::$funcWidget(
            $widgetId,
            $widgetData,
            $theme,
            $pageData
        );
    }

    /**
     * Default style widget HTML'ini üretir.
     *
     * @param string $widgetId
     * @param array $widgetData
     * @param object $theme
     * @param array<string,mixed> $pageData
     * @return string
     */
    private static function renderWidgetStyleDefault(
        string $widgetId,
        array $widgetData,
        object $theme,
        array $pageData
    ): string {
        // Verilerin uygun şekilde işlenmesi ve güvenli hale getirilmesi. Kendiniz için gerekli gördüğünüz ek kontrolleri ve işlemleri yapabilirsiniz.
        $textAlignClass = 'text-center';
        if ($widgetData['text_align'] === 'left') {
            $textAlignClass = 'text-start';
        } elseif ($widgetData['text_align'] === 'right') {
            $textAlignClass = 'text-end';
        }
        $output = '';
        $output .= '<div id="' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . '" class="fon-widget-container fon-example-card-widget">';
        $output .= '    <div class="card border-0 shadow-sm fon-example-card border-top border-4 border-' . htmlspecialchars($widgetData['card_color'], ENT_QUOTES, 'UTF-8') . ' ' . $textAlignClass . '">';
        $output .= '        <div class="card-body">';
        $output .= '            <div class="mb-3"><i class="' . htmlspecialchars($widgetData['icon_class'], ENT_QUOTES, 'UTF-8') . ' fs-2"></i></div>';

        $output .= '            <h4 class="card-title mb-2">' . htmlspecialchars($widgetData['title']) . '</h4>';

        // Açıklama kontrolü
        if ($widgetData['content_text'] !== '') {
            $output .= '            <div class="card-text mb-3">' . htmlspecialchars($widgetData['content_text']) . '</div>';
        }

        // Buton kontrolü
        if ($widgetData['button_text'] !== '' && $widgetData['button_url'] !== '') {
            $output .= '            <a href="' . htmlspecialchars($widgetData['button_url'], ENT_QUOTES, 'UTF-8') . '" class="btn btn-' . htmlspecialchars($widgetData['card_color'], ENT_QUOTES, 'UTF-8') . '" target="' . htmlspecialchars($widgetData['button_target'], ENT_QUOTES, 'UTF-8') . '">';
            $output .= '                ' . htmlspecialchars($widgetData['button_text'], ENT_QUOTES, 'UTF-8');
            $output .= '            </a>';
        }

        $output .= '        </div>';
        $output .= '    </div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Modern style widget HTML'ini üretir.
     *
     * @param string $widgetId
     * @param array $widgetData
     * @param object $theme
     * @param array<string,mixed> $pageData
     * @return string
     */
    private static function renderWidgetStyleModern(
        string $widgetId,
        array $widgetData,
        object $theme,
        array $pageData
    ): string {
		// Verilerin uygun şekilde işlenmesi ve güvenli hale getirilmesi. Kendiniz için gerekli gördüğünüz ek kontrolleri ve işlemleri yapabilirsiniz.
        $textAlignClass = 'text-center';
        if ($widgetData['text_align'] === 'left') {
            $textAlignClass = 'text-start';
        } elseif ($widgetData['text_align'] === 'right') {
            $textAlignClass = 'text-end';
        }

		// Widget HTML Output (Çıktısı)
        $output = '';
        $output .= '<div id="' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . '" class="fon-widget-container fon-example-card-widget fon-example-card-modern">';
        $output .= '    <div class="card border-0 fon-example-card ' . $textAlignClass . '">';
        $output .= '        <div class="card-body position-relative">';
        $output .= '            <div class="mb-3">';
        $output .= '                <span class="fon-modern-icon-wrap border border-' . htmlspecialchars($widgetData['card_color'], ENT_QUOTES, 'UTF-8') . '">';
        $output .= '                    <i class="' . htmlspecialchars($widgetData['icon_class'], ENT_QUOTES, 'UTF-8') . ' fs-4"></i>';
        $output .= '                </span>';
        $output .= '            </div>';
        $output .= '            <h4 class="card-title mb-2 fw-semibold">' . htmlspecialchars($widgetData['title']) . '</h4>';

        // Açıklama alanı kontrolü
        if ($widgetData['content_text'] !== '') {
            $output .= '            <div class="card-text mb-3 opacity-90">' . htmlspecialchars($widgetData['content_text']) . '</div>';
        }

        // Buton alanı kontrolü
        if ($widgetData['button_text'] !== '' && $widgetData['button_url'] !== '') {

            $output .= '            <a href="' . htmlspecialchars($widgetData['button_url'], ENT_QUOTES, 'UTF-8') . '" class="btn btn-' . htmlspecialchars($widgetData['card_color'], ENT_QUOTES, 'UTF-8') . ' rounded-pill px-4" target="' . htmlspecialchars($widgetData['button_target'], ENT_QUOTES, 'UTF-8') . '">';
            $output .= '                ' . htmlspecialchars($widgetData['button_text'], ENT_QUOTES, 'UTF-8') ;
            $output .= '            </a>';
        }

        $output .= '        </div>';
        $output .= '    </div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Widget'a özel CSS kodlarını eklemek için kullanılır.
     *
     * Stil kodu içerisinde <style></style> etiketlerini kullanmayınız. Sadece saf CSS kodu ekleyiniz. Tema, bu kodu uygun şekilde sayfaya ekleyecektir.
     *
     * @param string $widgetId
     * @param array $widgetData
     * @param object $theme
     * @param array<string,mixed> $pageData
     * @return void
     */
    private static function renderWidgetCss(
        string $widgetId,
        array $widgetData,
        object $theme,
        array $pageData
    ): void {
        $page = isset($pageData['page']) && is_string($pageData['page']) && trim($pageData['page']) !== ''
            ? trim($pageData['page'])
            : 'index';
        $css = ''; // Widget ayarlarınızdan gelen verilerle CSS kodunu bu değişkene atayabilir veya widget stili (Modern, Default vb.) bazlı özel cssler ekleyebilir ya da doğrudan css kodunuzu bu değişkene atayabilirsiniz.

        if ($widgetData['widget_style'] === 'modern') {
            $css .= '#' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . ' .fon-example-card {' . "\n";
            $css .= '    background: linear-gradient(135deg, ' . htmlspecialchars($widgetData['card_bg_color_normal'], ENT_QUOTES, 'UTF-8') . ' 0%, rgba(0, 0, 0, 0.06) 100%);' . "\n";
            $css .= '    color: ' . htmlspecialchars($widgetData['card_text_color_normal'], ENT_QUOTES, 'UTF-8') . ';' . "\n";
            $css .= '    border-radius: ' . (int) $widgetData['border_radius'] . 'px;' . "\n";
            $css .= '    opacity: ' . (((int) $widgetData['opacity']) / 100) . ';' . "\n";
            $css .= '    border: 1px solid rgba(0, 0, 0, 0.08);' . "\n";
            $css .= '    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);' . "\n";
            $css .= '    transition: all 0.25s ease-in-out;' . "\n";
            $css .= '    overflow: hidden;' . "\n";
            $css .= '    position: relative;' . "\n";
            $css .= '}' . "\n";
            $css .= '#' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . ' .fon-example-card::before {' . "\n";
            $css .= '    content: \'\';' . "\n";
            $css .= '    position: absolute;' . "\n";
            $css .= '    inset: 0;' . "\n";
            $css .= '    background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.28), transparent 45%);' . "\n";
            $css .= '    pointer-events: none;' . "\n";
            $css .= '}' . "\n";
            $css .= '#' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . ' .fon-example-card:hover {' . "\n";
            $css .= '    background: linear-gradient(135deg, ' . htmlspecialchars($widgetData['card_bg_color_hover'], ENT_QUOTES, 'UTF-8') . ' 0%, rgba(0, 0, 0, 0.08) 100%);' . "\n";
            $css .= '    color: ' . htmlspecialchars($widgetData['card_text_color_hover'], ENT_QUOTES, 'UTF-8') . ';' . "\n";
            $css .= '    transform: translateY(-4px);' . "\n";
            $css .= '    box-shadow: 0 18px 34px rgba(0, 0, 0, 0.16);' . "\n";
            $css .= '}' . "\n";
            $css .= '#' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . ' .fon-modern-icon-wrap {' . "\n";
            $css .= '    width: 56px;' . "\n";
            $css .= '    height: 56px;' . "\n";
            $css .= '    border-radius: 50%;' . "\n";
            $css .= '    display: inline-flex;' . "\n";
            $css .= '    align-items: center;' . "\n";
            $css .= '    justify-content: center;' . "\n";
            $css .= '    background: rgba(255, 255, 255, 0.22);' . "\n";
            $css .= '    backdrop-filter: blur(2px);' . "\n";
            $css .= '}' . "\n";
        }
        else {
            // Default style
            $css .= '#' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . ' .fon-example-card {' . "\n";
            $css .= '    background: ' . htmlspecialchars($widgetData['card_bg_color_normal'], ENT_QUOTES, 'UTF-8') . ';' . "\n";
            $css .= '    color: ' . htmlspecialchars($widgetData['card_text_color_normal'], ENT_QUOTES, 'UTF-8') . ';' . "\n";
            $css .= '    border-radius: ' . (int) $widgetData['border_radius'] . 'px;' . "\n";
            $css .= '    opacity: ' . (((int) $widgetData['opacity']) / 100) . ';' . "\n";
            $css .= '    transition: all 0.25s ease-in-out;' . "\n";
            $css .= '}' . "\n";
            $css .= '#' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . ' .fon-example-card:hover {' . "\n";
            $css .= '    background: ' . htmlspecialchars($widgetData['card_bg_color_hover'], ENT_QUOTES, 'UTF-8') . ';' . "\n";
            $css .= '    color: ' . htmlspecialchars($widgetData['card_text_color_hover'], ENT_QUOTES, 'UTF-8') . ';' . "\n";
            $css .= '    transform: translateY(-2px);' . "\n";
            $css .= '}' . "\n";
        }

        // Responsive padding örneği (widget ayarlarından gelen değerlere göre):

        if($widgetData['padding'] && is_object($widgetData['padding'])) {
            // Responsive padding değerlerini temanın parseResponsiveItems metoduna uygun şekilde hazırlıyoruz. Bu örnekte tüm ekran boyutları için aynı padding değerini kullanıyoruz, ancak isterseniz farklı boyutlar için farklı değerler de atayabilirsiniz. Birden fazla responsive özellik eklemek isterseniz, responsiveItems dizisine ekleyerek devam edebilirsiniz.
            $responsiveItems = [
                '.fon-example-card' => [
                    [
                        'type'  => 'padding',
                        'unit'  => 'px',
                        'value' => $widgetData['padding'],
                    ]
                ],
            ];

            $css .= $theme::parseResponsiveItems($responsiveItems, $widgetId);

        }


        // Dinamik olarak oluşturulan CSS'yi kaydet
        if ($css !== '' && is_callable([$theme, 'registerWidgetStyle'])) {
            $theme->registerWidgetStyle($page, $css);
        }

    }
    /**
     * Widget'a özel JavaScript kodlarını eklemek için kullanılır.
     *
     * Script kodu içerisinde <script></script> etiketleri içerisinde kullanınız. Okunurluğu iyileştirmek için HEREDOC kullanabilirsiniz..
     *
     * @param string $widgetId
     * @param array $widgetData
     * @param object $theme
     * @param array<string,mixed> $pageData
     * @return void
     */

    private static function renderWidgetJs(
        string $widgetId,
        array $widgetData,
        object $theme,
        array $pageData
    ): void {
        // Widget ayarlarınızdaki verilere {$widgetData[]} değişkeninden erişebilir ve js kodunuzda kullanabilirsiniz.
        $page = isset($pageData['page']) && is_string($pageData['page']) && trim($pageData['page']) !== ''
            ? trim($pageData['page'])
            : 'index';

        //HEREDOC için değişkenleri güvenli hale getirme
        $widgetIdJs = htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8');
        $pageJs = htmlspecialchars($page, ENT_QUOTES, 'UTF-8');
        $js = <<<JS
<script>
    // Örnek olarak widget yüklendiğinde konsola bir mesaj yazdıran basit bir JavaScript kodu ekleyelim. Gerçek kullanımda bu alana widget'inizle ilgili gerekli gördüğünüz herhangi bir JavaScript kodunu ekleyebilirsiniz.
    console.log("{$widgetData['name_widget']} - {$widgetData['title']} JS Loaded for widget ID: {$widgetIdJs} on page: {$pageJs}");
</script>
JS;

        // Dinamik olarak oluşturulan JavaScript'i kaydet
        if ($js !== '' && is_callable([$theme, 'registerWidgetScript'])) {
            // Eğer header'a eklemek isterseniz 'footer' yerine 'header' girmelisiniz.
            $theme->registerWidgetScript('footer', $page, $js);
        }

    }

    /**
     * Widget'a özel CSS/JS dosyaları, fontlar veya diğer asset'leri eklemek için kullanılır.
     *
     * Eğer widget'iniz özel CSS veya JS dosyalarına ihtiyaç duyuyorsa, bu metod içinde tema nesnesinin uygun metodlarını kullanarak bu dosyaları ekleyebilirsiniz. Tema tarafından desteklenen ve bileşenlerinizde kullanabileceğiniz öntanımlı assetlere templates/website/TemaAdi/inc/main-head.php ve templates/website/TemaAdi/inc/constant-footer-js.php dosyasındaki $hoptions dizisi içerisindeki kontrollerden ulaşabilirsiniz. Örneğin: $hoptions['magnific'], $hoptions['jsvectormap'], $hoptions['intlTelInput'], $hoptions['select2'], vs. Kendi özel css ve js kodlarınızı her zaman templates/website/TemaAdi/css/custom.css ve templates/website/TemaAdi/js/custom.js dosyalarına ekleyebileeğinizi unutmayınız. Ancak bu metod içinde de dinamik olarak temanın desteklediği öntanımlı assetleri ekleyebilirsiniz.
     *
     * @param object $theme
     * @param array<string,mixed> $pageData
     * @return void
     */

    private static function addWidgetAssets(
        object $theme
    ): void {
        $requiredAssets = []; // Eklemek istediğiniz asset'leri bu değişkene atayabilirsiniz. Örneğin: ['magnific', 'select2'] vs.

        if (!empty($requiredAssets) && is_callable([$theme, 'registerWidgetAsset'])) {
            $theme->registerWidgetAsset($requiredAssets);
        }

    }
}

