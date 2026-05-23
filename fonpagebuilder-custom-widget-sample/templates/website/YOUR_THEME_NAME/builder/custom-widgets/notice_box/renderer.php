<?php
/**
 * Notice Box custom widget renderer.
 */
final class FonCustomWidgetRenderer_notice_box
{
    /**
     * Notice Box widget'ini frontend'de render eder.
     *
     * @param object $theme Aktif tema nesnesi.
     * @param object $content Widget içerik verisi.
     * @param string $moduleId Widget modül kimliği.
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

        $widgetId = 'fon-widget-' . ($moduleId !== '' ? preg_replace('/[^a-zA-Z0-9_-]/', '', $moduleId) : uniqid('custom-', true));

        $widgetData = [];
        $widgetData['name_widget']= $theme::getFieldMultilingualText($content->name_widget ?? null, $lang, 'Example Card'); // Bu değer standarttır ve sadece yönetim panelinde gösterilir ve bileşeni tanımlamak için kullanılır. İsterseniz bileşenlerinizde de kullanabilirsiniz.
        $widgetData['notice_type'] = (string) (isset($content->notice_type) && $content->notice_type ? $content->notice_type : 'info');
        $widgetData['show_icon'] = (bool)(isset($content->show_icon) && $content->show_icon == 'yes' ? true : false);
        $widgetData['title'] = $theme::getFieldMultilingualText($content->title ?? null, $lang, 'Notice Title');
        $widgetData['title_font'] = (object)(isset($content->title_font) && $content->title_font ? $content->title_font : (object) []);
        $widgetData['message'] =$theme::getFieldMultilingualText($content->message ?? null, $lang, 'Notice message goes here. You can customize the content and style of this notice box widget to fit your website\'s design and needs.');
        $widgetData['dismissible'] = (string) (isset($content->dismissible) && $content->dismissible == 'yes' ? true : false);
        $widgetdata['widget_style'] =  (string)(isset($content->widget_style) && $content->widget_style ? $content->widget_style : 'default');
        $widgetData['border_left'] = (bool) (isset($content->border_left) && $content->border_left == 'yes' ? true : false);
        $widgetData['font_size'] = (string) (isset($content->font_size) && $content->font_size ? $content->font_size : '14px');
        $widgetData['button_url'] = (string) (isset($content->button_url) && $content->button_url ? $content->button_url : '#');
        $widgetData['button_target'] = (string) (isset($content->button_target) && $content->button_target ? $content->button_target : '_self');
        $widgetData['accent_color_normal'] = (string)(isset($content->accent_color->normal) && $content->accent_color->normal ? $content->accent_color->normal : '#007a7a');
        $widgetData['accent_color_hover'] = (string)(isset($content->accent_color->hover) && $content->accent_color->hover ? $content->accent_color->hover : '#ff9c01');

        $iconMap = [
            'info' => 'fa-solid fa-circle-info',
            'success' => 'fa-solid fa-circle-check',
            'warning' => 'fa-solid fa-triangle-exclamation',
            'danger' => 'fa-solid fa-circle-xmark',
            'primary' => 'fa-solid fa-bullhorn',
            'dark' => 'fa-solid fa-bell',
        ];
        $widgetData['icon_class'] = $iconMap[$widgetData['notice_type']] ?? $iconMap['info'];

		//Repeatable Items (Yinelenen Ögeler) için örnek:
		$widgetData['actions'] = [];
		if (isset($content->actions) && is_array($content->actions)) {
			foreach ($content->actions as $key => $action) {
				$widgetData['actions'][$key] = [
					'button_label' => $theme::getFieldMultilingualText($action->button_label ?? null, $lang, 'Düğme'),
					'button_url' => trim((string) ($action->button_url ?? '#')),
					'button_style' => trim((string) ($action->button_style ?? 'primary')),
					'button_color_normal' => trim((string) ($action->button_color_normal ?? '#007a7a')),
					'button_color_hover' => trim((string) ($action->button_color_hover ?? '#ff9c01')),
				];
				//Child repeatable items (Alt yinelenen ögeler) için örnek:
				if (isset($action->button_badges) && is_array($action->button_badges)) {
                    foreach ($action->button_badges as $button_badge) {
                        $widgetData['actions'][$key]['button_badges'][] = [
                            'text' => trim((string) ($button_badge->text ?? 'New')),
                            'color' => trim((string)($button_badge->color ?? 'primary')),
                        ];
                    }
                }
			}
		}

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

		$output = '';

        $output .= '<div id="' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . '" class="fon-widget-container fon-notice-box-widget">';

		// Alert kapsayıcı ve dismissible kontrolü
        $dismissClass = $widgetData['dismissible'] ? 'alert-dismissible fade show' : '';
        $output .= '    <div class="alert alert-' . htmlspecialchars($widgetData['notice_type'], ENT_QUOTES, 'UTF-8') . ' ' . $dismissClass . ' fon-notice-box" role="alert">';
        $output .= '        <div class="d-flex align-items-start gap-2">';

		// İkon kontrolü
        if ($widgetData['show_icon']) {
            $output .= '            <i class="' . htmlspecialchars($widgetData['icon_class'], ENT_QUOTES, 'UTF-8') . ' mt-1"></i>';
        }

        $output .= '            <div class="flex-grow-1">';
		// Başlık kontrolü
        if ($widgetData['title'] !== '') {
            $output .= '                <h5 class="mb-2 notice-title">' . htmlspecialchars($widgetData['title'], ENT_QUOTES, 'UTF-8') . '</h5>';
        }

		// Mesaj kontrolü
        if ($widgetData['message'] !== '') {
            $output .= '                <div class="mb-2 notice-desc">' . htmlspecialchars($widgetData['message'], ENT_QUOTES, 'UTF-8') . '</div>';
        }

		// Aksiyon butonları (Döngü kısmı)
        if (!empty($widgetData['actions'])) {
            $output .= '                <div class="d-flex flex-wrap gap-2 mt-2">';
            foreach ($widgetData['actions'] as $action_key => $action) {
                $output .= '                    <a href="' . htmlspecialchars($action['button_url'], ENT_QUOTES, 'UTF-8') . '" class="btn btn-sm btn-' . htmlspecialchars($action['button_style'], ENT_QUOTES, 'UTF-8') . '" id="#'.$widgetId.'-btn-'.$action_key.' target="_self" style="--bs-btn-color: ' . htmlspecialchars($action['button_color_normal'], ENT_QUOTES, 'UTF-8') . '; --bs-btn-hover-color: ' . htmlspecialchars($action['button_color_hover'], ENT_QUOTES, 'UTF-8') . ';">';
                $output .= '                        ' . htmlspecialchars($action['button_label'], ENT_QUOTES, 'UTF-8');
				if(!empty($action['button_badges'])) {
					foreach ($action['button_badges'] as $badge_key => $badge) {
						$output .= ' <span class="badge bg-' . htmlspecialchars($badge['color'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($badge['text'], ENT_QUOTES, 'UTF-8') . '</span>';
					}
				}
                $output .= '                    </a>';
            }
            $output .= '                </div>';
        }

        $output .= '            </div>';
        $output .= '        </div>';

		// Kapatma butonu kontrolü
        if ($widgetData['dismissible']) {
            $output .= '        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        }

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
        $output = '';

        $output .= '<div id="' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . '" class="fon-widget-container fon-notice-box-widget fon-notice-box-modern">';

		// Alert kapsayıcı ve dismissible kontrolü
        $dismissClass = $widgetData['dismissible'] ? 'alert-dismissible fade show' : '';
        $output .= '    <div class="alert ' . $dismissClass . ' fon-notice-box" role="alert">';
        $output .= '        <div class="d-flex align-items-start gap-2">';

		// İkon kontrolü
        if ($widgetData['show_icon']) {
            $output .= '            <i class="' . htmlspecialchars($widgetData['icon_class'], ENT_QUOTES, 'UTF-8') . ' mt-1"></i>';
        }

        $output .= '            <div class="flex-grow-1">';

		// Başlık kontrolü
        if ($widgetData['title'] !== '') {
            $output .= '                <h5 class="mb-2 text-light notice-title">' . htmlspecialchars($widgetData['title'], ENT_QUOTES, 'UTF-8') . '</h5>';
        }

		// Mesaj kontrolü
        if ($widgetData['message'] !== '') {
            $output .= '                <div class="mb-2 text-light opacity-75 notice-text">' . htmlspecialchars($widgetData['message'], ENT_QUOTES, 'UTF-8') . '</div>';
        }

		// Aksiyon butonları (Döngü)
        if (!empty($actions)) {
            $output .= '                <div class="d-flex flex-wrap gap-2 mt-2">';
            foreach ($widgetData['actions'] as $action_key => $action) {
                $output .= '                    <a href="' . htmlspecialchars($action['button_url'], ENT_QUOTES, 'UTF-8') . '" class="btn btn-sm btn-' . htmlspecialchars($action['button_style'], ENT_QUOTES, 'UTF-8') . '" id="#'.$widgetId.'-btn-'.$action_key.' target="_self" style="--bs-btn-color: ' . htmlspecialchars($action['button_color_normal'], ENT_QUOTES, 'UTF-8') . '; --bs-btn-hover-color: ' . htmlspecialchars($action['button_color_hover'], ENT_QUOTES, 'UTF-8') . ';">';
                $output .= '                        ' . htmlspecialchars($action['button_label'], ENT_QUOTES, 'UTF-8');
                if(!empty($action['button_badges'])) {
                    foreach ($action['button_badges'] as $badge_key => $badge) {
                        $output .= ' <span class="badge bg-' . htmlspecialchars($badge['color'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($badge['text'], ENT_QUOTES, 'UTF-8') . '</span>';
                    }
                }
                $output .= '                    </a>';
            }
            $output .= '                </div>';
        }

        $output .= '            </div>';
        $output .= '        </div>';

		// Kapatma butonu kontrolü (Modern görünüm için beyaz versiyon)
        if ($widgetData['dismissible']) {
            $output .= '        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>';
        }

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
            $css .= '#'. htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8').' .fon-notice-box {';
            $css .= '	border-left: '.($widgetData['border_left'] ? '4px' : '0').' solid '. htmlspecialchars($widgetData['accent_color_normal'], ENT_QUOTES, 'UTF-8').';';
            $css .= '	transition: all 0.25s ease-in-out;';
            $css .= '	background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.96));';
            $css .= '	color: #e2e8f0;';
            $css .= '	border: 1px solid rgba(148, 163, 184, 0.24);';
            $css .= '	box-shadow: 0 10px 28px rgba(2, 6, 23, 0.28);';
			if($widgetData['font_size'] !== '') {
				$css .= '	font-size: '.htmlspecialchars($widgetData['font_size'], ENT_QUOTES, 'UTF-8').';';
			}
			$css .= '}';
            $css .= '#'.htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8').' .fon-notice-box:hover {';
            $css .= '	border-left-color: '.htmlspecialchars($widgetData['accent_color_hover'], ENT_QUOTES, 'UTF-8').';';
            $css .= '	transform: translateY(-2px);';
            $css .= '	box-shadow: 0 14px 34px rgba(2, 6, 23, 0.34);';
            $css .= '}';

            $css .= '#'.htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8').' .fon-notice-box a.btn {';
			$css .= '	border-radius: 999px;';
			$css .= '	padding-left: 0.85rem;';
			$css .= '	padding-right: 0.85rem;';
			$css .= '}';
            if($widgetData['title_font'] && is_object($widgetData['title_font'])) {
                $css .= '#' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . ' .fon-notice-box h5.notice-title {';
                $css .= $theme::parseFontProperties($widgetData['title_font']);
                $css .= '}';
            }

        }
        else {
            // Default style
            $css .= '#' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . ' .fon-notice-box {';
            $css .= '	border-left: ' . ($widgetData['border_left'] ? '4px' : '0') . ' solid ' . htmlspecialchars($widgetData['accent_color_normal'], ENT_QUOTES, 'UTF-8') . ';';
            $css .= '	transition: all 0.25s ease-in-out;';
            if ($widgetData['font_size'] !== '') {
                $css .= '	font-size: ' . htmlspecialchars($widgetData['font_size'], ENT_QUOTES, 'UTF-8') . ';';
            }
            $css .= '}';

            $css .= '#' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . ' .fon-notice-box:hover {';
            $css .= '	border-left-color: ' . htmlspecialchars($widgetData['accent_color_hover'], ENT_QUOTES, 'UTF-8') . ';';
            $css .= '}';

            if($widgetData['title_font'] && is_object($widgetData['title_font'])) {
                $css .= '#' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . ' .fon-notice-box h5.notice-title {';
                $css .= $theme::parseFontProperties($widgetData['title_font']);
                $css .= '}';
            }
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

