<?php
/**
 * Notice Box custom widget renderer.
 */
final class FonCustomWidgetRenderer_notice_box
{
    /**
     * Renders the Notice Box widget on the frontend.
     *
     * @param object $theme Active theme object.
     * @param object $content Widget content data.
     * @param string $moduleId Widget module ID.
     * @param array<string,mixed> $pageData Page data.
     * @return string Rendered HTML output.
     * @throws \Throwable Passed to the upper layer in case of unexpected critical errors.
     */
    public static function render(
        object $theme,
        object $content,
        string $moduleId,
        array $pageData
    ): string {
        // You can use this to inspect the content data during development. Remember to remove it before going live.
        /*echo "<pre>content<br>";
        print_r(json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        echo "</pre>";*/


        $lang = Language::selected(); //Retrieves the currently active language code globally in the theme. For example: 'en', 'tr', etc.

        $widgetId = 'fon-widget-' . ($moduleId !== '' ? preg_replace('/[^a-zA-Z0-9_-]/', '', $moduleId) : uniqid('custom-', true));

        $widgetData = [];
        $widgetData['name_widget']= $theme::getFieldMultilingualText($content->name_widget ?? null, $lang, 'Example Card'); // This value is standard and is shown only in the admin panel and used to identify the component. You can also use it in your own widgets.
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

		//Example for Repeatable Items (Repeated Entries):
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
				//Example for child repeatable items (nested repeated items):
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

        //Widget-specific CSS formatting:
        self::renderWidgetCss(
            $widgetId,
            $widgetData,
            $theme,
            $pageData
        );

        //Widget-specific JS formatting:
        self::renderWidgetJs(
            $widgetId,
            $widgetData,
            $theme,
            $pageData
        );

        // Enqueue / Inject widget-specific assets (CSS/JS files, fonts, etc.):
        self::addWidgetAssets(
            $theme
        );

        // Rendering the widget HTML content:
        // 1. Determine the style name (if missing or invalid, it falls back directly to 'default').
        // If you offer multiple styles, you should create a render function for each style. For example: renderWidgetStyleDefault, renderVidgetStyleModern, etc.
        $funcWidget  = 'renderWidgetStyle' . ucfirst($widgetdata['widget_style'] ?? 'default');

        // 2. Check whether the method exists; otherwise fall back to the 'default' method.
        if (!method_exists(self::class, $funcWidget)) {
            $funcWidget = 'renderWidgetStyleDefault';
        }

        // 3. Return from a single place according to the selected widget style.
        return self::$funcWidget(
            $widgetId,
            $widgetData,
            $theme,
            $pageData
        );
    }

