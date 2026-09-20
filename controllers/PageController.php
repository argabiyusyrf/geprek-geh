<?php

class PageController {

    public function terms() {
        $this->render('Syarat &amp; Ketentuan', 'Ketentuan penggunaan layanan Geprek Geh.', 'website', '/geprek-geh/pages/terms', 'views/pages/terms.php');
    }

    public function privacy() {
        $this->render('Kebijakan Privasi', 'Bagaimana Geprek Geh mengelola dan melindungi data pribadimu.', 'website', '/geprek-geh/pages/privacy', 'views/pages/privacy.php');
    }

    private function render($title, $description, $ogType, $url, $view) {
        $page_title       = $title;
        $page_description = $description;
        $og_type          = $ogType;
        $og_url           = 'http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 's' : '') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $url;

        render(str_replace(['views/', '.php'], '', $view), get_defined_vars());
    }
}