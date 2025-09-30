<?php
declare(strict_types=1);

/**
 * Provides helper functions to render the Nexa sidebar navigation.
 */
if (!function_exists('nexa_sidebar_fonts')) {
    /**
     * Returns the font includes required by the sidebar.
     */
    function nexa_sidebar_fonts(): string
    {
        ob_start();
        include __DIR__ . '/fonts/monoton.php';

        return ob_get_clean() ?: '';
    }
}

if (!function_exists('nexa_render_sidebar')) {
    /**
     * Renders the sidebar navigation HTML.
     *
     * @param string $active The filename of the active navigation item.
     */
    function nexa_render_sidebar(string $active = 'dashboard.php'): string
    {
        $navigation = [
            'dashboard.php' => 'Gösterge Paneli',
            'projects.php' => 'Projeler',
            'orders.php' => 'Siparişler',
            'suppliers.php' => 'Tedarikçiler',
        ];

        $html = '<nav class="d-flex flex-column flex-grow-1 gap-2 p-4 bg-dark text-white" style="min-height:100vh;">';
        $html .= '<div class="mb-4"><span class="fs-4" style="font-family: \'Monoton\', cursive; letter-spacing: 4px;">Nexa</span></div>';

        foreach ($navigation as $file => $label) {
            $isActive = $file === $active;
            $classes = 'btn btn-sm text-start ' . ($isActive ? 'btn-primary' : 'btn-outline-light');
            $html .= sprintf(
                '<a class="%s" href="%s">%s</a>',
                htmlspecialchars($classes, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($file, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            );
        }

        $html .= '<div class="mt-auto pt-4 border-top border-secondary">';
        $html .= '<a class="btn btn-outline-light w-100" href="?logout=1">Çıkış Yap</a>';
        $html .= '</div>';
        $html .= '</nav>';

        return $html;
    }
}