    /**
     * Generates the widget HTML for the default style.
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

		// Alert container and dismissible check
        $dismissClass = $widgetData['dismissible'] ? 'alert-dismissible fade show' : '';
        $output .= '    <div class="alert alert-' . htmlspecialchars($widgetData['notice_type'], ENT_QUOTES, 'UTF-8') . ' ' . $dismissClass . ' fon-notice-box" role="alert">';
        $output .= '        <div class="d-flex align-items-start gap-2">';

		// Icon check
        if ($widgetData['show_icon']) {
            $output .= '            <i class="' . htmlspecialchars($widgetData['icon_class'], ENT_QUOTES, 'UTF-8') . ' mt-1"></i>';
        }

        $output .= '            <div class="flex-grow-1">';
		// Title check
        if ($widgetData['title'] !== '') {
            $output .= '                <h5 class="mb-2 notice-title">' . htmlspecialchars($widgetData['title'], ENT_QUOTES, 'UTF-8') . '</h5>';
        }

		// Message check
        if ($widgetData['message'] !== '') {
            $output .= '                <div class="mb-2 notice-desc">' . htmlspecialchars($widgetData['message'], ENT_QUOTES, 'UTF-8') . '</div>';
        }

		// Action buttons (loop section)
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

		// Dismiss button check
        if ($widgetData['dismissible']) {
            $output .= '        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        }

        $output .= '    </div>';
        $output .= '</div>';
        return $output;
    }

    /**
     * Generates the widget HTML for the modern style.
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

		// Alert container and dismissible check
        $dismissClass = $widgetData['dismissible'] ? 'alert-dismissible fade show' : '';
        $output .= '    <div class="alert ' . $dismissClass . ' fon-notice-box" role="alert">';
        $output .= '        <div class="d-flex align-items-start gap-2">';

		// Icon check
        if ($widgetData['show_icon']) {
            $output .= '            <i class="' . htmlspecialchars($widgetData['icon_class'], ENT_QUOTES, 'UTF-8') . ' mt-1"></i>';
        }

        $output .= '            <div class="flex-grow-1">';

		// Title check
        if ($widgetData['title'] !== '') {
            $output .= '                <h5 class="mb-2 text-light notice-title">' . htmlspecialchars($widgetData['title'], ENT_QUOTES, 'UTF-8') . '</h5>';
        }

		// Message check
        if ($widgetData['message'] !== '') {
            $output .= '                <div class="mb-2 text-light opacity-75 notice-text">' . htmlspecialchars($widgetData['message'], ENT_QUOTES, 'UTF-8') . '</div>';
        }

		// Action buttons (loop section)
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

		// Dismiss button check (White version for the modern appearance)
        if ($widgetData['dismissible']) {
            $output .= '        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>';
        }

        $output .= '    </div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Used to add widget-specific CSS code.
     *
     * Do not use <style></style> tags within the style code. Add raw CSS code only. The theme will properly inject this code into the page.
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
        $css = ''; // You can assign CSS code to this variable using data from widget settings, add custom CSS based on the widget style (Modern, Default, etc.), or directly assign your raw CSS code.

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


        // Register the dynamically generated CSS
        if ($css !== '' && is_callable([$theme, 'registerWidgetStyle'])) {
            $theme->registerWidgetStyle($page, $css);
        }

    }

    /**
     * Used to add widget-specific JavaScript code.
     *
     * Wrap your script code within <script></script> tags. You can use HEREDOC to improve code readability.
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
        // You can access data from your widget settings via the {$widgetData[]} variable and use it in your JS code.
        $page = isset($pageData['page']) && is_string($pageData['page']) && trim($pageData['page']) !== ''
            ? trim($pageData['page'])
            : 'index';
        //Sanitize variables for HEREDOC usage
		$widgetIdJs = htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8');
		$pageJs = htmlspecialchars($page, ENT_QUOTES, 'UTF-8');
        $js = <<<JS
<script>
    // As an example, add a simple JavaScript snippet that logs a message to the console when the widget loads. In real-world usage, you can place any widget-specific JavaScript code here.
    console.log("{$widgetData['name_widget']} - {$widgetData['title']} JS Loaded for widget ID: {$widgetIdJs} on page: {$pageJs}");
</script>
JS;

        // Save the dynamically generated JavaScript
        if ($js !== '' && is_callable([$theme, 'registerWidgetScript'])) {
            // If you want to add it to the header, use 'header' instead of 'footer'.
            $theme->registerWidgetScript('footer', $page, $js);
        }

    }

    /**
     * Used to add widget-specific CSS/JS files, fonts, or other assets.
     *
     * If your widget needs custom CSS or JS files, you can use the theme object's appropriate methods within this method to add them. You can discover predefined assets supported by the theme and available for use in your components through the checks in the $hoptions array inside the templates/website/TemaAdi/inc/main-head.php and templates/website/TemaAdi/inc/constant-footer-js.php files. For example: $hoptions['magnific'], $hoptions['jsvectormap'], $hoptions['intlTelInput'], $hoptions['select2'], etc. Remember that you can always add your own custom CSS and JS code to templates/website/TemaAdi/css/custom.css and templates/website/TemaAdi/js/custom.js. However, you can also dynamically add predefined assets supported by the theme within this method.
     *
     * @param object $theme
     * @param array<string,mixed> $pageData
     * @return void
     */

    private static function addWidgetAssets(
        object $theme
    ): void {
        $requiredAssets = []; // You can assign the assets you want to add to this variable. For example: ['magnific', 'select2'], etc.

        if (!empty($requiredAssets) && is_callable([$theme, 'registerWidgetAsset'])) {
            $theme->registerWidgetAsset($requiredAssets);
        }

    }
}

