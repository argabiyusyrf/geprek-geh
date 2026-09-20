<?php
class HomeController {
    public function index() {
        // Customer yang lupa set kata kunci recovery → arahkan ke setup (kecuali sengaja dilewati).
        if (Auth::check() && ($_SESSION['role'] ?? '') !== 'admin' && empty($_SESSION['skip_setup']) && !Auth::keywordSet()) {
            redirect('/geprek-geh/account/setup');
        }
        $featured = ProductRepo::publicList(
            'p.is_active = 1 AND p.is_featured = 1',
            [],
            'p.created_at DESC',
            8,
            0
        );
        $latest = ProductRepo::publicList(
            'p.is_active = 1',
            [],
            'p.created_at DESC',
            8,
            0
        );
        $categories = ProductRepo::categoriesWithCount();

        render('home/index', get_defined_vars());
    }
}
